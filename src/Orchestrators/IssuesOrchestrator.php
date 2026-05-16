<?php

/**
 * This file is part of Dex.
 *
 * (c) Olajide Olanrewaju <jidemac4@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Dex\Orchestrators;

use Dex\Services\Issues\IssuesListService;
use Dex\Services\Issues\IssuesSparklineService;
use Dex\Services\Issues\IssuesTrendService;
use Dex\Services\Issues\IssuesDetailService;
use Dex\Services\Issues\IssueShowViewPrepService;
use Dex\Services\Issues\IssueShowMetricsService;
use Dex\Services\Issues\IssueShowBreakdownsService;
use Dex\Services\Issues\IssueStatusService;
use Dex\Support\DexTime;
use Dex\DTO\Issues\IssuesListData;
use InvalidArgumentException;

/**
 * Orchestrates issues list and detail workflows.
 * Coordinates multiple services to build complete issue views.
 */
final readonly class IssuesOrchestrator
{
    public function __construct(
        private IssuesListService $listService,
        private IssuesSparklineService $sparklineService,
        private IssuesTrendService $trendService,
        private IssuesDetailService $detailService,
        private IssueShowViewPrepService $issueShowViewPrepService,
        private IssueShowMetricsService $issueShowMetricsService,
        private IssueShowBreakdownsService $issueShowBreakdownsService,
        private IssueStatusService $issueStatusService,
    ) {
    }

    /**
     * Get issues list data for the UI.
     *
     * Includes summary + chart data (intended for initial page load / page 1).
     */
    public function getIssuesData(
        ?string $status,
        string $searchQuery,
        int $page = 1,
        int $perPage = 25
    ): IssuesListData {
        [$rows, $pagination, $since, $prevSince] = $this->buildIssuesRowsAndPagination($status, $searchQuery, $page, $perPage);

        $summaryCounts = $this->listService->statusCounts();
        $volume = $this->sparklineService->buildVolume($since);
        $overallTrend = $this->trendService->calculateTotalTrend($since, $prevSince);

        return new IssuesListData(
            $rows,
            [
                'totalIssues' => (int) ($summaryCounts['total'] ?? 0),
                'openIssues' => (int) ($summaryCounts['open'] ?? 0),
                'regressedIssues' => (int) ($summaryCounts['regressed'] ?? 0),
                'resolvedIssues' => (int) ($summaryCounts['resolved'] ?? 0),
                'ignoredIssues' => (int) ($summaryCounts['ignored'] ?? 0),
                'events24h' => (int) ($overallTrend['current'] ?? 0),
                'eventsPrev24h' => (int) ($overallTrend['previous'] ?? 0),
                'eventsTrendPct' => $overallTrend['trendPct'] ?? null,
            ],
            [
                'hourly' => $volume,
            ],
            $pagination,
            [
                'status' => $status ?: 'all',
                'q' => $searchQuery,
            ]
        );
    }

    /**
     * List issues for pagination (issues + pagination only).
     *
     * Used by the AJAX pagination endpoint to keep payloads small.
     */
    public function listIssues(?string $status, string $searchQuery, int $page = 1, int $perPage = 25): IssuesListData
    {
        [$rows, $pagination] = $this->buildIssuesRowsAndPagination($status, $searchQuery, $page, $perPage);

        return new IssuesListData(
            $rows,
            null,
            null,
            $pagination,
            null
        );
    }

    public function showIssueDialogShell(int $issueId, int $selectedOccurrenceId): array
    {
        return array_merge(
            $this->buildIssueDialogPreparedData($issueId, $selectedOccurrenceId),
            [
                'dialogTab' => 'overview',
            ]
        );
    }

    public function showIssueDialogEvent(int $issueId, int $selectedOccurrenceId): array
    {
        return $this->buildIssueDialogPreparedData($issueId, $selectedOccurrenceId);
    }

    public function showIssueDialogTab(int $issueId, int $selectedOccurrenceId, string $tab): array
    {
        $tab = $this->normalizeDialogTab($tab);

        $prepared = array_merge(
            $this->buildIssueDialogPreparedData($issueId, $selectedOccurrenceId),
            [
                'dialogTab' => $tab,
            ]
        );

        if ($tab === 'metrics') {
            return array_merge(
                $prepared,
                $this->issueShowMetricsService->buildCharts($issueId),
                [
                    'breakdowns' => $this->issueShowBreakdownsService->buildBreakdowns($issueId),
                ]
            );
        }

        return $prepared;
    }

    public function resolveIssue(int $issueId): array
    {
        $issue = $this->issueStatusService->resolve($issueId);
        $summaryCounts = $this->listService->statusCounts();

        return [
            'issue' => [
                'id' => (int) ($issue['id'] ?? $issueId),
                'status' => $this->displayStatus((string) ($issue['status'] ?? 'resolved')),
            ],
            'summary' => [
                'totalIssues' => (int) ($summaryCounts['total'] ?? 0),
                'openIssues' => (int) ($summaryCounts['open'] ?? 0),
                'regressedIssues' => (int) ($summaryCounts['regressed'] ?? 0),
                'resolvedIssues' => (int) ($summaryCounts['resolved'] ?? 0),
                'ignoredIssues' => (int) ($summaryCounts['ignored'] ?? 0),
            ],
        ];
    }

    public function ignoreIssue(int $issueId): array
    {
        $issue = $this->issueStatusService->ignore($issueId);
        $summaryCounts = $this->listService->statusCounts();

        return [
            'issue' => [
                'id' => (int) ($issue['id'] ?? $issueId),
                'status' => $this->displayStatus((string) ($issue['status'] ?? 'ignored')),
            ],
            'summary' => [
                'totalIssues' => (int) ($summaryCounts['total'] ?? 0),
                'openIssues' => (int) ($summaryCounts['open'] ?? 0),
                'regressedIssues' => (int) ($summaryCounts['regressed'] ?? 0),
                'resolvedIssues' => (int) ($summaryCounts['resolved'] ?? 0),
                'ignoredIssues' => (int) ($summaryCounts['ignored'] ?? 0),
            ],
        ];
    }

    private function normalizeDialogTab(string $tab): string
    {
        $tab = strtolower(trim($tab));
        $allowed = ['stack', 'lifecycle', 'http', 'tags', 'raw', 'metrics'];

        if (! in_array($tab, $allowed, true)) {
            throw new InvalidArgumentException('Invalid issue dialog tab.');
        }

        return $tab;
    }

    private function displayStatus(string $status): string
    {
        $status = strtolower($status);

        return match ($status) {
            'unhandled' => 'open',
            'regression', 'regressed' => 'regressed',
            default => $status,
        };
    }

    private function timeAgo(string $datetime): string
    {
        return DexTime::timeAgo($datetime);
    }

    private function age(string $from): string
    {
        $age = DexTime::age($from);
        if ($age === '-') {
            return $age;
        }

        $parts = preg_split('/\s+/', trim($age)) ?: [];

        return (string) ($parts[0] ?? $age);
    }

    private function buildIssuesRowsAndPagination(
        ?string $status,
        string $searchQuery,
        int $page,
        int $perPage
    ): array {
        $list = $this->listService->list($status, $searchQuery, $page, $perPage);
        $rows = $list['rows'];
        $issueIds = $this->listService->extractIssueIds($rows);

        $since = DexTime::secondsAgoUtcString(86400);
        $prevSince = DexTime::secondsAgoUtcString(172800);

        $sparkMap = $this->sparklineService->buildSparklines($issueIds, $since);
        $trends = $this->trendService->calculateTrends($issueIds, $since, $prevSince);

        foreach ($rows as &$row) {
            $issueId = (int) ($row['id'] ?? 0);
            $events24h = (int) ($trends['current'][$issueId] ?? 0);
            $eventsPrev24h = (int) ($trends['previous'][$issueId] ?? 0);

            $row = [
                'id' => $issueId,
                'cls' => (string) ($row['class'] ?: 'UnknownIssue'),
                'status' => $this->displayStatus((string) ($row['status'] ?? '')),
                'level' => strtolower((string) ($row['level'] ?? 'error')),
                'message' => (string) ($row['title'] ?? ''),
                'method' => strtoupper((string) ($row['latest_method'] ?? '')),
                'route' => (string) ($row['latest_path'] ?? '-'),
                'env' => (string) ($row['environment'] ?? (defined('ENVIRONMENT') ? (string) ENVIRONMENT : '-')),
                'assignee' => null,
                'events' => (int) ($row['times_seen'] ?? 0),
                'events24h' => $events24h,
                'eventsPrev24h' => $eventsPrev24h,
                'trend' => $sparkMap[$issueId] ?? array_fill(0, 24, 0),
                'trendPct' => $this->trendService->calculateTrendPercentage($events24h, $eventsPrev24h),
                'lastSeen' => $this->timeAgo((string) ($row['last_seen'] ?? '')),
                'age' => $this->age((string) ($row['first_seen'] ?? '')),
            ];
        }
        unset($row);

        $total = (int) ($list['total'] ?? 0);
        $currentPage = (int) ($list['page'] ?? 1);
        $currentPerPage = (int) ($list['perPage'] ?? $perPage);
        $pages = max(1, (int) ceil($total / max(1, $currentPerPage)));

        return [
            $rows,
            [
                'page' => $currentPage,
                'perPage' => $currentPerPage,
                'total' => $total,
                'pages' => $pages,
                'hasPrev' => $currentPage > 1,
                'hasNext' => $currentPage < $pages,
                'from' => $total === 0 ? 0 : (($currentPage - 1) * $currentPerPage) + 1,
                'to' => min($total, $currentPage * $currentPerPage),
            ],
            $since,
            $prevSince,
        ];
    }

    private function buildIssueDialogPreparedData(int $issueId, int $selectedOccurrenceId): array
    {
        $detail = $this->detailService->getIssueDetail($issueId, $selectedOccurrenceId);

        return array_merge(
            $this->issueShowViewPrepService->prepare(
                $detail['issue'],
                $detail['occurrences'],
                is_array($detail['selected'] ?? null) ? $detail['selected'] : null,
                is_array($detail['request'] ?? null) ? $detail['request'] : null,
            ),
            [
                'eventPager' => $detail['eventPager'] ?? [],
            ]
        );
    }
}

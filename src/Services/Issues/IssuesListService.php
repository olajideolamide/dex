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

namespace Dex\Services\Issues;

use Dex\Repositories\IssueReadRepository;

/**
 * Fetches and filters issue list.
 * Handles pagination, searching, and status filtering.
 */
final readonly class IssuesListService
{
    public function __construct(
        private IssueReadRepository $issuesRepo,
    ) {
    }

    /**
     * List issues with optional status filter and search query.
     *
     * @return array List of issue rows
     */
    public function list(?string $status, string $searchQuery, int $page = 1, int $perPage = 25): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        return [
            'rows' => $this->issuesRepo->listIssues($status, $searchQuery, $perPage, $offset),
            'total' => $this->issuesRepo->countIssues($status, $searchQuery),
            'page' => $page,
            'perPage' => $perPage,
        ];
    }

    public function statusCounts(): array
    {
        return $this->issuesRepo->countByStatus();
    }

    /**
     * Extract issue IDs from list for further processing.
     */
    public function extractIssueIds(array $issueRows): array
    {
        return array_values(array_filter(array_map(
            static fn($r) => (int)($r['id'] ?? 0),
            $issueRows
        )));
    }
}

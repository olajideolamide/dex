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

use Dex\Repositories\OccurrenceReadRepository;
use Dex\Repositories\RequestReadRepository;
use Dex\Support\DexTime;

/**
 * Builds lightweight breakdowns for the issue show view.
 *
 * Uses occurrence context (scrubbed JSON) so it doesn't require additional tables.
 */
final class IssueShowBreakdownsService
{
    public function __construct(
        private readonly OccurrenceReadRepository $occurrences,
        private readonly RequestReadRepository $requests,
    ) {
    }

    /**
     * @return array{paths: array, transactions: array, envs: array, os: array, browser: array}
     */
    public function buildBreakdowns(int $issueId): array
    {
        $since24h = DexTime::secondsAgoUtcString(86400);
        $rows = $this->occurrences->contextRowsSince($issueId, $since24h, 20000);
        $requestRows = $this->requests->findLatestByRequestIds(array_column($rows, 'request_id'));

        $paths = [];
        $transactions = [];
        $envs = [];
        $os = [];
        $browser = [];

        foreach ($rows as $row) {
            $ctxRaw = (string) ($row['context'] ?? '');
            if ($ctxRaw === '') {
                continue;
            }

            $ctx = json_decode($ctxRaw, true);
            if (!is_array($ctx)) {
                continue;
            }

            $requestId = (string) ($row['request_id'] ?? '');
            $requestRow = $requestRows[$requestId] ?? null;
            $requestSnapshot = $this->decodeJsonArray($requestRow['snapshot_json'] ?? null);
            $snapshotRequest = is_array($requestSnapshot['request'] ?? null) ? $requestSnapshot['request'] : [];
            $snapshotCi = is_array($requestSnapshot['ci'] ?? null) ? $requestSnapshot['ci'] : [];

            $req = is_array($ctx['request'] ?? null) ? $ctx['request'] : [];
            $tags = is_array($ctx['tags'] ?? null) ? $ctx['tags'] : [];

            $path = (string) ($snapshotRequest['path'] ?? $requestRow['path'] ?? $req['path'] ?? '');
            if ($path !== '') {
                $paths[$path] = ($paths[$path] ?? 0) + 1;
            }

            $method = (string) ($snapshotRequest['method'] ?? $requestRow['method'] ?? $req['method'] ?? '');
            $transaction = trim((string) ($method . ' ' . $path));
            if ($transaction !== '') {
                $transactions[$transaction] = ($transactions[$transaction] ?? 0) + 1;
            }

            $env = (string) ($snapshotCi['env'] ?? $tags['environment'] ?? '');
            if ($env !== '') {
                $envs[$env] = ($envs[$env] ?? 0) + 1;
            }

            $ua = (string) ($snapshotRequest['user_agent'] ?? $req['user_agent'] ?? '');
            $uaParts = $this->parseUserAgent($ua);
            $osV = (string) ($uaParts['os'] ?? '');
            $browserV = (string) ($uaParts['browser'] ?? '');
            if ($osV !== '') {
                $os[$osV] = ($os[$osV] ?? 0) + 1;
            }
            if ($browserV !== '') {
                $browser[$browserV] = ($browser[$browserV] ?? 0) + 1;
            }
        }

        return [
            'paths' => $this->toPctRows($paths, 8),
            'transactions' => $this->toPctRows($transactions, 5),
            'envs' => $this->toPctRows($envs, 5),
            'os' => $this->toPctRows($os, 5),
            'browser' => $this->toPctRows($browser, 5),
        ];
    }

    /**
     * @param array<string,int> $counts
     * @return array<int, array{key:string, count:int, pct:int}>
     */
    private function toPctRows(array $counts, int $limit): array
    {
        if (empty($counts)) {
            return [];
        }

        arsort($counts);
        $total = array_sum($counts);
        if ($total <= 0) {
            return [];
        }

        $out = [];
        foreach (array_slice($counts, 0, $limit, true) as $k => $cnt) {
            $pct = (int) round(($cnt / $total) * 100);
            $out[] = ['key' => (string) $k, 'count' => (int) $cnt, 'pct' => $pct];
        }

        return $out;
    }

    private function decodeJsonArray(mixed $value): array
    {
        if (! is_string($value) || $value === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function parseUserAgent(string $ua): array
    {
        $ua = strtolower($ua);
        $os = 'Unknown';
        if (str_contains($ua, 'windows nt')) {
            $os = 'Windows';
        } elseif (str_contains($ua, 'android')) {
            $os = 'Android';
        } elseif (str_contains($ua, 'iphone') || str_contains($ua, 'ipad') || str_contains($ua, 'ios')) {
            $os = 'iOS';
        } elseif (str_contains($ua, 'mac os x') || str_contains($ua, 'macintosh')) {
            $os = 'macOS';
        } elseif (str_contains($ua, 'linux')) {
            $os = 'Linux';
        }

        $browser = 'Unknown';
        if (str_contains($ua, 'edg/')) {
            $browser = 'Edge';
        } elseif (str_contains($ua, 'opr/') || str_contains($ua, 'opera')) {
            $browser = 'Opera';
        } elseif (str_contains($ua, 'chrome/') && ! str_contains($ua, 'chromium') && ! str_contains($ua, 'edg/')) {
            $browser = 'Chrome';
        } elseif (str_contains($ua, 'safari/') && ! str_contains($ua, 'chrome/')) {
            $browser = 'Safari';
        } elseif (str_contains($ua, 'firefox/')) {
            $browser = 'Firefox';
        } elseif (str_contains($ua, 'curl/')) {
            $browser = 'curl';
        }

        return ['os' => $os, 'browser' => $browser];
    }
}

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

use Dex\Adapters\CiDbBucketExpressionProvider;
use Dex\Repositories\OccurrenceReadRepository;
use Dex\Support\DexTime;

/**
 * Builds sparkline data for issues (24-hour occurrence distribution).
 * Shows hourly breakdown of occurrences for each issue.
 */
final class IssuesSparklineService
{
    public function __construct(
        private readonly OccurrenceReadRepository $occurrences,
        private readonly CiDbBucketExpressionProvider $bucketExpr,
    ) {
    }

    /**
     * Build hourly sparklines (last 24 hours) for a set of issues.
     *
     * @param array $issueIds List of issue IDs
     * @return array Map of issue ID => [24 hourly counts]
     */
    public function buildSparklines(array $issueIds, string $since): array
    {
        $bucketKeys = $this->hourBucketKeys();
        $posMap = array_flip($bucketKeys);

        // Initialize sparklines
        $sparkMap = [];
        foreach ($issueIds as $iid) {
            $sparkMap[$iid] = array_fill(0, 24, 0);
        }

        // Fetch hourly occurrence data
        if (!empty($issueIds)) {
            $bucketExprSql = $this->bucketExpr->hourBucketExpr('happened_at');
            $sparkRows = $this->occurrences->sparkRows($issueIds, $since, $bucketExprSql);

            foreach ($sparkRows as $r) {
                $iid = (int)($r['issue_id'] ?? 0);
                $hb = DexTime::hourBucketKey((string) ($r['hb'] ?? ''));
                $cnt = (int)($r['cnt'] ?? 0);

                if ($iid > 0 && $hb !== null && isset($posMap[$hb], $sparkMap[$iid])) {
                    $sparkMap[$iid][$posMap[$hb]] = $cnt;
                }
            }
        }

        return $sparkMap;
    }

    public function buildVolume(string $since): array
    {
        $bucketKeys = $this->hourBucketKeys();
        $posMap = array_flip($bucketKeys);
        $series = array_fill(0, count($bucketKeys), 0);
        $bucketExprSql = $this->bucketExpr->hourBucketExpr('happened_at');
        $rows = $this->occurrences->hourlyTotalRows($since, $bucketExprSql);

        foreach ($rows as $row) {
            $bucket = DexTime::hourBucketKey((string) ($row['hb'] ?? ''));
            if ($bucket === null || ! isset($posMap[$bucket])) {
                continue;
            }

            $series[$posMap[$bucket]] = (int) ($row['cnt'] ?? 0);
        }

        return $series;
    }

    private function hourBucketKeys(): array
    {
        return DexTime::last24HourBucketKeys();
    }
}

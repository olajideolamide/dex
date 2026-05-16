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
 * Computes extra metrics for the issue show page.
 *
 * Kept separate from IssuesDetailService so that detail fetching stays focused
 * on the core rows (issue/occurrences/request).
 */
final class IssueShowMetricsService
{
    public function __construct(
        private readonly OccurrenceReadRepository $occurrences,
        private readonly CiDbBucketExpressionProvider $bucketExpr,
    ) {
    }

    /**
     * @return array{totalOccurrences:int, occ24h:int, hourLabels:array, hourCounts:array, todLabels:array, todCounts:array}
     */
    public function buildCharts(int $issueId): array
    {
        $since24h = DexTime::secondsAgoUtcString(86400);
        $since7d = DexTime::secondsAgoUtcString(7 * 86400);

        $totalOccurrences = $this->occurrences->countTotalForIssue($issueId);
        $occ24h = $this->occurrences->countForIssueBetween($issueId, $since24h);

        // 24h hourly bars (for spark)
        $hourBucketKeys = DexTime::last24HourBucketKeys();
        $hourBuckets = array_fill_keys($hourBucketKeys, 0);
        $hourLabels = array_map(
            static fn(string $bucketKey): string => substr($bucketKey, 11, 2),
            $hourBucketKeys
        );

        $bucketExprSql = $this->bucketExpr->hourBucketExpr('happened_at');
        $rows = $this->occurrences->hourlyCountRows($issueId, $since24h, $bucketExprSql);
        foreach ($rows as $r) {
            $hb = DexTime::hourBucketKey((string) ($r['hb'] ?? ''));
            $cnt = (int) ($r['cnt'] ?? 0);
            if ($hb !== null && array_key_exists($hb, $hourBuckets)) {
                $hourBuckets[$hb] = $cnt;
            }
        }

        // 7d time-of-day histogram
        $todCounts = array_fill(0, 24, 0);
        $todLabels = [];
        for ($h = 0; $h < 24; $h++) {
            $todLabels[] = str_pad((string) $h, 2, '0', STR_PAD_LEFT);
        }

        $recentTimes = $this->occurrences->listHappenedAtSince($issueId, $since7d, 20000);
        foreach ($recentTimes as $row) {
            $bucketKey = DexTime::hourBucketKey((string) ($row['happened_at'] ?? ''));
            if ($bucketKey === null) {
                continue;
            }
            $hour = (int) substr($bucketKey, 11, 2);
            if ($hour >= 0 && $hour <= 23) {
                $todCounts[$hour]++;
            }
        }

        return [
            'totalOccurrences' => $totalOccurrences,
            'occ24h' => $occ24h,
            'hourLabels' => $hourLabels,
            'hourCounts' => array_values($hourBuckets),
            'todLabels' => $todLabels,
            'todCounts' => $todCounts,
        ];
    }
}

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

/**
 * Calculates trend metrics for issues (24h vs previous 24h comparison).
 * Provides occurrence counts and percentage change indicators.
 */
final class IssuesTrendService
{
    public function __construct(
        private readonly OccurrenceReadRepository $occurrences,
    ) {
    }

    /**
     * Get occurrence counts for issues in current and previous 24h periods.
     *
     * @param array $issueIds List of issue IDs
     * @param string $since Timestamp for current 24h start
     * @param string $prevSince Timestamp for previous 24h start
     *
     * @return array Trend data: ['current' => [...], 'previous' => [...]]
     */
    public function calculateTrends(array $issueIds, string $since, string $prevSince): array
    {
        $currMap = $this->occurrences->countByIssueIdsSince($issueIds, $since);
        $prevMap = $this->occurrences->countByIssueIdsSince($issueIds, $prevSince, $since);

        return [
            'current' => $currMap,
            'previous' => $prevMap,
        ];
    }

    public function calculateTotalTrend(string $since, string $prevSince): array
    {
        $current = $this->occurrences->countTotalSince($since);
        $previous = $this->occurrences->countTotalSince($prevSince, $since);

        return [
            'current' => $current,
            'previous' => $previous,
            'trendPct' => $this->calculateTrendPercentage($current, $previous),
        ];
    }

    /**
     * Calculate percentage change between two periods.
     * Helper method for computing trend percentages.
     */
    public function calculateTrendPercentage(int $current, int $previous): ?float
    {
        if ($previous === 0) {
            return $current > 0 ? 100.0 : null;
        }
        return (($current - $previous) / $previous) * 100;
    }
}

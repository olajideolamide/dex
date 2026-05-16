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

use Dex\Services\Core\DataPurgeService;

/**
 * Orchestrates data purge workflow.
 * Coordinates with DataPurgeService and handles logging/output.
 *
 * Future enhancements could add:
 * - Pre-purge notifications
 * - Post-purge logging
 * - Multiple purge strategies
 * - Complex purge workflows
 */
final class PurgeDataOrchestrator
{
    public function __construct(
        private readonly DataPurgeService $purgeService,
    ) {
    }

    /**
     * Execute purge with output callback for CLI/UI integration.
     *
     * @param int $batchSize Rows per delete batch
     * @param int $maxRuntimeSeconds Max time to spend purging
     * @param int $sleepMsBetweenBatches Sleep between batches to avoid DB lock
     * @param bool $useDbLock Use database lock to prevent concurrent purges
     * @param callable(string):void $output Output logging callback
     *
     * @return array Purge summary with deleted counts and status
     */
    public function execute(
        int $batchSize = 500,
        int $maxRuntimeSeconds = 20,
        int $sleepMsBetweenBatches = 0,
        bool $useDbLock = true,
        ?callable $output = null
    ): array {
        $output ??= static function (): void {
        };

        return $this->purgeService->purgeAll(
            batchSize: $batchSize,
            maxRuntimeSeconds: $maxRuntimeSeconds,
            sleepMsBetweenBatches: $sleepMsBetweenBatches,
            useDbLock: $useDbLock,
            output: $output
        );
    }
}

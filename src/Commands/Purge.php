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

namespace Dex\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;
use Dex\Config\Services as DexServices;

final class Purge extends BaseCommand
{
    protected $group       = 'Dex';
    protected $name        = 'dex:purge';
    protected $description = 'Purges Dex data using TTL + max-row caps (batched).';
    protected $usage       = 'php spark dex:purge --batch=1000 --max-runtime=30 --sleep-ms=25';

    protected $options = [
        '--batch'       => 'Batch size per delete loop. Default from config.',
        '--max-runtime' => 'Max runtime seconds. Default from config.',
        '--sleep-ms'    => 'Sleep between batches (milliseconds). Default from config.',
        '--no-lock'     => 'Disable DB lock for this run (overrides config).',
    ];

    /**
     * Run the purge job with CLI overrides and print a summary.
     */
    public function run(array $params): int
    {
        $cfg = DexServices::configProvider()->get();

        if (!($cfg->purgeEnabled ?? false)) {
            CLI::error('Dex purge is disabled. Set $purgeEnabled = true in Config/Dex.php');
            return 1;
        }

        $batch   = (int)($params['batch']       ?? ($cfg->purgeBatchSize ?? 500));
        $maxSecs = (int)($params['max-runtime'] ?? ($cfg->purgeMaxRuntimeSeconds ?? 20));
        $sleepMs = (int)($params['sleep-ms']    ?? ($cfg->purgeSleepMsBetweenBatches ?? 0));

        $useLock = (bool)($cfg->purgeUseDbLock ?? true);
        if (isset($params['no-lock'])) {
            $useLock = false;
        }

        // Get orchestrator from services
        $orchestrator = DexServices::purgeDataOrchestrator();

        // Execute purge with CLI output callback
        $summary = $orchestrator->execute(
            batchSize: max(50, $batch),
            maxRuntimeSeconds: max(5, $maxSecs),
            sleepMsBetweenBatches: max(0, $sleepMs),
            useDbLock: $useLock,
            output: static function (string $msg): void {
                CLI::write($msg);
            }
        );

        CLI::newLine();
        CLI::write('--- Summary ---');
        foreach ($summary as $k => $v) {
            CLI::write(str_pad((string)$k, 28) . ': ' . $v);
        }

        return 0;
    }
}

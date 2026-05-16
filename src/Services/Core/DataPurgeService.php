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

namespace Dex\Services\Core;

use CodeIgniter\Database\BaseConnection;
use Dex\Support\DexTime;

/**
 * Handles all data purge operations (TTL and cap-based).
 * Coordinates with database to delete old telemetry data while respecting runtime limits.
 */
final class DataPurgeService
{
    public function __construct(
        private readonly BaseConnection $db,
        private readonly object $config,
    ) {
    }

    /**
     * Run TTL and cap purges, returning summary stats.
     *
     * @param callable(string):void $output Optional logging callback for CLI/UI
     */
    public function purgeAll(
        int $batchSize,
        int $maxRuntimeSeconds,
        int $sleepMsBetweenBatches,
        bool $useDbLock,
        ?callable $output = null
    ): array {
        $output ??= static function (): void {
        };
        $started = microtime(true);

        $lockAcquired = false;
        if ($useDbLock) {
            $lockName = (string)($this->config->purgeDbLockName ?? 'dex_purge');
            $lockAcquired = $this->tryAcquireDbLock($lockName);
            if (!$lockAcquired) {
                $output('Another purge appears to be running (DB lock not acquired). Exiting.');
                return [
                    'status' => 'skipped (lock busy)',
                ];
            }
        }

        try {
            $output('Dex purge starting...');
            $output('Batch: ' . $batchSize . ' | Max runtime: ' . $maxRuntimeSeconds . 's');

            // -------- Stage A: TTL --------
            $output('');
            $output('Stage A: TTL purge');

            $ttlDeleted = 0;

            $ttlDeleted += $this->purgeByTtl(
                table: 'dex_requests',
                idCol: 'id',
                dateCol: 'created_at',
                retentionDays: (int)($this->config->purgeRetentionDaysRequests ?? 14),
                batchSize: $batchSize,
                maxRuntimeSeconds: $maxRuntimeSeconds,
                sleepMsBetweenBatches: $sleepMsBetweenBatches,
                started: $started,
                output: $output
            );

            $ttlDeleted += $this->purgeByTtl(
                table: 'dex_occurrences',
                idCol: 'id',
                dateCol: 'happened_at',
                retentionDays: (int)($this->config->purgeRetentionDaysOccurrences ?? 30),
                batchSize: $batchSize,
                maxRuntimeSeconds: $maxRuntimeSeconds,
                sleepMsBetweenBatches: $sleepMsBetweenBatches,
                started: $started,
                output: $output
            );

            // Issues TTL purge (cascade delete occurrences for deleted issues)
            $ttlDeleted += $this->purgeIssuesByTtl(
                retentionDays: (int)($this->config->purgeRetentionDaysIssues ?? 90),
                batchSize: $batchSize,
                maxRuntimeSeconds: $maxRuntimeSeconds,
                sleepMsBetweenBatches: $sleepMsBetweenBatches,
                started: $started,
                output: $output
            );

            // -------- Stage B: Caps --------
            $output('');
            $output('Stage B: Max-row caps');

            $capDeleted = 0;

            $capDeleted += $this->purgeByCap(
                table: 'dex_requests',
                idCol: 'id',
                maxRows: (int)($this->config->purgeMaxRowsRequests ?? 100_000),
                batchSize: $batchSize,
                maxRuntimeSeconds: $maxRuntimeSeconds,
                sleepMsBetweenBatches: $sleepMsBetweenBatches,
                started: $started,
                output: $output
            );

            $capDeleted += $this->purgeByCap(
                table: 'dex_occurrences',
                idCol: 'id',
                maxRows: (int)($this->config->purgeMaxRowsOccurrences ?? 200_000),
                batchSize: $batchSize,
                maxRuntimeSeconds: $maxRuntimeSeconds,
                sleepMsBetweenBatches: $sleepMsBetweenBatches,
                started: $started,
                output: $output
            );

            $capDeleted += $this->purgeIssuesByCap(
                maxRows: (int)($this->config->purgeMaxRowsIssues ?? 50_000),
                batchSize: $batchSize,
                maxRuntimeSeconds: $maxRuntimeSeconds,
                sleepMsBetweenBatches: $sleepMsBetweenBatches,
                started: $started,
                output: $output
            );

            $runtime = round(microtime(true) - $started, 3);

            return [
                'ttlDeleted'    => (string)$ttlDeleted,
                'capDeleted'    => (string)$capDeleted,
                'totalDeleted'  => (string)($ttlDeleted + $capDeleted),
                'runtimeSec'    => (string)$runtime,
                'status'        => 'ok',
            ];
        } finally {
            if ($useDbLock) {
                $lockName = (string)($this->config->purgeDbLockName ?? 'dex_purge');
                $this->releaseDbLock($lockName);
            }
        }
    }

    /**
     * Delete rows older than the retention window in batches.
     */
    private function purgeByTtl(
        string $table,
        string $idCol,
        string $dateCol,
        int $retentionDays,
        int $batchSize,
        int $maxRuntimeSeconds,
        int $sleepMsBetweenBatches,
        float $started,
        callable $output
    ): int {
        $cutoff = $this->cutoffDateTime($retentionDays);

        $output(sprintf(
            'TTL %s: delete where %s < %s (batched)',
            $table,
            $dateCol,
            $cutoff
        ));

        $total = 0;

        while (!$this->runtimeExceeded($started, $maxRuntimeSeconds)) {
            $ids = $this->db->table($table)
                            ->select($idCol)
                            ->where($dateCol . ' <', $cutoff)
                            ->orderBy($idCol, 'ASC')
                            ->limit($batchSize)
                            ->get()
                            ->getResultArray();

            if (empty($ids)) {
                break;
            }

            $idList = array_map(static fn(array $row) => $row[$idCol], $ids);
            $count  = count($idList);

            $this->db->table($table)->whereIn($idCol, $idList)->delete();

            $total += $count;

            if ($sleepMsBetweenBatches > 0) {
                usleep($sleepMsBetweenBatches * 1000);
            }
        }

        $output('  -> deleted ' . $total);
        return $total;
    }

    /**
     * Enforce a max-row cap by deleting oldest rows in batches.
     */
    private function purgeByCap(
        string $table,
        string $idCol,
        int $maxRows,
        int $batchSize,
        int $maxRuntimeSeconds,
        int $sleepMsBetweenBatches,
        float $started,
        callable $output
    ): int {
        if ($maxRows <= 0) {
            $output('Cap ' . $table . ': disabled (maxRows <= 0)');
            return 0;
        }

        $current = $this->countRows($table);
        $output("Cap {$table}: keep latest {$maxRows} rows (current: {$current})");

        if ($current <= $maxRows) {
            $output('  -> ok (under cap)');
            return 0;
        }

        $minKeepId = $this->minIdToKeep($table, $idCol, $maxRows);
        if ($minKeepId === null) {
            $output('  -> could not determine minKeepId; skipping cap purge');
            return 0;
        }

        $total = 0;

        while (!$this->runtimeExceeded($started, $maxRuntimeSeconds)) {
            $ids = $this->db->table($table)
                            ->select($idCol)
                            ->where($idCol . ' <', $minKeepId)
                            ->orderBy($idCol, 'ASC')
                            ->limit($batchSize)
                            ->get()
                            ->getResultArray();

            if (empty($ids)) {
                break;
            }

            $idList = array_map(static fn(array $row) => $row[$idCol], $ids);
            $count  = count($idList);

            $this->db->table($table)->whereIn($idCol, $idList)->delete();

            $total += $count;

            if ($sleepMsBetweenBatches > 0) {
                usleep($sleepMsBetweenBatches * 1000);
            }
        }

        $output('  -> deleted ' . $total);
        return $total;
    }

    /**
     * TTL purge issues and cascade delete their occurrences.
     */
    private function purgeIssuesByTtl(
        int $retentionDays,
        int $batchSize,
        int $maxRuntimeSeconds,
        int $sleepMsBetweenBatches,
        float $started,
        callable $output
    ): int {
        $cutoff = $this->cutoffDateTime($retentionDays);

        $output(sprintf(
            'TTL dex_issues: delete where last_seen < %s (cascade occurrences)',
            $cutoff
        ));

        $total = 0;

        while (!$this->runtimeExceeded($started, $maxRuntimeSeconds)) {
            $issueIds = $this->db->table('dex_issues')
                                 ->select('id')
                                 ->where('last_seen <', $cutoff)
                                 ->orderBy('id', 'ASC')
                                 ->limit($batchSize)
                                 ->get()
                                 ->getResultArray();

            if (empty($issueIds)) {
                break;
            }

            $ids   = array_map(static fn(array $r) => (int)$r['id'], $issueIds);
            $count = count($ids);

            // Cascade occurrences first
            $this->db->table('dex_occurrences')->whereIn('issue_id', $ids)->delete();
            $this->db->table('dex_issues')->whereIn('id', $ids)->delete();

            $total += $count;

            if ($sleepMsBetweenBatches > 0) {
                usleep($sleepMsBetweenBatches * 1000);
            }
        }

        $output('  -> deleted ' . $total . ' issue rows');
        return $total;
    }

    /**
     * Cap issues table size and cascade delete old occurrences.
     */
    private function purgeIssuesByCap(
        int $maxRows,
        int $batchSize,
        int $maxRuntimeSeconds,
        int $sleepMsBetweenBatches,
        float $started,
        callable $output
    ): int {
        if ($maxRows <= 0) {
            $output('Cap dex_issues: disabled (maxRows <= 0)');
            return 0;
        }

        $current = $this->countRows('dex_issues');
        $output("Cap dex_issues: keep latest {$maxRows} rows (current: {$current})");

        if ($current <= $maxRows) {
            $output('  -> ok (under cap)');
            return 0;
        }

        $minKeepId = $this->minIdToKeep('dex_issues', 'id', $maxRows);
        if ($minKeepId === null) {
            $output('  -> could not determine minKeepId; skipping cap purge');
            return 0;
        }

        $total = 0;

        while (!$this->runtimeExceeded($started, $maxRuntimeSeconds)) {
            $issueIds = $this->db->table('dex_issues')
                                 ->select('id')
                                 ->where('id <', $minKeepId)
                                 ->orderBy('id', 'ASC')
                                 ->limit($batchSize)
                                 ->get()
                                 ->getResultArray();

            if (empty($issueIds)) {
                break;
            }

            $ids   = array_map(static fn(array $r) => (int)$r['id'], $issueIds);
            $count = count($ids);

            $this->db->table('dex_occurrences')->whereIn('issue_id', $ids)->delete();
            $this->db->table('dex_issues')->whereIn('id', $ids)->delete();

            $total += $count;

            if ($sleepMsBetweenBatches > 0) {
                usleep($sleepMsBetweenBatches * 1000);
            }
        }

        $output('  -> deleted ' . $total . ' issue rows');
        return $total;
    }

    /**
     * Calculate cutoff datetime for TTL purge.
     */
    private function cutoffDateTime(int $days): string
    {
        return DexTime::secondsAgoUtcString(max(0, $days) * 86400);
    }

    /**
     * Check if max runtime has been exceeded.
     */
    private function runtimeExceeded(float $started, int $maxRuntimeSeconds): bool
    {
        return (microtime(true) - $started) >= $maxRuntimeSeconds;
    }

    /**
     * Count total rows in a table.
     */
    private function countRows(string $table): int
    {
        $row = $this->db->query('SELECT COUNT(*) AS c FROM ' . $this->db->protectIdentifiers($table))
                        ->getRowArray();

        return (int)($row['c'] ?? 0);
    }

    /**
     * Get the minimum ID among the newest maxRows records.
     */
    private function minIdToKeep(string $table, string $idCol, int $maxRows): int|null
    {
        // Works on MySQL/MariaDB.
        $sql = sprintf(
            'SELECT MIN(%1$s) AS min_keep_id FROM (SELECT %1$s FROM %2$s ORDER BY %1$s DESC LIMIT ?) t',
            $this->db->protectIdentifiers($idCol),
            $this->db->protectIdentifiers($table)
        );

        $row = $this->db->query($sql, [$maxRows])->getRowArray();
        $val = $row['min_keep_id'] ?? null;

        return $val === null ? null : (int)$val;
    }

    /**
     * Acquire a named DB lock when supported (MySQL/MariaDB).
     */
    private function tryAcquireDbLock(string $name): bool
    {
        // Only MySQL/MariaDB support GET_LOCK in this form; otherwise skip.
        $driver = strtolower((string)($this->db->DBDriver ?? ''));
        if (!in_array($driver, ['mysqli', 'mysql'], true)) {
            return true;
        }

        $row = $this->db->query('SELECT GET_LOCK(?, 0) AS l', [$name])->getRowArray();
        return (int)($row['l'] ?? 0) === 1;
    }

    /**
     * Release a named DB lock when supported (MySQL/MariaDB).
     */
    private function releaseDbLock(string $name): void
    {
        $driver = strtolower((string)($this->db->DBDriver ?? ''));
        if (!in_array($driver, ['mysqli', 'mysql'], true)) {
            return;
        }
        $this->db->query('SELECT RELEASE_LOCK(?)', [$name]);
    }
}

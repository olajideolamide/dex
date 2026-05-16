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

namespace Dex\Repositories;

use CodeIgniter\Database\BaseConnection;
use Dex\Domain\Exceptions\RepositoryException;
use Dex\Models\IssueModel;
use Dex\Support\DexTime;
use Config\Database;
use Throwable;

final class IssueRepository
{
    private IssueModel $model;
    private BaseConnection $db;

    public function __construct(?IssueModel $model = null, ?BaseConnection $db = null)
    {
        $this->model = $model ?? new IssueModel();
        $this->db = $db ?? Database::connect();
    }

    public function upsertIssue(array $issue): int
    {
        if ($this->db->DBDriver === 'MySQLi') {
            return $this->upsertIssueAtomic($issue);
        }

        return $this->upsertIssueLegacy($issue);
    }

    private function upsertIssueLegacy(array $issue): int
    {
        try {
            $existing = $this->model->where('fingerprint', $issue['fingerprint'])->first();
        } catch (Throwable $e) {
            throw RepositoryException::writeFailed('issue', $e);
        }

        if ($existing) {
            $nextStatus = $existing['status'];
            if ($existing['status'] === 'resolved') {
                $nextStatus = 'regression';
            }

            try {
                $ok = $this->model->update($existing['id'], [
                    'last_seen' => $issue['last_seen'] ?? DexTime::nowUtcString(),
                    'times_seen' => (int) $existing['times_seen'] + 1,
                    'level' => $issue['level'] ?? $existing['level'],
                    'class' => $issue['class'] ?? $existing['class'],
                    'latest_path' => $issue['latest_path'] ?? $existing['latest_path'],
                    'latest_method' => $issue['latest_method'] ?? $existing['latest_method'] ?? null,
                    'environment' => $issue['environment'] ?? $existing['environment'] ?? null,
                    'title' => $issue['title'] ?? $existing['title'],
                    'status' => $nextStatus,
                ]);
            } catch (Throwable $e) {
                throw RepositoryException::writeFailed('issue', $e);
            }

            if ($ok === false) {
                throw RepositoryException::writeFailed('issue');
            }

            return (int) $existing['id'];
        }

        try {
            $id = $this->model->insert([
                'fingerprint' => $issue['fingerprint'],
                'level'       => $issue['level'] ?? 'error',
                'class'       => $issue['class'] ?? null,
                'title'       => $issue['title'] ?? 'Unknown',
                'latest_path' => $issue['latest_path'] ?? null,
                'latest_method' => $issue['latest_method'] ?? null,
                'environment' => $issue['environment'] ?? null,
                'status'      => 'open',
                'first_seen'  => DexTime::nowUtcString(),
                'last_seen'   => $issue['last_seen'] ?? DexTime::nowUtcString(),
                'times_seen'  => 1,
            ], true);
        } catch (Throwable $e) {
            throw RepositoryException::writeFailed('issue', $e);
        }

        if ($id === false) {
            throw RepositoryException::writeFailed('issue');
        }

        return (int) $id;
    }

    private function upsertIssueAtomic(array $issue): int
    {
        $now = DexTime::nowUtcString();
        $lastSeen = $issue['last_seen'] ?? $now;
        $firstSeen = $issue['first_seen'] ?? $now;
        $table = $this->db->protectIdentifiers($this->model->builder()->getTable(), true, false, false);

        $sql = "INSERT INTO {$table} (
                fingerprint, level, class, title, latest_path, latest_method, environment, status, first_seen, last_seen, times_seen
            ) VALUES (?, ?, ?, ?, ?, ?, ?, 'open', ?, ?, 1)
            ON DUPLICATE KEY UPDATE
                id = LAST_INSERT_ID(id),
                last_seen = VALUES(last_seen),
                times_seen = times_seen + 1,
                level = VALUES(level),
                class = COALESCE(VALUES(class), class),
                latest_path = COALESCE(VALUES(latest_path), latest_path),
                latest_method = COALESCE(VALUES(latest_method), latest_method),
                environment = COALESCE(VALUES(environment), environment),
                title = COALESCE(VALUES(title), title),
                status = CASE WHEN status = 'resolved' THEN 'regression' ELSE status END";

        try {
            $ok = $this->db->query($sql, [
                $issue['fingerprint'],
                $issue['level'] ?? 'error',
                $issue['class'] ?? null,
                $issue['title'] ?? 'Unknown',
                $issue['latest_path'] ?? null,
                $issue['latest_method'] ?? null,
                $issue['environment'] ?? null,
                $firstSeen,
                $lastSeen,
            ]);
        } catch (Throwable $e) {
            throw RepositoryException::writeFailed('issue', $e);
        }

        if ($ok === false) {
            throw RepositoryException::writeFailed('issue');
        }

        $id = (int) $this->db->insertID();
        if ($id <= 0) {
            throw RepositoryException::writeFailed('issue');
        }

        return $id;
    }

    public function resolveIssue(int $id): bool
    {
        try {
            $ok = $this->model->update($id, [
                'status' => 'resolved',
            ]);
        } catch (Throwable $e) {
            throw RepositoryException::writeFailed('issue', $e);
        }

        if ($ok === false) {
            throw RepositoryException::writeFailed('issue');
        }

        return true;
    }

    public function ignoreIssue(int $id): bool
    {
        try {
            $ok = $this->model->update($id, [
                'status' => 'ignored',
            ]);
        } catch (Throwable $e) {
            throw RepositoryException::writeFailed('issue', $e);
        }

        if ($ok === false) {
            throw RepositoryException::writeFailed('issue');
        }

        return true;
    }
}

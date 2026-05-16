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

use Dex\Domain\Exceptions\RepositoryException;
use Dex\Models\IssueModel;
use Throwable;

final class IssueReadRepository
{
    private IssueModel $model;
    private const REGRESSED_STATUSES = ['regression', 'regressed'];

    public function __construct(?IssueModel $model = null)
    {
        $this->model = $model ?? new IssueModel();
    }

    public function listIssues(?string $status, string $query, int $limit, int $offset = 0): array
    {
        try {
            $builder = $this->model->builder();

            $this->applyStatusFilter($builder, $status);
            $this->applySearchFilter($builder, $query);

            $builder->select([
                'id',
                'fingerprint',
                'level',
                'class',
                'title',
                'latest_path',
                'latest_method',
                'environment',
                'status',
                'times_seen',
                'first_seen',
                'last_seen',
            ]);
            return $builder->orderBy('last_seen', 'DESC')
                ->offset($offset)
                ->limit($limit)
                ->get()
                ->getResultArray();
        } catch (Throwable $e) {
            throw RepositoryException::readFailed('issues_list', $e);
        }
    }

    public function countIssues(?string $status, string $query): int
    {
        try {
            $builder = $this->model->builder();
            $this->applyStatusFilter($builder, $status);
            $this->applySearchFilter($builder, $query);

            return $builder->countAllResults();
        } catch (Throwable $e) {
            throw RepositoryException::readFailed('issues_count', $e);
        }
    }

    public function countByStatus(): array
    {
        try {
            $rows = $this->model->builder()
                ->select('status, COUNT(*) AS cnt')
                ->groupBy('status')
                ->get()
                ->getResultArray();
        } catch (Throwable $e) {
            throw RepositoryException::readFailed('issues_status_count', $e);
        }

        $counts = [
            'total' => 0,
            'open' => 0,
            'regressed' => 0,
            'resolved' => 0,
            'ignored' => 0,
        ];

        foreach ($rows as $row) {
            $status = strtolower((string) ($row['status'] ?? ''));
            $count = (int) ($row['cnt'] ?? 0);
            $counts['total'] += $count;

            if ($status === 'unhandled') {
                $counts['open'] += $count;
                continue;
            }

            if (in_array($status, self::REGRESSED_STATUSES, true)) {
                $counts['regressed'] += $count;
                continue;
            }

            if (isset($counts[$status])) {
                $counts[$status] += $count;
            }
        }

        return $counts;
    }

    public function findIssue(int $id): ?array
    {
        try {
            $row = $this->model->find($id);
            return is_array($row) ? $row : null;
        } catch (Throwable $e) {
            throw RepositoryException::readFailed('issue_find', $e);
        }
    }

    private function applyStatusFilter(object $builder, ?string $status): void
    {
        $status = strtolower(trim((string) $status));
        if ($status === '' || $status === 'all') {
            return;
        }

        if ($status === 'regressed') {
            $builder->whereIn('status', self::REGRESSED_STATUSES);
            return;
        }

        $builder->where('status', $status);
    }

    private function applySearchFilter(object $builder, string $query): void
    {
        $query = trim($query);
        if ($query === '') {
            return;
        }

        $builder->groupStart()
            ->like('title', $query)
            ->orLike('class', $query)
            ->orLike('latest_path', $query)
            ->orLike('fingerprint', $query)
            ->groupEnd();
    }
}

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
use Dex\Models\OccurrenceModel;
use Throwable;

final class OccurrenceReadRepository
{
    private OccurrenceModel $model;

    public function __construct(?OccurrenceModel $model = null)
    {
        $this->model = $model ?? new OccurrenceModel();
    }

    public function countTotalForIssue(int $issueId): int
    {
        try {
            return $this->model->builder()
                ->where('issue_id', $issueId)
                ->countAllResults();
        } catch (Throwable $e) {
            throw RepositoryException::readFailed('occurrences_total', $e);
        }
    }

    public function listForIssue(int $issueId, int $limit): array
    {
        try {
            return $this->model->builder()
                ->select('id, issue_id, happened_at, request_id, message, context')
                ->where('issue_id', $issueId)
                ->orderBy('happened_at', 'DESC')
                ->limit($limit)
                ->get()->getResultArray();
        } catch (Throwable $e) {
            throw RepositoryException::readFailed('occurrences_list', $e);
        }
    }

    public function listForIssueWithRequests(int $issueId, int $limit): array
    {
        try {
            return $this->model->builder()
                ->select('dex_occurrences.id, dex_occurrences.issue_id, dex_occurrences.happened_at, dex_occurrences.request_id, dex_occurrences.message, dex_occurrences.context')
                ->select('dex_requests.method as request_method, dex_requests.path as request_path, dex_requests.status_code as request_status_code, dex_requests.duration_ms as request_duration_ms, dex_requests.created_at as request_created_at, dex_requests.db_time_ms as request_db_time_ms, dex_requests.db_count as request_db_count, dex_requests.mem_peak as request_mem_peak, dex_requests.controller as request_controller, dex_requests.action as request_action')
                ->join('dex_requests', 'dex_requests.request_id = dex_occurrences.request_id', 'left')
                ->where('dex_occurrences.issue_id', $issueId)
                ->orderBy('dex_occurrences.happened_at', 'DESC')
                ->limit($limit)
                ->get()->getResultArray();
        } catch (Throwable $e) {
            throw RepositoryException::readFailed('occurrences_list_with_requests', $e);
        }
    }

    /**
     * Load a single occurrence with joined request data.
     * When $occurrenceId is null or zero, returns the latest occurrence for the issue.
     */
    public function findOccurrenceWithRequestForIssue(int $issueId, ?int $occurrenceId): ?array
    {
        try {
            $builder = $this->model->builder()
                ->select('dex_occurrences.id, dex_occurrences.issue_id, dex_occurrences.happened_at, dex_occurrences.request_id, dex_occurrences.message, dex_occurrences.context')
                ->select('dex_requests.method as request_method, dex_requests.path as request_path, dex_requests.status_code as request_status_code, dex_requests.duration_ms as request_duration_ms, dex_requests.created_at as request_created_at, dex_requests.db_time_ms as request_db_time_ms, dex_requests.db_count as request_db_count, dex_requests.mem_peak as request_mem_peak, dex_requests.controller as request_controller, dex_requests.action as request_action')
                ->join('dex_requests', 'dex_requests.request_id = dex_occurrences.request_id', 'left')
                ->where('dex_occurrences.issue_id', $issueId);

            if ($occurrenceId !== null && $occurrenceId > 0) {
                $builder->where('dex_occurrences.id', $occurrenceId);
            } else {
                $builder->orderBy('dex_occurrences.happened_at', 'DESC')
                    ->orderBy('dex_occurrences.id', 'DESC');
            }

            $row = $builder->limit(1)->get()->getRowArray();

            return is_array($row) ? $row : null;
        } catch (Throwable $e) {
            throw RepositoryException::readFailed('occurrence_find_with_request', $e);
        }
    }

    /**
     * Return the 1-based position of an occurrence inside the issue's
     * newest-first occurrence list. Returns 0 when the occurrence is not part of the issue.
     */
    public function findOccurrencePositionForIssue(int $issueId, int $occurrenceId, string $happenedAt): int
    {
        if ($issueId <= 0 || $occurrenceId <= 0 || $happenedAt === '') {
            return 0;
        }

        try {
            $newerCount = $this->model->builder()
                ->where('issue_id', $issueId)
                ->groupStart()
                    ->where('happened_at >', $happenedAt)
                    ->orGroupStart()
                        ->where('happened_at', $happenedAt)
                        ->where('id >', $occurrenceId)
                    ->groupEnd()
                ->groupEnd()
                ->countAllResults();

            return $newerCount + 1;
        } catch (Throwable $e) {
            throw RepositoryException::readFailed('occurrence_position', $e);
        }
    }

    /**
     * Return the ID of the occurrence immediately newer or older than the supplied
     * one in the issue's newest-first ordering.
     */
    public function findAdjacentOccurrenceIdForIssue(
        int $issueId,
        int $occurrenceId,
        string $happenedAt,
        string $direction
    ): ?int {
        if ($issueId <= 0 || $occurrenceId <= 0 || $happenedAt === '' || !in_array($direction, ['newer', 'older'], true)) {
            return null;
        }

        try {
            $builder = $this->model->builder()
                ->select('id')
                ->where('issue_id', $issueId);

            if ($direction === 'newer') {
                $builder->groupStart()
                        ->where('happened_at >', $happenedAt)
                        ->orGroupStart()
                            ->where('happened_at', $happenedAt)
                            ->where('id >', $occurrenceId)
                        ->groupEnd()
                    ->groupEnd()
                    ->orderBy('happened_at', 'ASC')
                    ->orderBy('id', 'ASC');
            } else {
                $builder->groupStart()
                        ->where('happened_at <', $happenedAt)
                        ->orGroupStart()
                            ->where('happened_at', $happenedAt)
                            ->where('id <', $occurrenceId)
                        ->groupEnd()
                    ->groupEnd()
                    ->orderBy('happened_at', 'DESC')
                    ->orderBy('id', 'DESC');
            }

            $row = $builder->limit(1)->get()->getRowArray();

            return is_array($row) ? (int) $row['id'] : null;
        } catch (Throwable $e) {
            throw RepositoryException::readFailed('occurrence_adjacent', $e);
        }
    }

    public function findOccurrence(int $id): ?array
    {
        try {
            $row = $this->model->find($id);
            return is_array($row) ? $row : null;
        } catch (Throwable $e) {
            throw RepositoryException::readFailed('occurrence_find', $e);
        }
    }

    public function countForIssueBetween(int $issueId, string $since, ?string $until = null): int
    {
        try {
            $builder = $this->model->builder()
                ->where('issue_id', $issueId)
                ->where('happened_at >=', $since);
            if ($until) {
                $builder->where('happened_at <', $until);
            }
            return $builder->countAllResults();
        } catch (Throwable $e) {
            throw RepositoryException::readFailed('occurrences_count', $e);
        }
    }

    public function countByIssueIdsSince(array $ids, string $since, ?string $until = null): array
    {
        if (empty($ids)) {
            return [];
        }

        try {
            $builder = $this->model->builder()
                ->select('issue_id, COUNT(*) as cnt')
                ->whereIn('issue_id', $ids)
                ->where('happened_at >=', $since);

            if ($until) {
                $builder->where('happened_at <', $until);
            }

            $rows = $builder->groupBy('issue_id')->get()->getResultArray();
        } catch (Throwable $e) {
            throw RepositoryException::readFailed('occurrences_group_count', $e);
        }

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row['issue_id']] = (int) $row['cnt'];
        }

        return $map;
    }

    public function sparkRows(array $issueIds, string $since, string $bucketExpr): array
    {
        if (empty($issueIds)) {
            return [];
        }

        try {
            return $this->model->builder()
                ->select("issue_id, {$bucketExpr} AS hb, COUNT(*) AS cnt", false)
                ->whereIn('issue_id', $issueIds)
                ->where('happened_at >=', $since)
                ->groupBy('issue_id, hb', false)
                ->get()->getResultArray();
        } catch (Throwable $e) {
            throw RepositoryException::readFailed('occurrences_spark', $e);
        }
    }

    public function hourlyTotalRows(string $since, string $bucketExpr): array
    {
        try {
            return $this->model->builder()
                ->select("{$bucketExpr} AS hb, COUNT(*) AS cnt", false)
                ->where('happened_at >=', $since)
                ->groupBy('hb', false)
                ->get()->getResultArray();
        } catch (Throwable $e) {
            throw RepositoryException::readFailed('occurrences_hourly_total', $e);
        }
    }

    public function countTotalSince(string $since, ?string $until = null): int
    {
        try {
            $builder = $this->model->builder()
                ->where('happened_at >=', $since);

            if ($until) {
                $builder->where('happened_at <', $until);
            }

            return $builder->countAllResults();
        } catch (Throwable $e) {
            throw RepositoryException::readFailed('occurrences_total_since', $e);
        }
    }

    public function hourlyCountRows(int $issueId, string $since, string $bucketExpr): array
    {
        try {
            return $this->model->builder()
                ->select("{$bucketExpr} AS hb, COUNT(*) AS cnt", false)
                ->where('issue_id', $issueId)
                ->where('happened_at >=', $since)
                ->groupBy('hb', false)
                ->get()->getResultArray();
        } catch (Throwable $e) {
            throw RepositoryException::readFailed('occurrences_hourly', $e);
        }
    }

    public function listHappenedAtSince(int $issueId, string $since, int $limit): array
    {
        try {
            return $this->model->builder()
                ->select('happened_at')
                ->where('issue_id', $issueId)
                ->where('happened_at >=', $since)
                ->limit($limit)
                ->get()->getResultArray();
        } catch (Throwable $e) {
            throw RepositoryException::readFailed('occurrences_since', $e);
        }
    }

    public function contextRowsSince(int $issueId, string $since, int $limit): array
    {
        try {
            return $this->model->builder()
                ->select('request_id, context')
                ->where('issue_id', $issueId)
                ->where('happened_at >=', $since)
                ->limit($limit)
                ->get()->getResultArray();
        } catch (Throwable $e) {
            throw RepositoryException::readFailed('occurrences_context', $e);
        }
    }

    public function listForRequestWithIssue(string $requestId, int $limit): array
    {
        try {
            return $this->model->builder()
                ->select('dex_occurrences.id, dex_occurrences.issue_id, dex_occurrences.happened_at, dex_occurrences.message, dex_issues.title, dex_issues.level')
                ->join('dex_issues', 'dex_issues.id = dex_occurrences.issue_id', 'left')
                ->where('dex_occurrences.request_id', $requestId)
                ->orderBy('dex_occurrences.id', 'DESC')
                ->limit($limit)
                ->get()->getResultArray();
        } catch (Throwable $e) {
            throw RepositoryException::readFailed('occurrences_by_request', $e);
        }
    }
}

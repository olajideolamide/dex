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
use Dex\Models\RequestModel;
use Throwable;

final class RequestReadRepository
{
    private RequestModel $model;

    public function __construct(?RequestModel $model = null)
    {
        $this->model = $model ?? new RequestModel();
    }

    public function findLatestByRequestId(string $requestId): ?array
    {
        try {
            $row = $this->model->where('request_id', $requestId)
                ->orderBy('id', 'DESC')
                ->first();
            return is_array($row) ? $row : null;
        } catch (Throwable $e) {
            throw RepositoryException::readFailed('request_find', $e);
        }
    }

    public function listSimilarRequests(string $path, string $method, string $since, int $limit): array
    {
        try {
            return $this->model->builder()
                ->select('request_id, created_at, status_code, duration_ms, db_time_ms, mem_peak')
                ->where('created_at >=', $since)
                ->where('path', $path)
                ->where('method', $method)
                ->limit($limit)
                ->get()->getResultArray();
        } catch (Throwable $e) {
            throw RepositoryException::readFailed('requests_similar', $e);
        }
    }

    public function listSimilarRecent(string $path, string $method, int $limit): array
    {
        try {
            return $this->model->builder()
                ->select('request_id, created_at, status_code, duration_ms, db_time_ms, mem_peak')
                ->where('path', $path)
                ->where('method', $method)
                ->orderBy('created_at', 'DESC')
                ->limit($limit)
                ->get()->getResultArray();
        } catch (Throwable $e) {
            throw RepositoryException::readFailed('requests_similar_recent', $e);
        }
    }

    public function listSimilarDurations(string $path, string $method, string $since, int $limit): array
    {
        try {
            return $this->model->builder()
                ->select('created_at, duration_ms')
                ->where('created_at >=', $since)
                ->where('path', $path)
                ->where('method', $method)
                ->orderBy('created_at', 'ASC')
                ->limit($limit)
                ->get()->getResultArray();
        } catch (Throwable $e) {
            throw RepositoryException::readFailed('requests_similar_duration', $e);
        }
    }

    /**
     * @return array<string, array>
     */
    public function findLatestByRequestIds(array $requestIds): array
    {
        $requestIds = array_values(array_filter(array_map(
            static fn(mixed $requestId): string => trim((string) $requestId),
            $requestIds
        )));

        if ($requestIds === []) {
            return [];
        }

        try {
            $rows = $this->model->builder()
                ->whereIn('request_id', array_unique($requestIds))
                ->orderBy('id', 'DESC')
                ->get()
                ->getResultArray();
        } catch (Throwable $e) {
            throw RepositoryException::readFailed('request_find_many', $e);
        }

        $map = [];
        foreach ($rows as $row) {
            $requestId = (string) ($row['request_id'] ?? '');
            if ($requestId === '' || isset($map[$requestId])) {
                continue;
            }
            $map[$requestId] = $row;
        }

        return $map;
    }
}

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

use Dex\Contracts\StorageInterface;
use Dex\DTO\ResponseMeta;
use Dex\Support\DexTime;
use Dex\Support\RequestSnapshot;
use Dex\Support\Scrubber;

/**
 * Builds and stores the request row for normal and crash-path finalization.
 */
final class RequestFinalizerService
{
    public function __construct(
        private readonly object $config,
        private readonly StorageInterface $storage,
        private readonly RequestLifecycleService $requestLifecycleService,
    ) {
    }

    /**
     * Persist a request row at request end or crash-path.
     */
    public function record(
        array &$ctx,
        int $statusCode,
        int $durationMs,
        int $memPeak,
        ?ResponseMeta $response
    ): void {
        $row = $this->buildRow($ctx, $statusCode, $durationMs, $memPeak);

        if (($this->config->captureRequestSnapshots ?? true) === true) {
            $snapshot = RequestSnapshot::build($ctx, $this->config, $response);
            $snapshot = Scrubber::scrub($snapshot, (array) ($this->config->scrubFields ?? []));
            $row['snapshot_json'] = Scrubber::safeJson($snapshot, (int) ($this->config->maxSnapshotBytes ?? 48000));
        }

        $this->storage->recordRequest($row);
    }

    /**
     * Build a dex_requests row with safe defaults for crash-path contexts.
     */
    private function buildRow(array &$ctx, int $statusCode, int $durationMs, int $memPeak): array
    {
        $createdAt = DexTime::nowUtcString();
        $ctx['_created_at'] = $createdAt;

        $this->requestLifecycleService->setContext($ctx);
        $lifecycle = $this->requestLifecycleService->finalize($durationMs, $statusCode, $memPeak);
        $lifecycle = Scrubber::scrub($lifecycle, (array) ($this->config->scrubFields ?? []));
        $summary = is_array($lifecycle['summary'] ?? null) ? $lifecycle['summary'] : [];

        return [
            'request_id' => $ctx['request_id'] ?? null,
            'method' => $ctx['method'] ?? null,
            'path' => $ctx['path'] ?? null,

            'status_code' => $statusCode,
            'duration_ms' => $durationMs,
            'mem_peak' => $memPeak,

            'db_count' => (int) ($ctx['db_count'] ?? 0),
            'db_time_ms' => (int) round((float) ($ctx['db_time_ms'] ?? 0)),

            'controller' => $ctx['controller'] ?? null,
            'action' => $ctx['action'] ?? null,

            'created_at' => $createdAt,

            'lifecycle_json' => Scrubber::safeJson($lifecycle, (int) ($this->config->maxLifecycleBytes ?? 128000)),
            'lifecycle_version' => 2,
            'has_error' => (int) ($ctx['had_error'] ?? false),
            'has_exception' => (int) (((int) ($summary['exception_count'] ?? 0)) > 0),
            'slow_request' => (int) $this->isSlowRequest($durationMs),
            'slow_query_count' => (int) ($summary['slow_query_count'] ?? 0),
            'slowest_query_ms' => (int) ($summary['slowest_query_ms'] ?? 0),
            'lifecycle_event_count' => (int) ($summary['event_count'] ?? 0),
            'manual_span_count' => (int) ($summary['manual_span_count'] ?? 0),
            'breadcrumb_count' => (int) ($summary['breadcrumb_count'] ?? 0),
        ];
    }

    private function isSlowRequest(int $durationMs): bool
    {
        return $durationMs >= (int) ($this->config->slowRequestMs ?? 1000);
    }
}

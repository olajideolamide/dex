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

use Dex\DTO\RequestMeta;
use Dex\Domain\Exceptions\DexException;
use Dex\Support\DexTime;

/**
 * Manages request context lifecycle: initialization, state tracking, and cleanup.
 * Central service that all other request-related services depend on.
 */
final class RequestContextService
{
    private ?array $ctx = null;

    public function __construct(
        private readonly object $config,
    ) {
    }

    /**
     * Initialize a new request context from RequestMeta.
     * Should be called at the start of request processing.
     */
    public function start(RequestMeta $meta): void
    {
        // Generate request ID: use incoming ID if provided, otherwise generate a new one
        $requestId = !empty($meta->incomingRequestId)
            ? $meta->incomingRequestId
            : bin2hex(random_bytes(16));

        $this->ctx = [
            'request_id' => $requestId,
            'started_at' => microtime(true),
            'started_at_dt' => DexTime::nowUtcString(),
            'method' => $meta->method,
            'path' => $meta->rawPath,
            'ip' => $meta->ip,
            'user_agent' => $meta->userAgent,
            'query_string' => null,
            'controller' => null,
            'action' => null,
            'route' => null,
            'route_params' => [],
            'had_error' => false,
            'request_recorded' => false,
            'db_count' => 0,
            'db_time_ms' => 0.0,
            'slow_query_count' => 0,
            'slowest_query_ms' => 0,
            'lifecycle' => [
                'version' => 2,
                'items' => [],
                'index_by_id' => [],
                'open_span_stack' => [],
                'summary' => [
                    'event_count' => 0,
                    'span_count' => 0,
                    'manual_span_count' => 0,
                    'breadcrumb_count' => 0,
                    'db_query_count' => 0,
                    'exception_count' => 0,
                    'max_depth' => 0,
                    'truncated' => false,
                ],
            ],
            'filters_before' => [],
            'filters_after' => [],
            'filters_before_count' => 0,
            'filters_after_count' => 0,
        ];
    }

    /**
     * Get the current request context array.
     */
    public function getContext(): ?array
    {
        return $this->ctx;
    }

    /**
     * Get the current request context array by reference.
     *
     * Use this when another service needs to mutate the live request context
     * (e.g. to append breadcrumbs/spans) so the changes are visible everywhere.
     *
     * @return array|null
     */
    public function &getContextRef(): ?array
    {
        return $this->ctx;
    }

    /**
     * Update context with controller/action information.
     */
    public function attachControllerInfo(string $controller, string $action): void
    {
        if ($this->ctx) {
            $this->ctx['controller'] = $controller;
            $this->ctx['action'] = $action;
        }
    }

    /**
     * Attach raw CI request object for later reference.
     */
    public function attachRawRequest(object $request): void
    {
        if ($this->ctx) {
            $this->ctx['_request'] = $request;
        }
    }

    /**
     * Mark that an error occurred in this request.
     */
    public function markError(): void
    {
        if ($this->ctx) {
            $this->ctx['had_error'] = true;
        }
    }

    /**
     * Get whether an error occurred in this request.
     */
    public function hasError(): bool
    {
        return (bool)($this->ctx['had_error'] ?? false);
    }

    /**
     * Record that the request has been persisted.
     */
    public function markRecorded(): void
    {
        if ($this->ctx) {
            $this->ctx['request_recorded'] = true;
        }
    }

    /**
     * Check if the request has already been recorded.
     */
    public function isRecorded(): bool
    {
        return (bool)($this->ctx['request_recorded'] ?? false);
    }

    /**
     * Get elapsed milliseconds since request start.
     */
    public function getElapsedMs(): int
    {
        if (!$this->ctx) {
            return 0;
        }
        return (int)round((microtime(true) - (float)$this->ctx['started_at']) * 1000);
    }

    /**
     * Decide if a request should be stored.
     * Dex stores request records only when tied to an error/exception/fatal.
     * Returns: [$shouldStore, $slowHit, $sampleHit]
     */
    public function shouldStoreRequest(int $statusCode, int $durationMs): array
    {
        if ($statusCode === 404) {
            return [false, false, false];
        }

        $errorHit = $this->hasError() || $statusCode >= 500;
        if ($errorHit) {
            return [true, false, false];
        }

        return [false, false, false];
    }

    /**
     * Validate request ID format and length.
     */
    public function isValidRequestId(string $id): bool
    {
        if ($id === '') {
            return false;
        }
        if (strlen($id) < 8 || strlen($id) > 80) {
            return false;
        }
        return (bool)preg_match('/^[A-Za-z0-9\-_\.]+$/', $id);
    }

    /**
     * Store snapshot flags from storage decision.
     */
    public function storeSnapshotFlags(bool $slowHit, bool $sampleHit): void
    {
        if ($this->ctx) {
            $this->ctx['_slow_hit'] = $slowHit;
            $this->ctx['_sample_hit'] = $sampleHit;
        }
    }

    /**
     * Store final metrics in context.
     */
    public function storeFinalMetrics(int $statusCode, int $durationMs, int $memPeak): void
    {
        if ($this->ctx) {
            $this->ctx['status_code'] = $statusCode;
            $this->ctx['_duration_ms'] = $durationMs;
            $this->ctx['_mem_peak'] = $memPeak;
        }
    }

    /**
     * Clear per-request state for long-running workers to avoid data leaks.
     */
    public function reset(): void
    {
        $this->ctx = null;
    }

    /**
     * Handle domain exception with logging.
     */
    public function handleDomainException(DexException|null $e): void
    {
        // Log or handle the exception - for now, silently ignore to avoid cascading errors
        // In the future, this could log to PHP error log if needed
    }
}

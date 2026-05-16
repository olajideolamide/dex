<?php

/**
 * This file is part of DEX.
 *
 * (c) Olajide Olanrewaju <jidemac4@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Dex\Services\Core;

use Dex\Support\DexRuntimePolicy;
use Throwable;

/**
 * Tracks DB query telemetry as first-class lifecycle items.
 */
final class QueryTrackingService
{
    /**
     * @var array<string, string>
     */
    private array $durationStrategyByClass = [];

    public function __construct(
        private readonly object $config,
        private readonly RequestLifecycleService $requestLifecycleService,
        private readonly DexRuntimePolicy $runtimePolicy,
    ) {
    }

    /**
     * Track a single query in the request lifecycle.
     *
     * @param object $query CI4 query object (expects getDuration()/duration and getQuery()).
     * @param array  $ctx   Request context array (passed by reference).
     */
    public function track(object $query, array &$ctx, ?string $sql = null): void
    {
        if (!$this->runtimePolicy->shouldRunContext($ctx)) {
            return;
        }

        $durationMs = $this->durationMs($query);
        $sql ??= $this->querySql($query) ?? '';

        $ctx['db_count'] = (int) ($ctx['db_count'] ?? 0) + 1;
        $ctx['db_time_ms'] = (float) ($ctx['db_time_ms'] ?? 0.0) + $durationMs;

        $this->requestLifecycleService->dbQuery(
            $sql,
            $this->spanDurationMs($durationMs),
            $this->storedDurationMs($durationMs)
        );
    }

    /**
     * Normalize CI query duration to milliseconds.
     */
    private function durationMs(object $query): float
    {
        $strategy = $this->durationStrategyByClass[$query::class] ?? $this->detectDurationStrategy($query);
        $this->durationStrategyByClass[$query::class] = $strategy;

        $duration = match ($strategy) {
            'property' => (float) $query->duration,
            'precision_method' => (float) $query->getDuration(9),
            'method' => (float) $query->getDuration(),
            default => 0.0,
        };

        return $duration > 0.0 ? $duration * 1000 : 0.0;
    }

    private function spanDurationMs(float $durationMs): float
    {
        if ($durationMs <= 0.0) {
            return 0.0;
        }

        return round($durationMs, 3);
    }

    private function storedDurationMs(float $durationMs): int
    {
        if ($durationMs <= 0.0) {
            return 0;
        }

        return max(1, (int) round($durationMs));
    }

    /**
     * Extract SQL from CI query object.
     */
    private function querySql(object $query): ?string
    {
        if (isset($query->query) && is_string($query->query)) {
            return $query->query;
        }

        if (method_exists($query, 'getQuery')) {
            $sql = $query->getQuery();
            if (is_string($sql)) {
                return $sql;
            }
        }

        return null;
    }

    private function detectDurationStrategy(object $query): string
    {
        if (isset($query->duration)) {
            return 'property';
        }

        if (!method_exists($query, 'getDuration')) {
            return 'none';
        }

        try {
            /** @phpstan-ignore-next-line */
            $query->getDuration(9);

            return 'precision_method';
        } catch (Throwable) {
            return 'method';
        }
    }
}

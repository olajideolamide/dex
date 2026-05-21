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

namespace Dex\Tests\Support\Factories;

use Dex\Support\DexTime;

/**
 * Test data factory for dex_requests rows.
 */
final class RequestFactory
{
    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    public static function normal(array $overrides = []): array
    {
        return array_merge(self::base(), $overrides);
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    public static function withException(array $overrides = []): array
    {
        return array_merge(self::base(), [
            'has_error'     => 1,
            'has_exception' => 1,
            'status_code'   => 500,
        ], $overrides);
    }

    /**
     * @param array<mixed>         $lifecycle
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    public static function withLifecycle(array $lifecycle, array $overrides = []): array
    {
        $summary = $lifecycle['summary'] ?? [];

        return array_merge(self::base(), [
            'lifecycle_json'        => json_encode($lifecycle, JSON_UNESCAPED_SLASHES),
            'lifecycle_event_count' => (int) ($summary['event_count'] ?? 0),
            'manual_span_count'     => (int) ($summary['manual_span_count'] ?? 0),
            'breadcrumb_count'      => (int) ($summary['breadcrumb_count'] ?? 0),
            'slow_query_count'      => (int) ($summary['slow_query_count'] ?? 0),
            'slowest_query_ms'      => (int) ($summary['slowest_query_ms'] ?? 0),
            'has_exception'         => (int) (((int) ($summary['exception_count'] ?? 0)) > 0),
        ], $overrides);
    }

    /**
     * @return array<string, mixed>
     */
    private static function base(): array
    {
        return [
            'request_id'            => 'req-' . uniqid('', true),
            'method'                => 'GET',
            'path'                  => '/test',
            'status_code'           => 200,
            'duration_ms'           => 100,
            'mem_peak'              => 2048000,
            'db_count'              => 0,
            'db_time_ms'            => 0,
            'controller'            => null,
            'action'                => null,
            'snapshot_json'         => null,
            'lifecycle_json'        => null,
            'lifecycle_version'     => 2,
            'has_error'             => 0,
            'has_exception'         => 0,
            'slow_request'          => 0,
            'slow_query_count'      => 0,
            'slowest_query_ms'      => 0,
            'lifecycle_event_count' => 0,
            'manual_span_count'     => 0,
            'breadcrumb_count'      => 0,
            'created_at'            => DexTime::nowUtcString(),
        ];
    }
}

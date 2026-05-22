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

/**
 * Test data factory for lifecycle JSON payloads.
 *
 * Produces lifecycle arrays shaped like those produced by RequestLifecycleService::finalize().
 */
final class LifecycleFactory
{
    /**
     * A realistic e-commerce checkout failure lifecycle.
     *
     * 3 manual spans, 4 breadcrumbs, 5 DB queries, 2 slow, 1 exception.
     *
     * @return array<string, mixed>
     */
    public static function ecommerceCheckoutFailure(): array
    {
        $items = [
            self::checkpoint('request.started', 'Request started', 0.0),
            self::checkpoint('route.matched', 'Route matched', 1.2),
            self::span('checkout.validate', 'Validate cart', 2.0, 15.0, 'manual'),
            self::breadcrumb('cart', 'Cart loaded with 3 items', 2.5),
            self::dbQuery('SELECT * FROM carts WHERE id = ?', 3.0, 8),
            self::dbQuery('SELECT * FROM products WHERE id IN (?)', 5.0, 12),
            self::breadcrumb('payment', 'Payment gateway initialised', 10.0),
            self::span('checkout.payment', 'Payment processing', 12.0, 45.0, 'manual'),
            self::dbQuery('INSERT INTO orders VALUES (?)', 20.0, 5),
            self::dbQuery('UPDATE inventory SET qty = qty - 1 WHERE ...', 22.0, 150, true),
            self::dbQuery('INSERT INTO audit_log ...', 24.0, 200, true),
            self::breadcrumb('payment', 'Gateway timeout', 30.0, 'error'),
            self::exceptionItem('App\\Exceptions\\PaymentGatewayException', 'Payment gateway timed out', 30.5),
            self::breadcrumb('checkout', 'Rolling back transaction', 31.0),
            self::span('checkout.rollback', 'Transaction rollback', 31.0, 35.0, 'manual'),
            self::checkpoint('response.sent', 'Response sent', 40.0),
        ];

        return [
            'version'    => 2,
            'started_at' => time() - 1,
            'items'      => $items,
            'summary'    => [
                'event_count'      => count($items),
                'exception_count'  => 1,
                'manual_span_count' => 3,
                'breadcrumb_count' => 4,
                'db_count'         => 5,
                'db_time_ms'       => 375,
                'slow_query_count' => 2,
                'slowest_query_ms' => 200,
            ],
        ];
    }

    /**
     * An API validation failure lifecycle.
     *
     * @return array<string, mixed>
     */
    public static function apiValidationFailure(): array
    {
        $items = [
            self::checkpoint('request.started', 'Request started', 0.0),
            self::checkpoint('route.matched', 'Route matched', 0.8),
            self::breadcrumb('validation', 'Validating request body', 1.5),
            self::exceptionItem('CodeIgniter\\Validation\\ValidationException', 'Validation failed', 2.0),
            self::checkpoint('response.sent', 'Response sent', 2.5),
        ];

        return [
            'version'    => 2,
            'started_at' => time() - 1,
            'items'      => $items,
            'summary'    => [
                'event_count'       => count($items),
                'exception_count'   => 1,
                'manual_span_count' => 0,
                'breadcrumb_count'  => 1,
                'db_count'          => 0,
                'db_time_ms'        => 0,
                'slow_query_count'  => 0,
                'slowest_query_ms'  => 0,
            ],
        ];
    }

    /**
     * A slow database request lifecycle.
     *
     * @return array<string, mixed>
     */
    public static function slowDatabaseRequest(): array
    {
        $items = [
            self::checkpoint('request.started', 'Request started', 0.0),
            self::checkpoint('route.matched', 'Route matched', 1.0),
            self::dbQuery('SELECT * FROM reports WHERE ...', 2.0, 1200, true),
            self::dbQuery('SELECT * FROM summaries WHERE ...', 1205.0, 800, true),
            self::checkpoint('response.sent', 'Response sent', 2010.0),
        ];

        return [
            'version'    => 2,
            'started_at' => time() - 3,
            'items'      => $items,
            'summary'    => [
                'event_count'       => count($items),
                'exception_count'   => 0,
                'manual_span_count' => 0,
                'breadcrumb_count'  => 0,
                'db_count'          => 2,
                'db_time_ms'        => 2000,
                'slow_query_count'  => 2,
                'slowest_query_ms'  => 1200,
            ],
        ];
    }

    /**
     * An empty, valid lifecycle payload.
     *
     * @return array<string, mixed>
     */
    public static function empty(): array
    {
        return [
            'version'    => 2,
            'started_at' => time(),
            'items'      => [],
            'summary'    => [
                'event_count'       => 0,
                'exception_count'   => 0,
                'manual_span_count' => 0,
                'breadcrumb_count'  => 0,
                'db_count'          => 0,
                'db_time_ms'        => 0,
                'slow_query_count'  => 0,
                'slowest_query_ms'  => 0,
            ],
        ];
    }

    // ─── Private item builders ────────────────────────────────────────────────

    /**
     * @param array<string, mixed> $metadata
     * @return array<string, mixed>
     */
    private static function checkpoint(string $name, string $label, float $ms, array $metadata = []): array
    {
        return [
            'id'          => 'chk-' . uniqid('', true),
            'type'        => 'checkpoint',
            'name'        => $name,
            'label'       => $label,
            'source'      => 'system',
            'origin'      => 'auto',
            'start_ms'    => $ms,
            'end_ms'      => null,
            'duration_ms' => null,
            'parent_id'   => null,
            'depth'       => 0,
            'status'      => 'ok',
            'data'        => $metadata,
        ];
    }

    /**
     * @param array<string, mixed> $metadata
     * @return array<string, mixed>
     */
    private static function span(string $name, string $label, float $startMs, float $endMs, string $source = 'manual', array $metadata = []): array
    {
        return [
            'id'          => 'spn-' . uniqid('', true),
            'type'        => 'span',
            'name'        => $name,
            'label'       => $label,
            'source'      => $source,
            'origin'      => 'manual',
            'start_ms'    => $startMs,
            'end_ms'      => $endMs,
            'duration_ms' => round($endMs - $startMs, 3),
            'parent_id'   => null,
            'depth'       => 0,
            'status'      => 'ok',
            'data'        => $metadata,
        ];
    }

    /**
     * @param array<string, mixed> $metadata
     * @return array<string, mixed>
     */
    private static function breadcrumb(string $category, string $message, float $ms, string $level = 'info', array $metadata = []): array
    {
        return [
            'id'          => 'brd-' . uniqid('', true),
            'type'        => 'breadcrumb',
            'name'        => 'breadcrumb.' . $category,
            'label'       => $message,
            'source'      => 'manual',
            'origin'      => 'manual',
            'start_ms'    => $ms,
            'end_ms'      => null,
            'duration_ms' => null,
            'parent_id'   => null,
            'depth'       => 0,
            'status'      => $level === 'error' ? 'failed' : 'ok',
            'data'        => array_merge(['category' => $category, 'level' => $level], $metadata),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function dbQuery(string $sql, float $ms, int $durationMs, bool $slow = false): array
    {
        return [
            'id'          => 'qry-' . uniqid('', true),
            'type'        => 'db_query',
            'name'        => 'db.query',
            'label'       => 'DB query',
            'source'      => 'db',
            'origin'      => 'auto',
            'start_ms'    => $ms,
            'end_ms'      => round($ms + $durationMs, 3),
            'duration_ms' => (float) $durationMs,
            'parent_id'   => null,
            'depth'       => 0,
            'status'      => 'ok',
            'data'        => ['sql' => $sql, 'slow' => $slow],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function exceptionItem(string $class, string $message, float $ms): array
    {
        return [
            'id'          => 'exc-' . uniqid('', true),
            'type'        => 'exception',
            'name'        => 'exception.thrown',
            'label'       => $message,
            'source'      => 'system',
            'origin'      => 'auto',
            'start_ms'    => $ms,
            'end_ms'      => null,
            'duration_ms' => null,
            'parent_id'   => null,
            'depth'       => 0,
            'status'      => 'failed',
            'data'        => ['class' => $class, 'message' => $message],
        ];
    }
}

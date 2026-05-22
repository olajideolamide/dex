<?php

declare(strict_types=1);

namespace Dex\Tests\Telemetry;

use Dex\Services\Core\RequestLifecycleService;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class RequestLifecycleServiceTest extends TestCase
{
    public function testCollectsNestedLifecycleItemsAndFinalizesOpenSpans(): void
    {
        $service = new RequestLifecycleService($this->config());
        $ctx = $this->context();
        $service->setContext($ctx);

        $service->checkpoint('request.started', 'Request started');
        $spanId = $service->startSpan('payment.charge', 'Charge provider', [], 'manual', 'manual');
        $service->checkpoint('payment.charge.prepared', 'Charge request prepared');

        $childSpanId = $service->startSpan('payment.http', 'Provider request', [], 'manual', 'manual');
        $service->checkpoint('payment.http.sent', 'Provider request sent');
        $service->finishSpan($childSpanId, 'ok');

        $service->checkpoint('payment.charge.completed', 'Charge completed');
        $service->breadcrumb('payment', 'Preparing request', [], 'info', 'manual');
        $service->exception(new RuntimeException('Provider timeout'));

        $payload = $service->finalize(150, 500, 4096);

        $this->assertNotNull($spanId);
        $this->assertNotNull($childSpanId);
        $this->assertSame(2, $payload['version']);
        $this->assertSame(500, $payload['request']['status_code']);
        $this->assertGreaterThanOrEqual(5, $payload['summary']['event_count']);
        $this->assertSame(2, $payload['summary']['manual_span_count']);
        $this->assertSame(1, $payload['summary']['breadcrumb_count']);
        $this->assertSame(1, $payload['summary']['exception_count']);

        $span = $this->findItem($payload['items'], (string) $spanId);
        $this->assertSame('failed', $span['status']);
        $this->assertNull($this->findItemOrNull($payload['items'], 'response.generated', 'name'));
        $this->assertNull($this->findItemOrNull($payload['items'], 'request.finished', 'name'));
        $this->assertIsNumeric($span['end_ms']);
        $this->assertLessThanOrEqual(160, (float) $span['end_ms'], 'Span end_ms should not exceed request duration + tolerance');

        $prepared = $this->findItem($payload['items'], 'payment.charge.prepared', 'name');
        $this->assertSame($spanId, $prepared['parent_id']);
        $this->assertSame(1, $prepared['depth']);

        $childSpan = $this->findItem($payload['items'], (string) $childSpanId);
        $this->assertSame($spanId, $childSpan['parent_id']);
        $this->assertSame(1, $childSpan['depth']);

        $sent = $this->findItem($payload['items'], 'payment.http.sent', 'name');
        $this->assertSame($childSpanId, $sent['parent_id']);
        $this->assertSame(2, $sent['depth']);

        $completed = $this->findItem($payload['items'], 'payment.charge.completed', 'name');
        $this->assertSame($spanId, $completed['parent_id']);
        $this->assertSame(1, $completed['depth']);
    }

    public function testDbQueryUpdatesSummaryCountersAndSanitizesSql(): void
    {
        $service = new RequestLifecycleService($this->config([
            'slowQueryMs' => 100,
            'maxSqlLength' => 200,
        ]));
        $ctx = $this->context();
        $service->setContext($ctx);
        $service->dbQuery("SELECT * FROM users WHERE id = 10 AND name = 'Alice'", 120.4, 120);
        $service->dbQuery("SELECT * FROM users WHERE id = 20 AND name = 'Bob'", 80.1, 80);
        $payload = $service->finalize(250, 200, 4096);
        $queryItems = array_values(array_filter($payload['items'], static fn(array $item): bool => ($item['type'] ?? '') === 'db_query'));
        $this->assertSame(2, $payload['summary']['db_query_count']);
        $this->assertSame(1, $payload['summary']['slow_query_count']);
        $this->assertCount(2, $queryItems);
        $this->assertStringNotContainsString('10', (string) ($queryItems[0]['data']['sql'] ?? ''));
        $this->assertStringNotContainsString('Alice', (string) ($queryItems[0]['data']['sql'] ?? ''));
        $this->assertStringNotContainsString('20', (string) ($queryItems[1]['data']['sql'] ?? ''));
        $this->assertStringNotContainsString('Bob', (string) ($queryItems[1]['data']['sql'] ?? ''));
    }

    public function testLifecycleItemCountCapMarksSummaryAsTruncated(): void
    {
        $service = new RequestLifecycleService($this->config([
            'maxLifecycleItems' => 2,
        ]));
        $ctx = $this->context();
        $service->setContext($ctx);
        $service->checkpoint('request.started', 'Request started');
        $service->checkpoint('request.middleware', 'Middleware');
        $service->checkpoint('request.controller', 'Controller');
        $payload = $service->finalize(120, 200, 2048);
        $this->assertTrue($payload['summary']['truncated']);
        $this->assertCount(2, $payload['items']);
    }

    public function testLifecycleMetadataIsCappedAndMarkedTruncated(): void
    {
        $service = new RequestLifecycleService($this->config([
            'maxLifecycleItemDataBytes' => 256,
        ]));
        $ctx = $this->context();
        $service->setContext($ctx);
        $service->breadcrumb('http', 'Incoming request', [
            'other' => 'value',
            'payload' => str_repeat('a', 900),
        ]);
        $payload = $service->finalize(120, 200, 2048);
        $breadcrumb = $this->findItem($payload['items'], 'breadcrumb.http', 'name');
        $this->assertTrue((bool) ($breadcrumb['data']['_truncated'] ?? false));
        $this->assertSame('value', $breadcrumb['data']['other'] ?? null);
    }
    private function config(array $overrides = []): object
    {
        return (object) array_merge([
            'captureLifecycle' => true,
            'maxLifecycleItems' => 220,
            'maxLifecycleItemDataBytes' => 6000,
            'slowQueryMs' => 100,
        ], $overrides);
    }

    private function context(): array
    {
        return [
            'request_id' => 'req-life',
            'started_at' => microtime(true),
            'started_at_dt' => '2026-05-12 12:00:00',
            'method' => 'POST',
            'path' => '/checkout',
            'controller' => 'CheckoutController',
            'action' => 'store',
            'had_error' => false,
            'db_count' => 0,
            'db_time_ms' => 0,
        ];
    }

    private function findItem(array $items, string $needle, string $key = 'id'): array
    {
        foreach ($items as $item) {
            if (($item[$key] ?? '') === $needle) {
                return $item;
            }
        }

        self::fail("Lifecycle item {$needle} was not found.");
    }

    private function findItemOrNull(array $items, string $needle, string $key = 'id'): ?array
    {
        foreach ($items as $item) {
            if (($item[$key] ?? '') === $needle) {
                return $item;
            }
        }

        return null;
    }
}

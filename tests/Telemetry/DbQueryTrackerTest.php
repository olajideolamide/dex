<?php

declare(strict_types=1);

namespace Dex\Tests\Telemetry;

use Dex\Services\Core\QueryTrackingService;
use Dex\Services\Core\RequestLifecycleService;
use Dex\Support\DexRuntimePolicy;
use PHPUnit\Framework\TestCase;

final class DbQueryTrackerTest extends TestCase
{
    public function testTracksQueriesAsLifecycleItems(): void
    {
        $config = (object) [
            'enabled' => true,
            'maxSqlLength' => 100,
            'slowQueryMs' => 400,
            'captureLifecycle' => true,
        ];
        $lifecycleService = new RequestLifecycleService($config);
        $tracker = new QueryTrackingService($config, $lifecycleService, new DexRuntimePolicy($config));

        $ctx = [
            'request_id' => 'req-1',
            'started_at' => microtime(true),
            'db_count' => 0,
            'db_time_ms' => 0.0,
        ];
        $lifecycleService->setContext($ctx);
        $tracker->track(new FakeQuery(0.5, "select * from users where id = 123 and name='Bob'"), $ctx);
        $tracker->track(new FakeQuery(0.3, "select * from users where id = 999 and name='Sue'"), $ctx);
        $payload = $lifecycleService->finalize(900, 200, 4096);
        $this->assertSame(2, $ctx['db_count']);
        $this->assertSame(800.0, $ctx['db_time_ms']);
        $this->assertSame(1, $ctx['slow_query_count']);
        $this->assertSame(2, $payload['summary']['db_query_count']);
        $this->assertSame(1, $payload['summary']['slow_query_count']);
        $queryItems = array_values(array_filter($payload['items'], static fn(array $item): bool => ($item['type'] ?? '') === 'db_query'));
        $this->assertCount(2, $queryItems);
        $this->assertStringNotContainsString('123', (string) ($queryItems[0]['data']['sql'] ?? ''));
        $this->assertStringNotContainsString('Bob', (string) ($queryItems[0]['data']['sql'] ?? ''));
        $this->assertStringNotContainsString('999', (string) ($queryItems[1]['data']['sql'] ?? ''));
        $this->assertStringNotContainsString('Sue', (string) ($queryItems[1]['data']['sql'] ?? ''));
    }
}

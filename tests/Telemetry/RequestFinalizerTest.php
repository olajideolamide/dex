<?php

declare(strict_types=1);

namespace Dex\Tests\Telemetry;

use Dex\DTO\ResponseMeta;
use Dex\Services\Core\RequestFinalizerService;
use Dex\Services\Core\RequestLifecycleService;
use Dex\Tests\Support\Doubles\MemoryStorage;
use PHPUnit\Framework\TestCase;

final class RequestFinalizerTest extends TestCase
{
    public function testRecordsRequestRow(): void
    {
        $storage = new MemoryStorage();
        $config = (object) [
            'captureRequestSnapshots' => false,
            'captureLifecycle' => true,
            'maxLifecycleBytes' => 128000,
        ];

        $lifecycleService = new RequestLifecycleService($config);
        $finalizer = new RequestFinalizerService($config, $storage, $lifecycleService);

        $ctx = [
            'request_id' => 'req-1',
            'started_at' => microtime(true),
            'started_at_dt' => '2026-05-12 12:00:00',
            'method' => 'GET',
            'path' => '/hello',
            'db_count' => 2,
            'db_time_ms' => 12.7,
            'controller' => 'Home',
            'action' => 'index',
            'had_error' => true,
        ];
        $lifecycleService->setContext($ctx);
        $lifecycleService->breadcrumb('test', 'breadcrumb', [], 'info', 'manual');

        $finalizer->record($ctx, 201, 55, 2048, new ResponseMeta(201));

        $this->assertCount(1, $storage->requests);
        $row = $storage->requests[0];

        $this->assertSame('req-1', $row['request_id']);
        $this->assertSame(201, $row['status_code']);
        $this->assertSame(55, $row['duration_ms']);
        $this->assertSame(2, $row['db_count']);
        $this->assertSame(13, $row['db_time_ms']);
        $this->assertSame('Home', $row['controller']);
        $this->assertSame('index', $row['action']);

        $this->assertNotNull($row['lifecycle_json']);
        $this->assertSame(2, $row['lifecycle_version']);
        $this->assertSame(1, $row['has_error']);
        $this->assertSame(1, $row['breadcrumb_count']);
        $this->assertArrayNotHasKey('breadcrumbs_json', $row);
        $this->assertArrayNotHasKey('spans_json', $row);
        $this->assertArrayNotHasKey('snapshot_json', $row);
    }
}

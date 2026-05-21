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

namespace Dex\Tests\Repositories;

use Dex\Domain\Exceptions\RepositoryException;
use Dex\Repositories\RequestRepository;
use Dex\Tests\Support\DexDatabaseTestCase;

/**
 * @group database
 */
final class RequestRepositoryTest extends DexDatabaseTestCase
{
    private RequestRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new RequestRepository();
    }

    private function baseRow(array $overrides = []): array
    {
        return array_merge([
            'request_id'            => 'req-' . uniqid('', true),
            'method'                => 'GET',
            'path'                  => '/test',
            'status_code'           => 200,
            'duration_ms'           => 120,
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
            'created_at'            => date('Y-m-d H:i:s'),
        ], $overrides);
    }

    public function testStoresRequestRow(): void
    {
        $row = $this->baseRow(['path' => '/api/users', 'method' => 'POST']);
        $this->repository->recordRequest($row);

        $this->seeInDatabase('dex_requests', [
            'request_id' => $row['request_id'],
            'path'       => '/api/users',
            'method'     => 'POST',
        ]);
    }

    public function testStoresSnapshotJson(): void
    {
        $snapshot = json_encode(['headers' => ['accept' => 'application/json']]);
        $row = $this->baseRow(['snapshot_json' => $snapshot]);
        $this->repository->recordRequest($row);

        $stored = $this->decodeJsonColumn('dex_requests', 'snapshot_json', ['request_id' => $row['request_id']]);
        $this->assertArrayHasKey('headers', $stored);
    }

    public function testStoresLifecycleJson(): void
    {
        $lifecycle = json_encode(['items' => [], 'summary' => ['event_count' => 0]]);
        $row = $this->baseRow(['lifecycle_json' => $lifecycle]);
        $this->repository->recordRequest($row);

        $stored = $this->decodeJsonColumn('dex_requests', 'lifecycle_json', ['request_id' => $row['request_id']]);
        $this->assertArrayHasKey('items', $stored);
    }

    public function testStoresRequestMetrics(): void
    {
        $row = $this->baseRow([
            'duration_ms' => 350,
            'mem_peak'    => 5000000,
            'db_count'    => 7,
            'db_time_ms'  => 45,
        ]);
        $this->repository->recordRequest($row);

        $this->seeInDatabase('dex_requests', [
            'request_id'  => $row['request_id'],
            'duration_ms' => 350,
            'db_count'    => 7,
            'db_time_ms'  => 45,
        ]);
    }

    public function testStoresControllerAndAction(): void
    {
        $row = $this->baseRow([
            'controller' => 'App\\Controllers\\UserController',
            'action'     => 'show',
        ]);
        $this->repository->recordRequest($row);

        $this->seeInDatabase('dex_requests', [
            'request_id' => $row['request_id'],
            'controller' => 'App\\Controllers\\UserController',
            'action'     => 'show',
        ]);
    }

    public function testStoresHasError(): void
    {
        $row = $this->baseRow(['has_error' => 1]);
        $this->repository->recordRequest($row);

        $this->seeInDatabase('dex_requests', ['request_id' => $row['request_id'], 'has_error' => 1]);
    }

    public function testStoresHasException(): void
    {
        $row = $this->baseRow(['has_exception' => 1]);
        $this->repository->recordRequest($row);

        $this->seeInDatabase('dex_requests', ['request_id' => $row['request_id'], 'has_exception' => 1]);
    }

    public function testStoresSlowRequest(): void
    {
        $row = $this->baseRow(['slow_request' => 1, 'duration_ms' => 2500]);
        $this->repository->recordRequest($row);

        $this->seeInDatabase('dex_requests', ['request_id' => $row['request_id'], 'slow_request' => 1]);
    }

    public function testStoresSlowQueryCount(): void
    {
        $row = $this->baseRow(['slow_query_count' => 3, 'slowest_query_ms' => 450]);
        $this->repository->recordRequest($row);

        $this->seeInDatabase('dex_requests', [
            'request_id'      => $row['request_id'],
            'slow_query_count' => 3,
            'slowest_query_ms' => 450,
        ]);
    }

    public function testStoresLifecycleEventCounters(): void
    {
        $row = $this->baseRow([
            'lifecycle_event_count' => 12,
            'manual_span_count'     => 3,
            'breadcrumb_count'      => 4,
        ]);
        $this->repository->recordRequest($row);

        $this->seeInDatabase('dex_requests', [
            'request_id'            => $row['request_id'],
            'lifecycle_event_count' => 12,
            'manual_span_count'     => 3,
            'breadcrumb_count'      => 4,
        ]);
    }

    public function testRejectsDuplicateRequestId(): void
    {
        $requestId = 'req-dup-' . uniqid('', true);

        $this->repository->recordRequest($this->baseRow(['request_id' => $requestId]));

        $this->expectException(RepositoryException::class);
        $this->repository->recordRequest($this->baseRow(['request_id' => $requestId]));
    }
}

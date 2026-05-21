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

use Dex\Repositories\RequestReadRepository;
use Dex\Tests\Support\DexDatabaseTestCase;

/**
 * @group database
 */
final class RequestReadRepositoryTest extends DexDatabaseTestCase
{
    private RequestReadRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new RequestReadRepository();
    }

    public function testFindByRequestId(): void
    {
        $requestId = 'req-find-' . uniqid('', true);
        $this->insertRequest(['request_id' => $requestId, 'path' => '/api/find']);

        $row = $this->repository->findLatestByRequestId($requestId);

        $this->assertNotNull($row);
        $this->assertSame($requestId, $row['request_id']);
        $this->assertSame('/api/find', $row['path']);
    }

    public function testReturnNullForUnknownRequestId(): void
    {
        $row = $this->repository->findLatestByRequestId('req-does-not-exist-xyz');

        $this->assertNull($row);
    }

    public function testListSimilarRequestsByPathAndMethod(): void
    {
        $since = date('Y-m-d H:i:s', strtotime('-1 hour'));

        $this->insertRequest(['path' => '/api/checkout', 'method' => 'POST', 'created_at' => date('Y-m-d H:i:s')]);
        $this->insertRequest(['path' => '/api/checkout', 'method' => 'POST', 'created_at' => date('Y-m-d H:i:s')]);
        $this->insertRequest(['path' => '/api/profile',  'method' => 'GET',  'created_at' => date('Y-m-d H:i:s')]);

        $rows = $this->repository->listSimilarRequests('/api/checkout', 'POST', $since, 10);

        $this->assertCount(2, $rows);
    }

    public function testDecodeLifecycleJsonSafely(): void
    {
        $lifecycle = ['items' => [], 'summary' => ['event_count' => 5]];
        $requestId = 'req-lifecycle-' . uniqid('', true);
        $this->insertRequest([
            'request_id'   => $requestId,
            'lifecycle_json' => json_encode($lifecycle),
        ]);

        $row = $this->repository->findLatestByRequestId($requestId);
        $this->assertNotNull($row);

        $decoded = json_decode((string) $row['lifecycle_json'], true);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('summary', $decoded);
        $this->assertSame(5, $decoded['summary']['event_count']);
    }

    public function testDecodeSnapshotJsonSafely(): void
    {
        $snapshot = ['method' => 'GET', 'path' => '/api/test'];
        $requestId = 'req-snapshot-' . uniqid('', true);
        $this->insertRequest([
            'request_id'    => $requestId,
            'snapshot_json' => json_encode($snapshot),
        ]);

        $row = $this->repository->findLatestByRequestId($requestId);
        $this->assertNotNull($row);

        $decoded = json_decode((string) $row['snapshot_json'], true);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('method', $decoded);
    }

    public function testHandleNullLifecycleJson(): void
    {
        $requestId = 'req-null-lc-' . uniqid('', true);
        $this->insertRequest(['request_id' => $requestId, 'lifecycle_json' => null]);

        $row = $this->repository->findLatestByRequestId($requestId);
        $this->assertNotNull($row);
        $this->assertNull($row['lifecycle_json']);
    }

    public function testFindLatestByRequestIds(): void
    {
        $id1 = 'req-batch-1-' . uniqid('', true);
        $id2 = 'req-batch-2-' . uniqid('', true);

        $this->insertRequest(['request_id' => $id1, 'path' => '/path/one']);
        $this->insertRequest(['request_id' => $id2, 'path' => '/path/two']);

        $map = $this->repository->findLatestByRequestIds([$id1, $id2]);

        $this->assertArrayHasKey($id1, $map);
        $this->assertArrayHasKey($id2, $map);
        $this->assertSame('/path/one', $map[$id1]['path']);
        $this->assertSame('/path/two', $map[$id2]['path']);
    }
}

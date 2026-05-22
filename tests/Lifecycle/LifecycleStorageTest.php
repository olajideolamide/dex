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

namespace Dex\Tests\Lifecycle;

use Dex\Repositories\RequestRepository;
use Dex\Tests\Support\DexDatabaseTestCase;
use Dex\Tests\Support\Factories\LifecycleFactory;
use Dex\Tests\Support\Factories\RequestFactory;

/**
 * @group database
 * @group lifecycle
 */
final class LifecycleStorageTest extends DexDatabaseTestCase
{
    private RequestRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new RequestRepository();
    }

    public function testStoresCompleteLifecycleJson(): void
    {
        $lifecycle = LifecycleFactory::ecommerceCheckoutFailure();
        $row = RequestFactory::withLifecycle($lifecycle, ['path' => '/checkout']);
        $this->repository->recordRequest($row);

        $stored = $this->decodeJsonColumn('dex_requests', 'lifecycle_json', ['request_id' => $row['request_id']]);

        $this->assertArrayHasKey('items', $stored);
        $this->assertArrayHasKey('summary', $stored);
        $this->assertNotEmpty($stored['items']);
    }

    public function testLifecycleItemsContainExpectedEventTypes(): void
    {
        $lifecycle = LifecycleFactory::ecommerceCheckoutFailure();
        $row = RequestFactory::withLifecycle($lifecycle);
        $this->repository->recordRequest($row);

        $stored = $this->decodeJsonColumn('dex_requests', 'lifecycle_json', ['request_id' => $row['request_id']]);
        $types = array_column($stored['items'], 'type');

        $this->assertContains('checkpoint', $types, 'lifecycle_json should contain checkpoints');
        $this->assertContains('span', $types, 'lifecycle_json should contain spans');
        $this->assertContains('breadcrumb', $types, 'lifecycle_json should contain breadcrumbs');
        $this->assertContains('db_query', $types, 'lifecycle_json should contain db_queries');
        $this->assertContains('exception', $types, 'lifecycle_json should contain exceptions');
    }

    public function testLifecycleItemsHaveRequiredFields(): void
    {
        $lifecycle = LifecycleFactory::ecommerceCheckoutFailure();
        $row = RequestFactory::withLifecycle($lifecycle);
        $this->repository->recordRequest($row);

        $stored = $this->decodeJsonColumn('dex_requests', 'lifecycle_json', ['request_id' => $row['request_id']]);

        foreach ($stored['items'] as $item) {
            $this->assertArrayHasKey('type', $item, 'Each lifecycle item must have a type');
            $this->assertArrayHasKey('label', $item, 'Each lifecycle item must have a label');
            $this->assertArrayHasKey('start_ms', $item, 'Each lifecycle item must have start_ms');
        }
    }

    public function testSpanItemsHaveDurationWherePossible(): void
    {
        $lifecycle = LifecycleFactory::ecommerceCheckoutFailure();
        $row = RequestFactory::withLifecycle($lifecycle);
        $this->repository->recordRequest($row);

        $stored = $this->decodeJsonColumn('dex_requests', 'lifecycle_json', ['request_id' => $row['request_id']]);
        $spans = array_filter($stored['items'], static fn(array $i): bool => $i['type'] === 'span');

        foreach ($spans as $span) {
            $this->assertNotNull($span['duration_ms'] ?? null, 'Span should have duration_ms');
            $this->assertGreaterThanOrEqual(0, $span['duration_ms']);
        }
    }

    public function testDbQueryItemsContainQueryInfo(): void
    {
        $lifecycle = LifecycleFactory::ecommerceCheckoutFailure();
        $row = RequestFactory::withLifecycle($lifecycle);
        $this->repository->recordRequest($row);

        $stored = $this->decodeJsonColumn('dex_requests', 'lifecycle_json', ['request_id' => $row['request_id']]);
        $queries = array_filter($stored['items'], static fn(array $i): bool => $i['type'] === 'db_query');

        $this->assertNotEmpty($queries, 'There should be at least one db_query item');
        foreach ($queries as $q) {
            $this->assertArrayHasKey('data', $q, 'DB query item must have data');
        }
    }

    public function testExceptionItemsContainExceptionInfo(): void
    {
        $lifecycle = LifecycleFactory::ecommerceCheckoutFailure();
        $row = RequestFactory::withLifecycle($lifecycle);
        $this->repository->recordRequest($row);

        $stored = $this->decodeJsonColumn('dex_requests', 'lifecycle_json', ['request_id' => $row['request_id']]);
        $exceptions = array_filter($stored['items'], static fn(array $i): bool => $i['type'] === 'exception');

        $this->assertNotEmpty($exceptions);
        foreach ($exceptions as $exc) {
            $this->assertArrayHasKey('data', $exc);
            $this->assertNotEmpty($exc['data']);
        }
    }
}

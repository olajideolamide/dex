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
final class LifecycleMetricsTest extends DexDatabaseTestCase
{
    private RequestRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new RequestRepository();
    }

    /**
     * The checkout failure lifecycle has:
     * - 3 manual spans
     * - 4 breadcrumbs
     * - 5 DB queries
     * - 2 slow DB queries
     * - 1 exception
     */
    public function testStoresLifecycleCounters(): void
    {
        $lifecycle = LifecycleFactory::ecommerceCheckoutFailure();
        $row = RequestFactory::withLifecycle($lifecycle, [
            'has_error'     => 1,
            'has_exception' => 1,
        ]);

        $this->repository->recordRequest($row);

        $this->seeInDatabase('dex_requests', [
            'request_id'            => $row['request_id'],
            'manual_span_count'     => 3,
            'breadcrumb_count'      => 4,
            'slow_query_count'      => 2,
            'slowest_query_ms'      => 200,
            'has_exception'         => 1,
            'has_error'             => 1,
        ]);
    }

    public function testStoresDbCount(): void
    {
        $lifecycle = LifecycleFactory::ecommerceCheckoutFailure();
        $row = RequestFactory::withLifecycle($lifecycle, ['db_count' => 5, 'db_time_ms' => 375]);

        $this->repository->recordRequest($row);

        $this->seeInDatabase('dex_requests', [
            'request_id' => $row['request_id'],
            'db_count'   => 5,
            'db_time_ms' => 375,
        ]);
    }

    public function testStoresLifecycleEventCount(): void
    {
        $lifecycle = LifecycleFactory::ecommerceCheckoutFailure();
        $row = RequestFactory::withLifecycle($lifecycle);

        $this->repository->recordRequest($row);

        $this->assertGreaterThan(0, $row['lifecycle_event_count']);
        $this->seeInDatabase('dex_requests', [
            'request_id'            => $row['request_id'],
            'lifecycle_event_count' => $row['lifecycle_event_count'],
        ]);
    }

    public function testSlowDatabaseRequestMetrics(): void
    {
        $lifecycle = LifecycleFactory::slowDatabaseRequest();
        $row = RequestFactory::withLifecycle($lifecycle, [
            'slow_request' => 1,
            'duration_ms'  => 2010,
            'db_count'     => 2,
            'db_time_ms'   => 2000,
        ]);

        $this->repository->recordRequest($row);

        $this->seeInDatabase('dex_requests', [
            'request_id'       => $row['request_id'],
            'slow_query_count' => 2,
            'slowest_query_ms' => 1200,
            'slow_request'     => 1,
        ]);
    }

    public function testApiValidationFailureMetrics(): void
    {
        $lifecycle = LifecycleFactory::apiValidationFailure();
        $row = RequestFactory::withLifecycle($lifecycle, [
            'has_exception' => 1,
            'has_error'     => 1,
            'status_code'   => 422,
        ]);

        $this->repository->recordRequest($row);

        $this->seeInDatabase('dex_requests', [
            'request_id'        => $row['request_id'],
            'has_exception'     => 1,
            'slow_query_count'  => 0,
            'manual_span_count' => 0,
            'breadcrumb_count'  => 1,
        ]);
    }
}

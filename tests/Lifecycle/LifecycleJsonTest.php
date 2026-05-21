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
use Dex\Support\Scrubber;
use Dex\Tests\Support\DexDatabaseTestCase;
use Dex\Tests\Support\Factories\LifecycleFactory;
use Dex\Tests\Support\Factories\RequestFactory;

/**
 * @group database
 * @group lifecycle
 */
final class LifecycleJsonTest extends DexDatabaseTestCase
{
    private RequestRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new RequestRepository();
    }

    public function testEmptyLifecycleBecomesValidPayload(): void
    {
        $lifecycle = LifecycleFactory::empty();
        $row = RequestFactory::withLifecycle($lifecycle);

        $this->repository->recordRequest($row);

        $stored = $this->decodeJsonColumn('dex_requests', 'lifecycle_json', ['request_id' => $row['request_id']]);
        $this->assertArrayHasKey('items', $stored);
        $this->assertEmpty($stored['items']);
    }

    public function testLargeLifecycleCanBeStored(): void
    {
        $lifecycle = LifecycleFactory::ecommerceCheckoutFailure();

        // Inflate the lifecycle with many extra items
        for ($i = 0; $i < 50; $i++) {
            $lifecycle['items'][] = [
                'id'          => 'extra-' . $i,
                'type'        => 'breadcrumb',
                'name'        => 'breadcrumb.extra',
                'label'       => 'Extra breadcrumb ' . $i,
                'source'      => 'manual',
                'origin'      => 'manual',
                'start_ms'    => 100.0 + $i,
                'end_ms'      => null,
                'duration_ms' => null,
                'parent_id'   => null,
                'depth'       => 0,
                'status'      => 'ok',
                'data'        => ['index' => $i],
            ];
        }

        $row = RequestFactory::withLifecycle($lifecycle);

        // Should not throw
        $this->repository->recordRequest($row);

        $this->seeInDatabase('dex_requests', ['request_id' => $row['request_id']]);
    }

    public function testScrubsSensitiveLifecycleData(): void
    {
        $sensitiveFields = ['password', 'token', 'authorization', 'api_key', 'secret'];
        $scrubKeys = $sensitiveFields;

        $lifecycle = LifecycleFactory::empty();
        $lifecycle['items'][] = [
            'id'          => 'brd-sensitive',
            'type'        => 'breadcrumb',
            'name'        => 'breadcrumb.auth',
            'label'       => 'Auth attempt',
            'source'      => 'manual',
            'origin'      => 'manual',
            'start_ms'    => 1.0,
            'end_ms'      => null,
            'duration_ms' => null,
            'parent_id'   => null,
            'depth'       => 0,
            'status'      => 'ok',
            'data'        => [
                'user'          => 'john',
                'password'      => 'super-secret-123',
                'token'         => 'bearer-token-xyz',
                'authorization' => 'Bearer abc123',
            ],
        ];

        $scrubbed = Scrubber::scrub($lifecycle, $scrubKeys);

        // Find the sensitive breadcrumb item
        $item = $scrubbed['items'][0];
        $this->assertSame('[REDACTED]', $item['data']['password'], 'password should be redacted');
        $this->assertSame('[REDACTED]', $item['data']['token'], 'token should be redacted');
        $this->assertSame('[REDACTED]', $item['data']['authorization'], 'authorization should be redacted');
        $this->assertSame('john', $item['data']['user'], 'non-sensitive fields should pass through');
    }

    public function testHeadersCookiesAndPasswordsAreScrubbed(): void
    {
        $scrubKeys = ['password', 'token', 'cookie', 'set-cookie', 'authorization', 'api_key'];

        $lifecycle = [
            'version'    => 2,
            'started_at' => time(),
            'items'      => [],
            'snapshot'   => [
                'headers' => [
                    'cookie'        => 'session=abc123; user=john',
                    'authorization' => 'Bearer secret-token',
                    'accept'        => 'application/json',
                ],
                'post' => [
                    'username' => 'admin',
                    'password' => 'hunter2',
                    'api_key'  => 'key-12345',
                ],
            ],
        ];

        $scrubbed = Scrubber::scrub($lifecycle, $scrubKeys);

        $this->assertSame('[REDACTED]', $scrubbed['snapshot']['headers']['cookie']);
        $this->assertSame('[REDACTED]', $scrubbed['snapshot']['headers']['authorization']);
        $this->assertSame('application/json', $scrubbed['snapshot']['headers']['accept']);
        $this->assertSame('[REDACTED]', $scrubbed['snapshot']['post']['password']);
        $this->assertSame('[REDACTED]', $scrubbed['snapshot']['post']['api_key']);
        $this->assertSame('admin', $scrubbed['snapshot']['post']['username']);
    }

    public function testInvalidLifecycleDataDoesNotCrashStorage(): void
    {
        // Pass a malformed lifecycle_json (just a string, not an object)
        $row = RequestFactory::normal([
            'lifecycle_json' => '{}',
        ]);

        // Should not throw
        $this->repository->recordRequest($row);

        $this->seeInDatabase('dex_requests', ['request_id' => $row['request_id']]);
    }

    public function testNullLifecycleIsStoredWithoutError(): void
    {
        $row = RequestFactory::normal(['lifecycle_json' => null]);

        $this->repository->recordRequest($row);

        $this->seeInDatabase('dex_requests', ['request_id' => $row['request_id']]);
    }
}

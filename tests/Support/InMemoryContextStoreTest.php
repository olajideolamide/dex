<?php

declare(strict_types=1);

namespace Dex\Tests\Support;

use Dex\Domain\Exceptions\ContextNotInitializedException;
use Dex\Support\InMemoryContextStore;
use PHPUnit\Framework\TestCase;

final class InMemoryContextStoreTest extends TestCase
{
    public function testSetGetAndClear(): void
    {
        $store = new InMemoryContextStore();
        $ctx = ['request_id' => 'abc'];

        $store->set($ctx);
        $this->assertSame($ctx, $store->get());

        $ctx['request_id'] = 'def';
        $this->assertSame('def', $store->get()['request_id']);

        $store->clear();
        $this->assertNull($store->get());
    }

    public function testRequireThrowsWhenNotInitialized(): void
    {
        $store = new InMemoryContextStore();

        $this->expectException(ContextNotInitializedException::class);
        $store->require();
    }
}

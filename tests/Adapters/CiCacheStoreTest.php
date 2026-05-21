<?php

declare(strict_types=1);

namespace Dex\Tests\Adapters;

use Config\Services;
use Dex\Adapters\CiCacheStore;
use Dex\Tests\Support\DexTestCase;

final class CiCacheStoreTest extends DexTestCase
{
    protected function tearDown(): void
    {
        Services::resetSingle('cache');

        parent::tearDown();
    }

    public function testGetAndSaveProxyToCacheService(): void
    {
        $cache = new CacheFake();
        Services::injectMock('cache', $cache);

        $store = new CiCacheStore();
        $store->save('key', 'value', 60);

        $this->assertSame('value', $store->get('key'));
        $this->assertSame(['key' => ['value', 60]], $cache->saved);
    }
}

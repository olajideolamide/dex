<?php

declare(strict_types=1);

namespace Dex\Tests\Adapters;

use Config\Services;
use Dex\Adapters\CiCacheStore;
use PHPUnit\Framework\TestCase;

final class CiCacheStoreTest extends TestCase
{
    protected function tearDown(): void
    {
        Services::resetSingle('cache');
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

final class CacheFake
{
    public array $saved = [];

    public function get(string $key): mixed
    {
        return $this->saved[$key][0] ?? null;
    }

    public function save(string $key, mixed $value, int $ttl): void
    {
        $this->saved[$key] = [$value, $ttl];
    }
}

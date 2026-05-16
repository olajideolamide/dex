<?php

declare(strict_types=1);

namespace Dex\Tests\Support;

use Dex\Support\CachedConfigProvider;
use Dex\Support\ConfigResolver;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class CachedConfigProviderTest extends TestCase
{
    protected function setUp(): void
    {
        $this->resetConfigResolverCache();
    }

    protected function tearDown(): void
    {
        $this->resetConfigResolverCache();
    }

    public function testGetCachesResolvedConfig(): void
    {
        $provider = new CachedConfigProvider();

        $first = $provider->get();
        $second = $provider->get();

        $this->assertSame($first, $second);
    }

    private function resetConfigResolverCache(): void
    {
        $ref = new ReflectionClass(ConfigResolver::class);
        $prop = $ref->getProperty('cached');
        $prop->setValue(null);
    }
}

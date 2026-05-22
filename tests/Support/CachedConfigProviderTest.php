<?php

declare(strict_types=1);

namespace Dex\Tests\Support;

use Dex\Support\CachedConfigProvider;
use Dex\Tests\Support\DexTestCase;

final class CachedConfigProviderTest extends DexTestCase
{
    public function testGetCachesResolvedConfig(): void
    {
        $provider = new CachedConfigProvider();

        $first = $provider->get();
        $second = $provider->get();

        $this->assertSame($first, $second);
    }
}

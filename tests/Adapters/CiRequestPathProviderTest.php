<?php

declare(strict_types=1);

namespace Dex\Tests\Adapters;

use Config\Services;
use Dex\Adapters\CiRequestPathProvider;
use Dex\Tests\Support\DexTestCase;

final class CiRequestPathProviderTest extends DexTestCase
{
    protected function tearDown(): void
    {
        Services::resetSingle('request');

        parent::tearDown();
    }

    public function testCurrentPathReturnsRequestUriPath(): void
    {
        Services::injectMock('request', new FakeRequest('/hello'));

        $provider = new CiRequestPathProvider();

        $this->assertSame('/hello', $provider->currentPath());
    }
}

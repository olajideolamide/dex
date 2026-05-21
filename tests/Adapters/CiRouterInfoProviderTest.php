<?php

declare(strict_types=1);

namespace Dex\Tests\Adapters;

use Config\Services;
use Dex\Adapters\CiRouterInfoProvider;
use Dex\Tests\Support\DexTestCase;

final class CiRouterInfoProviderTest extends DexTestCase
{
    protected function tearDown(): void
    {
        Services::resetSingle('router');

        parent::tearDown();
    }

    public function testReturnsControllerInfoFromRouter(): void
    {
        Services::injectMock('router', new RouterFake());

        $provider = new CiRouterInfoProvider();
        $info = $provider->getControllerInfo();

        $this->assertSame('App\\Controllers\\Home', $info->controller);
        $this->assertSame('index', $info->action);
        $this->assertSame('home/(:num)', $info->route);
        $this->assertSame(['42'], $info->params);
    }
}

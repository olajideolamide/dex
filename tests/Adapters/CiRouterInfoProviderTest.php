<?php

declare(strict_types=1);

namespace Dex\Tests\Adapters;

use Config\Services;
use Dex\Adapters\CiRouterInfoProvider;
use PHPUnit\Framework\TestCase;

final class CiRouterInfoProviderTest extends TestCase
{
    protected function tearDown(): void
    {
        Services::resetSingle('router');
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

final class RouterFake
{
    public function controllerName(): string
    {
        return 'App\\Controllers\\Home';
    }

    public function methodName(): string
    {
        return 'index';
    }

    public function getMatchedRoute(): array
    {
        return ['home/(:num)', 'App\\Controllers\\Home::index'];
    }

    public function params(): array
    {
        return ['42'];
    }
}

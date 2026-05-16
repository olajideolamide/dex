<?php

declare(strict_types=1);

namespace Dex\Tests\Adapters;

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

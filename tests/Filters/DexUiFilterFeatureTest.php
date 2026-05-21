<?php

declare(strict_types=1);

namespace Dex\Tests\Filters;

use CodeIgniter\Config\Factories;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;
use Dex\Filters\DexUiFilter;
use Dex\Tests\Support\DexDatabaseTestCase;

final class DexUiFilterFeatureTest extends DexDatabaseTestCase
{
    use FeatureTestTrait;

    private const FILTER_ALIAS = 'dexui';

    protected function setUp(): void
    {
        parent::setUp();

        $this->registerFilterAlias();
        $this->addRoutes();
    }

    public function testProtectedRouteReturnsNotFoundWhenUiIsDisabled(): void
    {
        $this->setDexEnv('DEX_ENABLED', '1');
        $this->setDexEnv('DEX_UI_ENABLED', '0');
        $this->setDexEnv('DEX_UI_STEALTH_DENY', '1');
        $this->setClientIp('127.0.0.1');

        $this->expectException(PageNotFoundException::class);
        $this->get('dex-protected');
    }

    public function testProtectedRouteReturnsForbiddenWhenStealthDenyIsDisabled(): void
    {
        $this->setDexEnv('DEX_ENABLED', '1');
        $this->setDexEnv('DEX_UI_ENABLED', '0');
        $this->setDexEnv('DEX_UI_STEALTH_DENY', '0');
        $this->setClientIp('127.0.0.1');

        $result = $this->get('dex-protected');

        $result->assertStatus(403);
    }

    public function testProtectedRouteAllowsAllowedClientIp(): void
    {
        $this->setDexEnv('DEX_ENABLED', '1');
        $this->setDexEnv('DEX_UI_ENABLED', '1');
        $this->setDexEnv('DEX_ALLOWED_IPS', '127.0.0.1');
        $this->setDexEnv('DEX_UI_ALLOWLIST', '127.0.0.0/24');
        $this->setClientIp('127.0.0.1');

        $result = $this->get('dex-protected');

        $result->assertStatus(200);
        $result->assertSee('Protected');
    }

    private function registerFilterAlias(): void
    {
        $filterConfig = config('Filters');
        $filterConfig->aliases[self::FILTER_ALIAS] = DexUiFilter::class;

        Factories::injectMock('config', 'Filters', $filterConfig);
        Factories::injectMock('filters', 'filters', $filterConfig);
    }

    private function addRoutes(): void
    {
        $routes = service('routes');
        $routes->group('/', ['filter' => self::FILTER_ALIAS], static function ($routes): void {
            $routes->get('dex-protected', static function (): void {
                echo 'Protected';
            });
        });

        Services::injectMock('routes', $routes);
    }

    private function setClientIp(string $ipAddress): void
    {
        service('superglobals')->setServer('REMOTE_ADDR', $ipAddress);
    }
}

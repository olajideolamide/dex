<?php

/**
 * This file is part of Dex.
 *
 * (c) Olajide Olanrewaju <jidemac4@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Dex\Tests\Feature;

use CodeIgniter\Config\Factories;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;
use Dex\Filters\DexUiFilter;
use Dex\Tests\Support\DexDatabaseTestCase;

/**
 * @group database
 * @group feature
 */
final class DashboardAccessTest extends DexDatabaseTestCase
{
    use FeatureTestTrait;

    private const FILTER_ALIAS = 'dexui';

    protected function setUp(): void
    {
        parent::setUp();
        $this->registerFilterAlias();
    }

    public function testDashboardLoadsWhenEnabled(): void
    {
        $this->setDexEnv('DEX_ENABLED', '1');
        $this->setDexEnv('DEX_UI_ENABLED', '1');
        $this->setDexEnv('DEX_UI_ALLOWLIST', '127.0.0.1');
        $this->setClientIp('127.0.0.1');

        $routes = service('routes');
        $routes->group('test-dash', ['filter' => self::FILTER_ALIAS], static function ($routes): void {
            $routes->get('', static function (): void {
                echo 'Dashboard';
            });
        });
        Services::injectMock('routes', $routes);

        $result = $this->get('test-dash');
        $result->assertStatus(200);
        $result->assertSee('Dashboard');
    }

    public function testDashboardBlockedWhenUiIsDisabledWithStealthDeny(): void
    {
        $this->setDexEnv('DEX_ENABLED', '1');
        $this->setDexEnv('DEX_UI_ENABLED', '0');
        $this->setDexEnv('DEX_UI_STEALTH_DENY', '1');
        $this->setClientIp('127.0.0.1');

        $routes = service('routes');
        $routes->group('test-dash-blocked', ['filter' => self::FILTER_ALIAS], static function ($routes): void {
            $routes->get('', static function (): void {
                echo 'Should not reach here';
            });
        });
        Services::injectMock('routes', $routes);

        $this->expectException(PageNotFoundException::class);
        $this->get('test-dash-blocked');
    }

    public function testDashboardReturnsForbiddenWhenUiDisabledAndStealthOff(): void
    {
        $this->setDexEnv('DEX_ENABLED', '1');
        $this->setDexEnv('DEX_UI_ENABLED', '0');
        $this->setDexEnv('DEX_UI_STEALTH_DENY', '0');
        $this->setClientIp('127.0.0.1');

        $routes = service('routes');
        $routes->group('test-dash-403', ['filter' => self::FILTER_ALIAS], static function ($routes): void {
            $routes->get('', static function (): void {
                echo 'Should not reach here';
            });
        });
        Services::injectMock('routes', $routes);

        $result = $this->get('test-dash-403');
        $result->assertStatus(403);
    }

    public function testAllowedIpCanAccess(): void
    {
        $this->setDexEnv('DEX_ENABLED', '1');
        $this->setDexEnv('DEX_UI_ENABLED', '1');
        $this->setDexEnv('DEX_UI_ALLOWLIST', '10.0.0.5');
        $this->setClientIp('10.0.0.5');

        $routes = service('routes');
        $routes->group('test-dash-ip-allowed', ['filter' => self::FILTER_ALIAS], static function ($routes): void {
            $routes->get('', static function (): void {
                echo 'Allowed';
            });
        });
        Services::injectMock('routes', $routes);

        $result = $this->get('test-dash-ip-allowed');
        $result->assertStatus(200);
    }

    public function testBlockedIpCannotAccess(): void
    {
        $this->setDexEnv('DEX_ENABLED', '1');
        $this->setDexEnv('DEX_UI_ENABLED', '1');
        $this->setDexEnv('DEX_UI_ALLOWLIST', '10.0.0.5');
        $this->setDexEnv('DEX_UI_STEALTH_DENY', '0');
        $this->setClientIp('192.168.1.99');

        $routes = service('routes');
        $routes->group('test-dash-ip-blocked', ['filter' => self::FILTER_ALIAS], static function ($routes): void {
            $routes->get('', static function (): void {
                echo 'Should not reach here';
            });
        });
        Services::injectMock('routes', $routes);

        $result = $this->get('test-dash-ip-blocked');
        $result->assertStatus(403);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function registerFilterAlias(): void
    {
        $filterConfig = config('Filters');
        $filterConfig->aliases[self::FILTER_ALIAS] = DexUiFilter::class;

        Factories::injectMock('config', 'Filters', $filterConfig);
        Factories::injectMock('filters', 'filters', $filterConfig);
    }

    private function setClientIp(string $ip): void
    {
        service('superglobals')->setServer('REMOTE_ADDR', $ip);
    }
}

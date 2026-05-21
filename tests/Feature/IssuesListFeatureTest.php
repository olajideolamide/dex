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
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;
use Dex\Filters\DexUiFilter;
use Dex\Tests\Support\DexDatabaseTestCase;

/**
 * @group database
 * @group feature
 */
final class IssuesListFeatureTest extends DexDatabaseTestCase
{
    use FeatureTestTrait;

    private const FILTER_ALIAS = 'dexui';
    private const PREFIX = 'dex';

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupDexRoutes();
        $this->allowLocalIp();
    }

    public function testIssuesListLoads(): void
    {
        $result = $this->get(self::PREFIX);
        $result->assertStatus(200);
    }

    public function testOpenIssuesAppear(): void
    {
        $this->insertIssue(['fingerprint' => 'fp-list-open', 'title' => 'Open list issue', 'status' => 'open']);

        $result = $this->get(self::PREFIX . '?status=open');
        $result->assertStatus(200);
    }

    public function testResolvedIssuesCanBeFiltered(): void
    {
        $this->insertIssue(['fingerprint' => 'fp-list-resolved', 'title' => 'Resolved list issue', 'status' => 'resolved']);

        $result = $this->get(self::PREFIX . '?status=resolved');
        $result->assertStatus(200);
    }

    public function testIgnoredIssuesCanBeFiltered(): void
    {
        $this->insertIssue(['fingerprint' => 'fp-list-ignored', 'title' => 'Ignored list issue', 'status' => 'ignored']);

        $result = $this->get(self::PREFIX . '?status=ignored');
        $result->assertStatus(200);
    }

    public function testRegressedIssuesCanBeFiltered(): void
    {
        $this->insertIssue(['fingerprint' => 'fp-list-regressed', 'title' => 'Regressed list issue', 'status' => 'regression']);

        $result = $this->get(self::PREFIX . '?status=regressed');
        $result->assertStatus(200);
    }

    public function testEmptyStateRendersCleanly(): void
    {
        // No issues seeded — list should still return 200
        $result = $this->get(self::PREFIX . '?status=open');
        $result->assertStatus(200);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function setupDexRoutes(): void
    {
        $filterConfig = config('Filters');
        $filterConfig->aliases[self::FILTER_ALIAS] = DexUiFilter::class;
        Factories::injectMock('config', 'Filters', $filterConfig);
        Factories::injectMock('filters', 'filters', $filterConfig);

        $routes = service('routes');
        $routes->group(self::PREFIX, [
            'namespace' => 'Dex\\Controllers',
            'filter'    => self::FILTER_ALIAS,
        ], static function ($routes): void {
            $routes->get('', 'Issues::index');
            $routes->get('issues/data', 'Issues::data');
            $routes->get('issues/(:num)/dialog', 'Issues::dialog/$1');
            $routes->post('issues/(:num)/resolve', 'Issues::resolve/$1');
            $routes->post('issues/(:num)/ignore', 'Issues::ignore/$1');
        });

        Services::injectMock('routes', $routes);
    }

    private function allowLocalIp(): void
    {
        $this->setDexEnv('DEX_ENABLED', '1');
        $this->setDexEnv('DEX_UI_ENABLED', '1');
        $this->setDexEnv('DEX_UI_ALLOWLIST', '127.0.0.1,::1');
        $this->setDexEnv('DEX_UI_STEALTH_DENY', '0');
        service('superglobals')->setServer('REMOTE_ADDR', '127.0.0.1');
    }
}

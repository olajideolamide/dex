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
final class IssueStatusActionFeatureTest extends DexDatabaseTestCase
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

    public function testIssueCanBeResolved(): void
    {
        $issueId = $this->insertIssue(['status' => 'open']);

        $result = $this->post(self::PREFIX . '/issues/' . $issueId . '/resolve');

        $result->assertStatus(200);

        $body = json_decode((string) $result->getJSON(), true);
        $this->assertTrue($body['success'] ?? false, 'Response should indicate success');

        $this->seeInDatabase('dex_issues', ['id' => $issueId, 'status' => 'resolved']);
    }

    public function testIssueCanBeIgnored(): void
    {
        $issueId = $this->insertIssue(['status' => 'open']);

        $result = $this->post(self::PREFIX . '/issues/' . $issueId . '/ignore');

        $result->assertStatus(200);

        $body = json_decode((string) $result->getJSON(), true);
        $this->assertTrue($body['success'] ?? false, 'Response should indicate success');

        $this->seeInDatabase('dex_issues', ['id' => $issueId, 'status' => 'ignored']);
    }

    public function testResolveNonExistentIssueReturns404(): void
    {
        $result = $this->post(self::PREFIX . '/issues/99999/resolve');
        $result->assertStatus(404);
    }

    public function testIgnoreNonExistentIssueReturns404(): void
    {
        $result = $this->post(self::PREFIX . '/issues/99999/ignore');
        $result->assertStatus(404);
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

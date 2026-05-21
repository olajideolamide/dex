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
use Dex\Tests\Support\Factories\LifecycleFactory;

/**
 * @group database
 * @group feature
 */
final class IssueShowFeatureTest extends DexDatabaseTestCase
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

    public function testIssueDetailLoadsWithLifecycle(): void
    {
        $lifecycle  = LifecycleFactory::ecommerceCheckoutFailure();
        $requestId  = 'req-show-' . uniqid('', true);

        $issueId = $this->insertIssue([
            'fingerprint' => 'fp-show-lifecycle',
            'title'       => 'Show issue with lifecycle',
            'class'       => 'RuntimeException',
        ]);

        $this->insertRequest([
            'request_id'     => $requestId,
            'path'           => '/api/show',
            'lifecycle_json' => json_encode($lifecycle),
            'has_exception'  => 1,
        ]);

        $this->insertOccurrence([
            'issue_id'   => $issueId,
            'request_id' => $requestId,
            'message'    => 'Show test exception',
        ]);

        // The dialog endpoint loads the issue detail
        $result = $this->get(self::PREFIX . '/issues/' . $issueId . '/dialog');
        $result->assertStatus(200);
    }

    public function testMissingIssueReturns404(): void
    {
        $result = $this->get(self::PREFIX . '/issues/99999/dialog');
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

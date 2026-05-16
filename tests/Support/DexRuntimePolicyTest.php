<?php

declare(strict_types=1);

namespace Dex\Tests\Support;

use Dex\DTO\RequestMeta;
use Dex\Support\DexRuntimePolicy;
use PHPUnit\Framework\TestCase;

final class DexRuntimePolicyTest extends TestCase
{
    public function testDisabledConfigShortCircuitsRunChecks(): void
    {
        $policy = new DexRuntimePolicy((object) [
            'enabled' => false,
            'routePrefix' => 'dex',
            'ignoreSelfRoutes' => true,
            'botUserAgentBlocklist' => ['bot'],
        ]);

        $request = new RequestMeta('GET', '/orders', '127.0.0.1', 'Mozilla/5.0', null);

        $this->assertFalse($policy->shouldRunRequest($request));
        $this->assertFalse($policy->shouldRunContext(['path' => '/orders', 'user_agent' => 'Mozilla/5.0']));
        $this->assertFalse($policy->shouldRunPath('/orders'));
        $this->assertFalse($policy->shouldRunController('App\\Controllers\\Orders'));
    }

    public function testBlocksBotUserAgentsCaseInsensitively(): void
    {
        $policy = new DexRuntimePolicy((object) [
            'enabled' => true,
            'routePrefix' => 'dex',
            'ignoreSelfRoutes' => true,
            'botUserAgentBlocklist' => ['googlebot', 'bingbot'],
        ]);

        $request = new RequestMeta('GET', '/products', null, 'Mozilla/5.0 (compatible; GoogleBot/2.1)', null);

        $this->assertFalse($policy->shouldRunRequest($request));
        $this->assertFalse($policy->shouldRunContext([
            'path' => '/products',
            'user_agent' => 'BingBot/3.0',
        ]));
    }

    public function testBlocksInternalAndIgnoredPaths(): void
    {
        $policy = new DexRuntimePolicy((object) [
            'enabled' => true,
            'routePrefix' => 'dex',
            'ignoreSelfRoutes' => true,
            'ignorePathPrefixes' => ['/health', '/status'],
            'botUserAgentBlocklist' => [],
        ]);

        $this->assertFalse($policy->shouldRunPath('/dex/issues'));
        $this->assertFalse($policy->shouldRunPath('/health/live'));
        $this->assertFalse($policy->shouldRunContext(['path' => '/status/check']));
        $this->assertTrue($policy->shouldRunPath('/orders/123'));
    }

    public function testBlocksDexControllersOnly(): void
    {
        $policy = new DexRuntimePolicy((object) ['enabled' => true]);

        $this->assertFalse($policy->shouldRunController('Dex\\Controllers\\Issues'));
        $this->assertTrue($policy->shouldRunController('App\\Controllers\\Home'));
    }
}

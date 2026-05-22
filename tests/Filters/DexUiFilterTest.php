<?php

declare(strict_types=1);

namespace Dex\Tests\Filters;

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Dex\Adapters\CiResponseFactory;
use Dex\Filters\DexUiFilter;
use Dex\Tests\Support\DexTestCase;

final class DexUiFilterTest extends DexTestCase
{
    public function testDenyThrowsNotFoundWhenDexIsDisabled(): void
    {
        $this->setDexEnv('DEX_ENABLED', '0');

        $filter = new DexUiFilter($this->responseFactoryReturningStatusCode(403));

        $this->expectException(PageNotFoundException::class);
        $filter->before($this->requestWithIp('127.0.0.1'));
    }

    public function testDenyReturnsForbiddenWhenStealthDenyIsDisabled(): void
    {
        $this->setDexEnv('DEX_ENABLED', '1');
        $this->setDexEnv('DEX_UI_ENABLED', '0');
        $this->setDexEnv('DEX_UI_STEALTH_DENY', '0');

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())
            ->method('setStatusCode')
            ->with(403, 'Forbidden')
            ->willReturnSelf();

        $filter = new DexUiFilter(new TestResponseFactory($response));

        $result = $filter->before($this->requestWithIp('127.0.0.1'));

        $this->assertSame($response, $result);
    }

    public function testDenyWhenAllowedIpsDoesNotContainClientIp(): void
    {
        $this->setDexEnv('DEX_ENABLED', '1');
        $this->setDexEnv('DEX_UI_ENABLED', '1');
        $this->setDexEnv('DEX_ALLOWED_IPS', '10.0.0.1,10.0.0.2');
        $this->setDexEnv('DEX_UI_ALLOWLIST', '10.0.0.0/8');

        $filter = new DexUiFilter($this->responseFactoryReturningStatusCode(403));

        $this->expectException(PageNotFoundException::class);
        $filter->before($this->requestWithIp('127.0.0.1'));
    }

    public function testAllowsWhenIpPassesLegacyAndCidrAllowRules(): void
    {
        $this->setDexEnv('DEX_ENABLED', '1');
        $this->setDexEnv('DEX_UI_ENABLED', '1');
        $this->setDexEnv('DEX_ALLOWED_IPS', '127.0.0.1');
        $this->setDexEnv('DEX_UI_ALLOWLIST', '127.0.0.0/24');

        $filter = new DexUiFilter($this->responseFactoryReturningStatusCode(403));

        $this->assertNull($filter->before($this->requestWithIp('127.0.0.1')));
    }

    private function requestWithIp(string $ip): RequestInterface
    {
        $request = $this->createMock(RequestInterface::class);
        $request->method('getIPAddress')->willReturn($ip);

        return $request;
    }

    private function responseFactoryReturningStatusCode(int $statusCode): CiResponseFactory
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->any())
            ->method('setStatusCode')
            ->with($statusCode, 'Forbidden')
            ->willReturnSelf();

        return new TestResponseFactory($response);
    }
}

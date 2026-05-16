<?php

declare(strict_types=1);

namespace Dex\Tests\Filters;

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Dex\Adapters\CiResponseFactory;
use Dex\Filters\DexUiFilter;
use Dex\Support\ConfigResolver;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class DexUiFilterTest extends TestCase
{
    /**
     * @var list<string>
     */
    private array $envKeys = [];

    protected function setUp(): void
    {
        $this->resetConfigResolverCache();
        $this->clearDexEnv();
    }

    protected function tearDown(): void
    {
        $this->clearDexEnv();
        $this->resetConfigResolverCache();
    }

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

    public function testDeniesInProductionWhenAllowInProductionIsDisabled(): void
    {
        if (ENVIRONMENT !== 'production') {
            $this->markTestSkipped('This scenario only runs when ENVIRONMENT is production.');
        }

        $this->setDexEnv('DEX_ENABLED', '1');
        $this->setDexEnv('DEX_UI_ENABLED', '1');
        $this->setDexEnv('DEX_ALLOW_IN_PRODUCTION', '0');

        $filter = new DexUiFilter($this->responseFactoryReturningStatusCode(403));

        $this->expectException(PageNotFoundException::class);
        $filter->before($this->requestWithIp('127.0.0.1'));
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

    private function setDexEnv(string $key, string $value): void
    {
        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
        $this->envKeys[] = $key;
    }

    private function clearDexEnv(): void
    {
        foreach (array_unique($this->envKeys) as $key) {
            putenv($key);
            unset($_ENV[$key], $_SERVER[$key]);
        }

        $this->envKeys = [];
    }

    private function resetConfigResolverCache(): void
    {
        $reflection = new ReflectionClass(ConfigResolver::class);
        $property = $reflection->getProperty('cached');
        $property->setAccessible(true);
        $property->setValue(null);
    }
}

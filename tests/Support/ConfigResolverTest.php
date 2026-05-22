<?php

declare(strict_types=1);

namespace Dex\Tests\Support;

use Dex\Config\Dex as PackageDex;
use Dex\Support\ConfigResolver;
use Dex\Tests\Support\DexTestCase;

final class ConfigResolverTest extends DexTestCase
{
    public function testResolveReturnsCachedConfig(): void
    {
        $config = ConfigResolver::resolve();

        $this->assertInstanceOf(PackageDex::class, $config);
        $this->assertSame($config, ConfigResolver::resolve());
    }

    public function testFallsBackToPackageDefaultsWhenEnvIsMissing(): void
    {
        $config = ConfigResolver::resolve();

        $this->assertSame('dex', $config->routePrefix);
        $this->assertTrue($config->uiEnabled);
    }

    public function testEnvOverridesPackageDefaults(): void
    {
        $this->setEnv('DEX_ROUTE_PREFIX', 'envPrefix');
        $this->setEnv('DEX_UI_ENABLED', '0');

        $config = ConfigResolver::resolve();

        $this->assertSame('envPrefix', $config->routePrefix);
        $this->assertFalse($config->uiEnabled);
    }

    public function testEnvCastingSupportsArrays(): void
    {
        $this->setEnv('DEX_UI_ENABLED', 'false');
        $this->setEnv('DEX_ALLOWED_IPS', '10.0.0.1, 10.0.0.2');
        $this->setEnv('DEX_IGNORE_PATH_PREFIXES', '["/health","/status"]');

        $config = ConfigResolver::resolve();

        $this->assertFalse($config->uiEnabled);
        $this->assertSame(['10.0.0.1', '10.0.0.2'], $config->allowedIPs);
        $this->assertSame(['/health', '/status'], $config->ignorePathPrefixes);
    }

    private function setEnv(string $key, string $value): void
    {
        $this->setDexEnv($key, $value);
    }
}

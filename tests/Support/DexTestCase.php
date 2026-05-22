<?php

declare(strict_types=1);

namespace Dex\Tests\Support;

use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;
use Dex\Config\Services as DexServices;
use Dex\Support\ConfigResolver;

abstract class DexTestCase extends CIUnitTestCase
{
    /**
     * @var list<string>
     */
    private array $envKeys = [];

    protected function setUp(): void
    {
        $this->resetServices();
        DexServices::reset();
        ConfigResolver::resetCache();
        $this->clearDexEnv();

        parent::setUp();
    }

    protected function tearDown(): void
    {
        $this->clearDexEnv();
        ConfigResolver::resetCache();
        DexServices::reset();

        parent::tearDown();
    }

    protected function setDexEnv(string $key, string $value): void
    {
        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
        Services::superglobals()->setServer($key, $value);
        $this->envKeys[] = $key;

        ConfigResolver::resetCache();
    }

    private function clearDexEnv(): void
    {
        foreach (array_unique($this->envKeys) as $key) {
            putenv($key);
            unset($_ENV[$key]);
            unset($_SERVER[$key]);
            //Services::superglobals()->unsetServer($key);
        }

        $this->envKeys = [];
    }
}

<?php

declare(strict_types=1);

namespace Dex\Tests\Support;

use Dex\Support\ConfigResolver;
use PHPUnit\Framework\TestCase;

abstract class DexTestCase extends TestCase
{
    /**
     * @var list<string>
     */
    private array $envKeys = [];

    protected function setUp(): void
    {
        parent::setUp();

        ConfigResolver::resetCache();
        $this->clearDexEnv();
    }

    protected function tearDown(): void
    {
        $this->clearDexEnv();
        ConfigResolver::resetCache();

        parent::tearDown();
    }

    protected function setDexEnv(string $key, string $value): void
    {
        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
        $this->envKeys[] = $key;
    }

    private function clearDexEnv(): void
    {
        foreach (array_unique($this->envKeys) as $key) {
            putenv($key);
            unset($_ENV[$key]);
        }

        $this->envKeys = [];
    }
}

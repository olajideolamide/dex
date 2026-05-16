<?php

declare(strict_types=1);

namespace Dex\Tests\Adapters;

use Config\Services;
use Dex\Adapters\CiRequestPathProvider;
use PHPUnit\Framework\TestCase;

final class CiRequestPathProviderTest extends TestCase
{
    protected function tearDown(): void
    {
        Services::resetSingle('request');
    }

    public function testCurrentPathReturnsRequestUriPath(): void
    {
        Services::injectMock('request', new FakeRequest('/hello'));

        $provider = new CiRequestPathProvider();

        $this->assertSame('/hello', $provider->currentPath());
    }
}

final class FakeRequest
{
    public function __construct(private string $path)
    {
    }

    public function getUri(): FakeUri
    {
        return new FakeUri($this->path);
    }
}

final class FakeUri
{
    public function __construct(private string $path)
    {
    }

    public function getPath(): string
    {
        return $this->path;
    }
}

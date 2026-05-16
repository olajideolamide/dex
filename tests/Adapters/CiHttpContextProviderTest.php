<?php

declare(strict_types=1);

namespace Dex\Tests\Adapters;

use Config\Services;
use Dex\Adapters\CiHttpContextProvider;
use PHPUnit\Framework\TestCase;

final class CiHttpContextProviderTest extends TestCase
{
    protected function tearDown(): void
    {
        Services::resetSingle('request');
    }

    public function testBuildWithoutHeaders(): void
    {
        Services::injectMock('request', new HttpRequestFake('/path', 'GET', [], ['X-Test' => 'value']));

        $provider = new CiHttpContextProvider();
        $ctx = $provider->build(false, 10, 50);

        $this->assertSame('GET', $ctx['method']);
        $this->assertSame('/path', $ctx['path']);
        $this->assertArrayNotHasKey('headers', $ctx);
    }

    public function testBuildIncludesLimitedHeaders(): void
    {
        Services::injectMock('request', new HttpRequestFake('/path', 'POST', ['a=1'], [
            'X-First' => '1234567890',
            'X-Second' => 'value2',
        ]));

        $provider = new CiHttpContextProvider();
        $ctx = $provider->build(true, 1, 5);

        $this->assertSame('POST', $ctx['method']);
        $this->assertSame('/path', $ctx['path']);
        $this->assertSame('12345...', $ctx['headers']['X-First']);
        $this->assertCount(1, $ctx['headers']);
    }
}

final class HttpRequestFake
{
    public function __construct(
        private string $path,
        private string $method,
        private array $query,
        private array $headers
    ) {
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUri(): HttpUriFake
    {
        return new HttpUriFake($this->path, $this->query);
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getHeaderLine(string $name): string
    {
        foreach ($this->headers as $key => $value) {
            if (strcasecmp($key, $name) === 0) {
                return (string) $value;
            }
        }
        return '';
    }
}

final class HttpUriFake
{
    public function __construct(private string $path, private array $query)
    {
    }

    public function __toString(): string
    {
        $query = $this->query ? ('?' . implode('&', $this->query)) : '';
        return 'https://example.test' . $this->path . $query;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getQuery(): string
    {
        return implode('&', $this->query);
    }
}

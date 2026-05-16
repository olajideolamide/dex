<?php

declare(strict_types=1);

namespace Dex\Tests\Adapters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\URI;
use Dex\Adapters\CiRequestMetaFactory;
use PHPUnit\Framework\TestCase;

final class CiRequestMetaFactoryTest extends TestCase
{
    public function testBuildsRequestMetaFromRequest(): void
    {
        $request = new StubRequest(
            new StubUri('/hello'),
            'POST',
            '127.0.0.1',
            'TestAgent',
            ['X-Request-Id' => ' abc123 ']
        );

        $config = (object) ['requestIdHeader' => 'X-Request-Id'];

        $meta = CiRequestMetaFactory::fromRequest($request, $config);

        $this->assertSame('POST', $meta->method);
        $this->assertSame('/hello', $meta->rawPath);
        $this->assertSame('127.0.0.1', $meta->ip);
        $this->assertSame('TestAgent', $meta->userAgent);
        $this->assertSame('abc123', $meta->incomingRequestId);
    }

    public function testEmptyHeaderYieldsNullIncomingId(): void
    {
        $request = new StubRequest(new StubUri('/'), 'GET', null, null, []);
        $config = (object) ['requestIdHeader' => 'X-Request-Id'];

        $meta = CiRequestMetaFactory::fromRequest($request, $config);

        $this->assertNull($meta->incomingRequestId);
    }
}

final class StubUri
{
    public function __construct(private string $path)
    {
    }

    public function getPath(): string
    {
        return $this->path;
    }
}

final class StubRequest implements RequestInterface
{
    private array $headers;
    private string $method;
    private ?string $ip;
    private ?string $agent;
    private string $protocol = '1.1';
    private string $body = '';

    public function __construct(
        private StubUri $uri,
        string $method,
        ?string $ip,
        ?string $agent,
        array $headers
    ) {
        $this->method = $method;
        $this->ip = $ip;
        $this->agent = $agent;
        $this->headers = $headers;
    }

    public function getUserAgent(): ?string
    {
        return $this->agent;
    }

    public function getProtocolVersion(): string
    {
        return $this->protocol;
    }

    public function setBody($data)
    {
        $this->body = (string) $data;
        return $this;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function appendBody($data)
    {
        $this->body .= (string) $data;
        return $this;
    }

    public function populateHeaders(): void
    {
    }

    public function headers(): array
    {
        return $this->headers;
    }

    public function hasHeader(string $name): bool
    {
        foreach ($this->headers as $key => $_) {
            if (strcasecmp($key, $name) === 0) {
                return true;
            }
        }
        return false;
    }

    public function header($name)
    {
        foreach ($this->headers as $key => $value) {
            if (strcasecmp($key, (string) $name) === 0) {
                return $value;
            }
        }
        return null;
    }

    public function getHeaderLine(string $name): string
    {
        $value = $this->header($name);
        return is_string($value) ? $value : '';
    }

    public function setHeader(string $name, $value)
    {
        $this->headers[$name] = (string) $value;
        return $this;
    }

    public function removeHeader(string $name)
    {
        foreach (array_keys($this->headers) as $key) {
            if (strcasecmp($key, $name) === 0) {
                unset($this->headers[$key]);
            }
        }
        return $this;
    }

    public function appendHeader(string $name, ?string $value)
    {
        return $this->setHeader($name, (string) $value);
    }

    public function prependHeader(string $name, string $value)
    {
        return $this->setHeader($name, $value);
    }

    public function setProtocolVersion(string $version)
    {
        $this->protocol = $version;
        return $this;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function withMethod($method)
    {
        $this->method = (string) $method;
        return $this;
    }

    public function getUri()
    {
        return $this->uri;
    }

    public function withUri(URI $uri, $preserveHost = false)
    {
        return $this;
    }

    public function getIPAddress(): string
    {
        return (string) $this->ip;
    }

    public function getServer($index = null, $filter = null)
    {
        return null;
    }
}

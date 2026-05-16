<?php

declare(strict_types=1);

namespace Dex\Tests\Support;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\URI;
use Dex\DTO\ResponseMeta;
use Dex\Support\RequestSnapshot;
use PHPUnit\Framework\TestCase;

final class RequestSnapshotTest extends TestCase
{
    public function testBuildCapturesRequestAndResponseMetadata(): void
    {
        $req = new StubRequest(
            new FakeUri('https://example.test/path?foo=bar', 'foo=bar', 'example.test', 'https'),
            ['foo' => '1'],
            ['bar' => '2'],
            ['file' => ['name' => 'x.txt']],
            ['X-Request-Id' => 'abc', 'Authorization' => 'secret', 'CF-IPCountry' => 'ng'],
            true
        );

        $config = (object) [
            'snapshotIncludeInputKeys' => true,
            'snapshotMaxKeys' => 5,
            'snapshotIncludeHeaders' => true,
            'snapshotHeaderAllowlist' => ['x-request-id'],
        ];

        $ctx = [
            'request_id' => 'req-123',
            'controller' => 'Home',
            'action' => 'index',
            'route' => 'home',
            'route_params' => ['id' => 5],
            'method' => 'GET',
            'path' => '/path',
            'ip' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            '_duration_ms' => 12.3,
            '_mem_peak' => 2048,
            'db_count' => 2,
            'db_time_ms' => 45.6,
            'breadcrumbs' => ['a', 'b'],
            'spans' => ['s1'],
            '_sample_hit' => true,
            '_slow_hit' => false,
            'had_error' => true,
            '_request' => $req,
        ];

        $response = new ResponseMeta(201, ['Content-Type' => 'application/json']);

        $snapshot = RequestSnapshot::build($ctx, $config, $response);

        $this->assertSame('req-123', $snapshot['request_id']);
        $this->assertSame(PHP_VERSION, $snapshot['ci']['php']);
        $this->assertSame(PHP_SAPI, $snapshot['ci']['sapi']);
        $this->assertSame('Home', $snapshot['routing']['controller']);
        $this->assertSame('GET', $snapshot['request']['method']);
        $this->assertSame('/path', $snapshot['request']['path']);
        $this->assertSame('https://example.test/path?foo=bar', $snapshot['request']['url']);
        $this->assertSame('foo=bar', $snapshot['request']['query']);
        $this->assertSame('example.test', $snapshot['request']['host']);
        $this->assertSame('https', $snapshot['request']['scheme']);
        $this->assertTrue($snapshot['request']['is_ajax']);
        $this->assertSame('NG', $snapshot['user']['country']);
        $this->assertSame('Unknown', $snapshot['user']['browser']);
        $this->assertSame(PHP_VERSION, $snapshot['server']['php']['version']);
        $this->assertSame(PHP_OS_FAMILY, $snapshot['server']['os']['family']);

        $this->assertSame(201, $snapshot['response']['status_code']);
        $this->assertSame('application/json', $snapshot['response']['content_type']);
        $this->assertSame(46, $snapshot['metrics']['db_time_ms']);

        $this->assertSame(['foo'], $snapshot['input']['get']);
        $this->assertSame(['bar'], $snapshot['input']['post']);
        $this->assertSame(['file'], $snapshot['input']['files']);

        $this->assertSame(['X-Request-Id' => 'abc'], $snapshot['headers']);
    }
}

final class StubHeader
{
    public function __construct(private string $value)
    {
    }

    public function getValueLine(): string
    {
        return $this->value;
    }
}

final class FakeUri
{
    public function __construct(
        private string $uri,
        private string $query,
        private string $host,
        private string $scheme
    ) {
    }

    public function __toString(): string
    {
        return $this->uri;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getScheme(): string
    {
        return $this->scheme;
    }
}

final class StubRequest implements RequestInterface
{
    private array $headers = [];
    private array $get = [];
    private array $post = [];
    private array $files = [];
    private bool $ajax = false;
    private string $method = 'GET';
    private string $protocol = '1.1';
    private string $body = '';

    public function __construct(
        private FakeUri $uri,
        array $get = [],
        array $post = [],
        array $files = [],
        array $headers = [],
        bool $ajax = false
    ) {
        $this->get = $get;
        $this->post = $post;
        $this->files = $files;
        $this->ajax = $ajax;

        foreach ($headers as $name => $value) {
            $this->headers[$name] = new StubHeader((string) $value);
        }
    }

    public function getGet()
    {
        return $this->get;
    }

    public function getPost()
    {
        return $this->post;
    }

    public function getFiles(): array
    {
        return $this->files;
    }

    public function isAJAX(): bool
    {
        return $this->ajax;
    }

    public function getHeaders(): array
    {
        return $this->headers;
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
        foreach ($this->headers as $key => $header) {
            if (strcasecmp($key, (string) $name) === 0) {
                return $header;
            }
        }
        return null;
    }

    public function getHeaderLine(string $name): string
    {
        $header = $this->header($name);
        return $header instanceof StubHeader ? $header->getValueLine() : '';
    }

    public function setHeader(string $name, $value)
    {
        $this->headers[$name] = new StubHeader((string) $value);
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
        return $this->setHeader($name, $value ?? '');
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
        return '127.0.0.1';
    }

    public function getServer($index = null, $filter = null)
    {
        return null;
    }
}

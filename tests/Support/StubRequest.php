<?php

declare(strict_types=1);

namespace Dex\Tests\Support;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\Header;
use CodeIgniter\HTTP\URI;

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
            $this->headers[$name] = new Header((string) $name, (string) $value);
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
        return $header instanceof Header ? $header->getValueLine() : '';
    }

    public function setHeader(string $name, $value)
    {
        $this->headers[$name] = new Header($name, (string) $value);
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

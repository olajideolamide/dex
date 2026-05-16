<?php

declare(strict_types=1);

namespace Dex\Tests\Adapters;

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

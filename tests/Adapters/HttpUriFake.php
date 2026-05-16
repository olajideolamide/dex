<?php

declare(strict_types=1);

namespace Dex\Tests\Adapters;

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

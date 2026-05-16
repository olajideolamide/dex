<?php

declare(strict_types=1);

namespace Dex\Tests\Adapters;

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

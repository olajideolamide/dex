<?php

declare(strict_types=1);

namespace Dex\Tests\Adapters;

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

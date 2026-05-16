<?php

declare(strict_types=1);

namespace Dex\Tests\Support;

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

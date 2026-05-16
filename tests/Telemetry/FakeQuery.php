<?php

declare(strict_types=1);

namespace Dex\Tests\Telemetry;

final class FakeQuery
{
    public function __construct(private float $duration, private string $sql)
    {
    }

    public function getDuration(): float
    {
        return $this->duration;
    }

    public function getQuery(): string
    {
        return $this->sql;
    }
}

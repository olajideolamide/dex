<?php

declare(strict_types=1);

namespace Dex\Tests\Adapters;

final class CacheFake
{
    public array $saved = [];

    public function get(string $key): mixed
    {
        return $this->saved[$key][0] ?? null;
    }

    public function save(string $key, mixed $value, int $ttl): void
    {
        $this->saved[$key] = [$value, $ttl];
    }
}

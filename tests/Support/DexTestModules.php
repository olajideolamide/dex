<?php

declare(strict_types=1);

namespace Dex\Tests\Support;

final class DexTestModules
{
    public function shouldDiscover(string $type): bool
    {
        return false;
    }
}

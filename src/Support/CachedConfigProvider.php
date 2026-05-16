<?php

/**
 * This file is part of Dex.
 *
 * (c) Olajide Olanrewaju <jidemac4@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Dex\Support;

use Dex\Config\Dex;

final class CachedConfigProvider
{
    private ?Dex $cached = null;

    public function get(): Dex
    {
        if ($this->cached !== null) {
            return $this->cached;
        }

        return $this->cached = ConfigResolver::resolve();
    }
}

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

namespace Dex\Adapters;

use Throwable;

class CiCacheStore
{
    public function get(string $key): mixed
    {
        try {
            $cache = service('cache');
            return $cache?->get($key);
        } catch (Throwable) {
            return null;
        }
    }

    public function save(string $key, mixed $value, int $ttlSeconds): void
    {
        try {
            $cache = service('cache');
            $cache?->save($key, $value, $ttlSeconds);
        } catch (Throwable) {
            // ignore
        }
    }
}

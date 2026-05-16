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

namespace Dex\Services\Core;

use Dex\Adapters\CiCacheStore;

/**
 * Rate-limits occurrence/issue captures per fingerprint to prevent spam.
 * Uses cache store for per-minute counters.
 */
final class RateLimiterService
{
    public function __construct(
        private readonly object $config,
        private readonly CiCacheStore $cacheStore,
    ) {
    }

    /**
     * Check if a fingerprint is rate-limited (max occurrences per minute).
     * Increments counter and returns true if limit exceeded.
     */
    public function isLimited(string $fingerprint): bool
    {
        $max = (int)($this->config->maxOccurrencesPerMinute ?? 30);
        if ($max <= 0) {
            return false;
        }

        $minute = date('YmdHi');
        $key = 'msrl-' . $minute . '-' . $fingerprint;

        $count = (int)($this->cacheStore->get($key) ?? 0);

        if ($count >= $max) {
            return true;
        }

        $this->cacheStore->save($key, $count + 1, 75);
        return false;
    }
}

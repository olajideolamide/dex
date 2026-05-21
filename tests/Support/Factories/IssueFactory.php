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

namespace Dex\Tests\Support\Factories;

use Dex\Support\DexTime;

/**
 * Test data factory for dex_issues rows.
 */
final class IssueFactory
{
    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    public static function open(array $overrides = []): array
    {
        return array_merge(self::base(), ['status' => 'open'], $overrides);
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    public static function resolved(array $overrides = []): array
    {
        return array_merge(self::base(), ['status' => 'resolved'], $overrides);
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    public static function ignored(array $overrides = []): array
    {
        return array_merge(self::base(), ['status' => 'ignored'], $overrides);
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    public static function regressed(array $overrides = []): array
    {
        return array_merge(self::base(), ['status' => 'regression'], $overrides);
    }

    /**
     * @return array<string, mixed>
     */
    private static function base(): array
    {
        $now = DexTime::nowUtcString();
        return [
            'fingerprint'   => 'fp-' . uniqid('', true),
            'level'         => 'error',
            'class'         => 'RuntimeException',
            'title'         => 'Test issue',
            'latest_path'   => '/test/path',
            'latest_method' => 'GET',
            'environment'   => 'testing',
            'times_seen'    => 1,
            'first_seen'    => $now,
            'last_seen'     => $now,
        ];
    }
}

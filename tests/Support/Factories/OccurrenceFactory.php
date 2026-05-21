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
 * Test data factory for dex_occurrences rows.
 */
final class OccurrenceFactory
{
    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    public static function forIssue(int $issueId, array $overrides = []): array
    {
        return array_merge([
            'issue_id'    => $issueId,
            'request_id'  => null,
            'happened_at' => DexTime::nowUtcString(),
            'message'     => 'Test exception message',
            'context'     => null,
        ], $overrides);
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    public static function withRequest(int $issueId, string $requestId, array $overrides = []): array
    {
        return array_merge(self::forIssue($issueId), [
            'request_id' => $requestId,
        ], $overrides);
    }

    /**
     * @param array<string, mixed> $context
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    public static function withContext(int $issueId, array $context, array $overrides = []): array
    {
        return array_merge(self::forIssue($issueId), [
            'context' => json_encode($context, JSON_UNESCAPED_SLASHES),
        ], $overrides);
    }
}

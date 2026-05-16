<?php

/**
 * This file is part of Dex.
 *
 * (c) Olajide Olanrewaju <jidemac4@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Dex\Contracts;

interface StorageInterface
{
    public function upsertIssue(array $issue): int;

    public function recordOccurrence(array $occurrence): void;

    public function recordRequest(array $request): void;
}

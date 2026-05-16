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

namespace Dex\Domain\Exceptions;

final class UiAccessDeniedException extends DexException
{
    public function __construct(
        string $message,
        private readonly bool $stealth
    ) {
        parent::__construct($message);
    }

    public function isStealth(): bool
    {
        return $this->stealth;
    }
}

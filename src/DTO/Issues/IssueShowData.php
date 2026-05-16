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

namespace Dex\DTO\Issues;

final class IssueShowData
{
    public function __construct(
        public readonly array $viewData
    ) {
    }

    public function toArray(): array
    {
        return $this->viewData;
    }
}

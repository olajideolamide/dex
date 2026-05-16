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

namespace Dex\DTO;

final class RequestMeta
{
    public function __construct(
        public readonly string $method,
        public readonly string $rawPath,
        public readonly ?string $ip,
        public readonly ?string $userAgent,
        public readonly ?string $incomingRequestId,
    ) {
    }
}

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

final readonly class ControllerInfo
{
    public function __construct(
        public ?string $controller,
        public ?string $action,
        public ?string $route,
        public ?array $params,
        public ?array $routeOptions = null,
    ) {
    }
}

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

use CodeIgniter\HTTP\ResponseInterface;
use Config\Services as AppServices;

class CiResponseFactory
{
    public function create(): ResponseInterface
    {
        return AppServices::response();
    }
}

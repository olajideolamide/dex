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
use Dex\DTO\ResponseMeta;

final class CiResponseApplier
{
    public static function apply(ResponseInterface $response, ResponseMeta $meta): void
    {
        foreach ($meta->headers() as $name => $value) {
            $response->setHeader($name, $value);
        }
    }
}

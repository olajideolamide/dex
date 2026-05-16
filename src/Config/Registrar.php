<?php

/**
 * This file is part of Dex.
 *
 * (c) Olajide Olanrewaju <jidemac4@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Dex\Config;

use Dex\Commands\Purge;
use Dex\Filters\DexUiFilter;

class Registrar
{
    // CodeIgniter Registrar methods are framework-defined and must use these names.
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public static function Filters(): array
    {
        return [
            'aliases' => [
                'dex-ui'     => DexUiFilter::class,
            ],
        ];
    }

    public static function Commands(): array
    {
        return [
            Purge::class
        ];
    }
    // phpcs:enable
}

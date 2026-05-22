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

use Throwable;

class CiRequestPathProvider
{
    public function currentPath(): ?string
    {
        try {
            $req = service('request');
            if (! $req) {
                return null;
            }
            return (string) $req->getUri()->getPath();
        } catch (Throwable) {
            return null;
        }
    }
}

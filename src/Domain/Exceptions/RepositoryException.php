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

use Dex\Domain\Exceptions\DexException;
use Throwable;

final class RepositoryException extends DexException
{
    public static function writeFailed(string $entity, ?Throwable $previous = null): self
    {
        return new self("Repository write failed for {$entity}.", 0, $previous);
    }

    public static function readFailed(string $entity, ?Throwable $previous = null): self
    {
        return new self("Repository read failed for {$entity}.", 0, $previous);
    }
}

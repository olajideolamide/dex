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

namespace Dex\Support;

use Dex\Domain\Exceptions\ContextNotInitializedException;

final class InMemoryContextStore
{
    private ?array $ctx = null;

    public function set(array &$ctx): void
    {
        // Keep reference so changes stay synced.
        $this->ctx = &$ctx;
    }

    public function get(): ?array
    {
        return $this->ctx;
    }

    /**
     * Return context or throw if it has not been initialized.
     */
    public function require(): array
    {
        if ($this->ctx === null) {
            throw new ContextNotInitializedException('Dex context not initialized.');
        }

        return $this->ctx;
    }

    public function clear(): void
    {
        $this->ctx = null;
    }
}

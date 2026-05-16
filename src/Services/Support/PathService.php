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

namespace Dex\Services\Support;

use Dex\Adapters\CiRequestPathProvider;
use Dex\Support\PathHelper;

/**
 * Handles path normalization and internal route detection.
 * Coordinates between PathHelper utilities and current request path.
 */
final class PathService
{
    public function __construct(
        private readonly object $config,
        private readonly CiRequestPathProvider $pathProvider,
    ) {
    }

    /**
     * Normalize a path (removes index.php, etc).
     */
    public function normalize(string $path): string
    {
        return PathHelper::normalizePath($path);
    }

    /**
     * Check if a path is internal (Dex UI or configured ignores).
     */
    public function isInternal(string $path): bool
    {
        return PathHelper::isInternalPath($path, $this->config);
    }

    /**
     * Public API for other components to check if path should be ignored.
     */
    public function shouldIgnore(string $path): bool
    {
        return $this->isInternal($path);
    }

    /**
     * Get the current request path live.
     */
    public function getCurrentPath(): ?string
    {
        return $this->pathProvider->currentPath();
    }

    /**
     * Check if current execution is internal/Dex UI.
     * Uses context path if available, otherwise checks live request path.
     */
    public function isCurrentPathInternal(?string $contextPath): bool
    {
        // Prefer context path if available
        $path = $contextPath ?? $this->getCurrentPath();

        if (!is_string($path) || $path === '') {
            return false;
        }

        return $this->isInternal($path);
    }

    /**
     * Check if a controller is internal (Dex).
     */
    public function isInternalController(string $controller): bool
    {
        return str_starts_with($controller, 'Dex\\');
    }
}

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

/**
 * Small, focused helpers for dealing with request paths.
 * Keeps path rules consistent across the library (ignore prefixes and internal UI routes).
 */
final class PathHelper
{
    /**
     * Normalize a request path (leading slash, no query, no trailing slash).
     */
    public static function normalizePath(string $path): string
    {
        $path = '/' . ltrim($path, '/');
        // collapse multiple slashes
        $path = (string) preg_replace('#/+#', '/', $path);

        // strip query string defensively if someone passes "/foo?bar=baz"
        $qPos = strpos($path, '?');
        if ($qPos !== false) {
            $path = substr($path, 0, $qPos);
        }

        return rtrim($path, '/') ?: '/';
    }

    /**
     * Decide if a path should be treated as internal Dex/UI traffic.
     */
    public static function isInternalPath(string $path, object $config): bool
    {
        $path = self::normalizePath($path);

        // Ignore Dex UI/routes by default (unless explicitly disabled)
        if (($config->ignoreSelfRoutes ?? true) === true) {
            $routePrefix = (string) ($config->routePrefix ?? 'dex');

            if (str_starts_with($path, '/' . $routePrefix)) {
                return true;
            }
        }

        // Configured ignore prefixes (privacy + noise reduction)
        $prefixes = (array) ($config->ignorePathPrefixes ?? []);
        foreach ($prefixes as $p) {
            $p = self::normalizePath((string) $p);
            if ($p !== '/' && str_starts_with($path, $p)) {
                return true;
            }
        }

        return false;
    }
}

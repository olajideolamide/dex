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

use Throwable;

/**
 * Captures and manages CI filter information for requests.
 * Detects active filters per request and captures them as breadcrumbs.
 */
final class FilterService
{
    private ?array $ctx = null;

    public function __construct(
        private readonly object $config,
        private readonly PathService $pathService,
    ) {
    }

    /**
     * Set the request context reference (called by Dex facade).
     */
    public function setContext(?array &$ctx): void
    {
        $this->ctx = &$ctx;
    }

    /**
     * Capture a best-effort summary of active CI filters for this request.
     */
    public function captureFiltersSummary(callable $addBreadcrumb): void
    {
        if (!$this->ctx) {
            return;
        }

        try {
            $filters = config('Filters');
            if (!is_object($filters)) {
                return;
            }

            $path = (string)($this->ctx['path'] ?? '/');
            $method = strtolower((string)($this->ctx['method'] ?? 'get'));

            $before = [];
            $after = [];

            // Globals
            $globals = $filters->globals ?? [];
            $before = array_merge($before, (array)($globals['before']));
            $after = array_merge($after, (array)($globals['after']));

            // HTTP method filters (treated as "before" per CI4 config shape)
            $methods = $filters->methods ?? [];
            if (is_array($methods) && isset($methods[$method])) {
                $before = array_merge($before, (array)$methods[$method]);
            }

            // URI-pattern filters
            $map = $filters->filters ?? [];
            if (is_array($map)) {
                foreach ($map as $alias => $rule) {
                    if (!is_array($rule)) {
                        continue;
                    }
                    // before patterns
                    foreach ((array)($rule['before'] ?? []) as $pat) {
                        if ($this->matchesFilterPattern($path, (string)$pat)) {
                            $before[] = (string)$alias;
                            break;
                        }
                    }
                    // after patterns
                    foreach ((array)($rule['after'] ?? []) as $pat) {
                        if ($this->matchesFilterPattern($path, (string)$pat)) {
                            $after[] = (string)$alias;
                            break;
                        }
                    }
                }
            }

            $before = $this->uniqueList($before);
            $after = $this->uniqueList($after);

            $this->ctx['filters_before'] = array_slice($before, 0, 40);
            $this->ctx['filters_after'] = array_slice($after, 0, 40);
            $this->ctx['filters_before_count'] = count($before);
            $this->ctx['filters_after_count'] = count($after);

            // Breadcrumb for quick show only counts + a few aliases.
            $addBreadcrumb('ci.filters', 'Filters summary', [
                'before_count' => $this->ctx['filters_before_count'],
                'after_count' => $this->ctx['filters_after_count'],
                'before' => array_slice($before, 0, 12),
                'after' => array_slice($after, 0, 12),
            ]);
        } catch (Throwable) {
            // best-effort
        }
    }

    /**
     * Match a URI path against a filter pattern (supports wildcards).
     */
    private function matchesFilterPattern(string $path, string $pattern): bool
    {
        // Simple glob-style matching
        $pattern = preg_quote($pattern, '#');
        $pattern = str_replace('\*', '.*', $pattern);
        return (bool)preg_match('#^' . $pattern . '$#', $path);
    }

    /**
     * De-duplicate list items while preserving order.
     */
    private function uniqueList(array $items): array
    {
        $out = [];
        $seen = [];
        foreach ($items as $it) {
            $s = (string)$it;
            if (!isset($seen[$s])) {
                $out[] = $it;
                $seen[$s] = true;
            }
        }
        return $out;
    }
}

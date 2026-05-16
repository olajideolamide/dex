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

use Dex\Config\Services;
use Dex\Support\ConfigResolver;
use Dex\Support\DexTime;

if (! function_exists('dex_config')) {
    function dex_config(): object
    {
        return ConfigResolver::resolve();
    }
}

if (! function_exists('dex_route_prefix')) {
    function dex_route_prefix(): string
    {
        $config = dex_config();
        $prefix = trim((string) ($config->routePrefix ?? ''));

        return $prefix !== '' ? $prefix : 'dex';
    }
}

if (! function_exists('dex_format_bytes')) {
    function dex_format_bytes(int|float $bytes): string
    {
        $bytes = (float) $bytes;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $unitIndex = 0;

        while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }

        return number_format($bytes, $unitIndex === 0 ? 0 : 2) . ' ' . $units[$unitIndex];
    }
}

if (! function_exists('dex_format_ms')) {
    function dex_format_ms(int|float $milliseconds): string
    {
        $milliseconds = (float) $milliseconds;

        if ($milliseconds > 0 && $milliseconds < 1) {
            return '<1 ms';
        }

        if ($milliseconds < 1000) {
            return number_format($milliseconds, 0) . ' ms';
        }

        return number_format($milliseconds / 1000, 2) . ' s';
    }
}

if (! function_exists('dex_format_datetime')) {
    function dex_format_datetime(?string $datetime): string
    {
        return DexTime::formatForDisplay($datetime, dex_config());
    }
}

if (! function_exists('dex_time_ago')) {
    function dex_time_ago(?string $datetime): string
    {
        return DexTime::timeAgo($datetime, dex_config());
    }
}

if (! function_exists('dex_age')) {
    function dex_age(?string $from, ?string $to = null): string
    {
        return DexTime::age($from, $to, dex_config());
    }
}

if (! function_exists('dex_code_snippet')) {
    /**
     * Read a small code snippet around a line number.
     * Returns null when the file is unreadable or outside ROOTPATH.
     */
    function dex_code_snippet(?string $file, ?int $line, int $radius = 3): ?array
    {
        $file = (string) ($file ?? '');
        $line = (int) ($line ?? 0);
        if ($file === '' || $line <= 0) {
            return null;
        }

        $realPath = realpath($file);
        if (! $realPath || ! is_file($realPath) || ! is_readable($realPath)) {
            return null;
        }

        $rootPath = realpath(ROOTPATH) ?: ROOTPATH;
        $normalizedFile = str_replace('\\', '/', $realPath);
        $normalizedRoot = str_replace('\\', '/', (string) $rootPath);

        if ($normalizedRoot !== '' && ! str_starts_with($normalizedFile, $normalizedRoot)) {
            return null;
        }

        $startLine = max(1, $line - $radius);
        $endLine = $line + $radius;
        $lines = [];

        try {
            $fileObject = new SplFileObject($realPath);
            $fileObject->seek($startLine - 1);

            for ($currentLine = $startLine; $currentLine <= $endLine && ! $fileObject->eof(); $currentLine++) {
                $text = rtrim((string) $fileObject->current(), "\r\n");
                $lines[] = [
                    'no' => $currentLine,
                    'text' => $text,
                ];
                $fileObject->next();
            }
        } catch (Throwable) {
            return null;
        }

        $relativePath = $normalizedRoot !== ''
            ? ltrim(str_replace($normalizedRoot, '', $normalizedFile), '/')
            : $normalizedFile;

        return [
            'file' => $realPath,
            'rel' => $relativePath,
            'line' => $line,
            'start' => $startLine,
            'end' => $endLine,
            'lines' => $lines,
        ];
    }
}

if (! function_exists('dex_breadcrumb')) {
    function dex_breadcrumb(string $category, string $message, array $data = [], string $level = 'info'): void
    {
        try {
            $data['_origin'] = $data['_origin'] ?? 'manual';
            Services::dex()->addBreadcrumb($category, $message, $data, $level);
        } catch (Throwable) {
            // Never break the host app.
        }
    }
}

if (! function_exists('dex_span_start')) {
    function dex_span_start(string $operation, ?string $description = null, array $tags = []): ?string
    {
        try {
            $tags['_origin'] = $tags['_origin'] ?? 'manual';

            return Services::dex()->startSpan($operation, $description, $tags);
        } catch (Throwable) {
            return null;
        }
    }
}

if (! function_exists('dex_span_finish')) {
    function dex_span_finish(?string $id): void
    {
        if (! $id) {
            return;
        }

        try {
            Services::dex()->finishSpan($id);
        } catch (Throwable) {
            // Never break the host app.
        }
    }
}

if (! function_exists('dex_span')) {
    function dex_span(string $operation, ?string $description, callable $callback, array $tags = [])
    {
        $spanId = dex_span_start($operation, $description, $tags);

        try {
            return $callback();
        } finally {
            dex_span_finish($spanId);
        }
    }
}

if (! function_exists('dex_capture_exception')) {
    function dex_capture_exception(Throwable $exception): void
    {
        try {
            Services::dex()->captureException($exception, false);
        } catch (Throwable) {
            // Never break the host app.
        }
    }
}

if (! function_exists('dex_kv_table')) {
    /**
     * @param array<int, array{k:string, v:mixed, mono?:bool, copy?:string}> $rows
     */
    function dex_kv_table(array $rows): string
    {
        $output = '<div class="table-responsive"><table class="table table-sm table-vcenter ms-kv-table mb-0"><tbody>';

        foreach ($rows as $row) {
            $key = (string) ($row['k'] ?? '');
            $value = $row['v'] ?? '';
            $isMonospace = (bool) ($row['mono'] ?? false);
            $copyValue = (string) ($row['copy'] ?? '');

            if ($value === null || $value === '') {
                continue;
            }

            $valueHtml = $isMonospace
                ? '<span>' . esc((string) $value) . '</span>'
                : esc((string) $value);

            $copyButton = '';
            if ($copyValue !== '') {
                $copyButton = '<button type="button" class="btn btn-sm btn-ghost-secondary ms-copy" data-copy="' . esc($copyValue) . '" title="Copy">'
                    . '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">'
                    . '<path stroke="none" d="M0 0h24v24H0z" fill="none"/>'
                    . '<rect x="8" y="8" width="12" height="12" rx="2" />'
                    . '<path d="M16 8V6a2 2 0 0 0 -2 -2H6a2 2 0 0 0 -2 2v8a2 2 0 0 0 2 2h2" />'
                    . '</svg>'
                    . '</button>';
            }

            $output .= '<tr>'
                . '<td class="text-muted" style="width: 34%;">' . esc($key) . '</td>'
                . '<td>' . $valueHtml . '</td>'
                . '<td class="text-end" style="width:1%;">' . $copyButton . '</td>'
                . '</tr>';
        }

        $output .= '</tbody></table></div>';

        return $output;
    }
}

if (! function_exists('dex_code_block')) {
    function dex_code_block(string $text, string $copy = ''): string
    {
        $text = rtrim($text);
        $actions = '';

        if ($copy !== '') {
            $actions = '<div class="ms-code-actions">'
                . '<button type="button" class="btn btn-sm btn-ghost-secondary ms-copy" data-copy="' . esc($copy) . '" title="Copy">'
                . '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">'
                . '<path stroke="none" d="M0 0h24v24H0z" fill="none"/>'
                . '<rect x="8" y="8" width="12" height="12" rx="2" />'
                . '<path d="M16 8V6a2 2 0 0 0 -2 -2H6a2 2 0 0 0 -2 2v8a2 2 0 0 0 2 2h2" />'
                . '</svg>'
                . '</button>'
                . '</div>';
        }

        return '<div class="ms-code-wrap">'
            . $actions
            . '<pre class="ms-code"><code>' . esc($text) . '</code></pre>'
            . '</div>';
    }
}

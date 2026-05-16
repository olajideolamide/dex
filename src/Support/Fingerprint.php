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

use Throwable;

class Fingerprint
{
    /**
     * Build a stable fingerprint for log messages (level + normalized text).
     */
    public static function fromMessage(string $level, string $message): string
    {
        $base = strtolower(trim($level)) . '|' . self::normalize($message);
        return substr(sha1($base), 0, 40);
    }

    /**
     * Build a fingerprint from exception class, message, and top stack frames.
     */
    public static function fromException(Throwable $e): string
    {
        $parts = [];
        $parts[] = get_class($e);
        $parts[] = self::normalize($e->getMessage());

        $trace = $e->getTrace();
        $max = min(6, count($trace));

        for ($i = 0; $i < $max; $i++) {
            $frame = $trace[$i] ?? [];
            $file = isset($frame['file']) ? self::shortPath((string) $frame['file']) : '';
            $line = isset($frame['line']) ? (int) $frame['line'] : 0;
            $func = isset($frame['function']) ? (string) $frame['function'] : '';
            $parts[] = "{$file}:{$line}:{$func}";
        }

        return substr(sha1(implode('|', $parts)), 0, 40);
    }

    /**
     * Build a fingerprint for fatal errors (message, file, line).
     */
    public static function fromFatal(array $err): string
    {
        $base = 'fatal|' . self::normalize((string) ($err['message'] ?? ''))
            . '|' . self::shortPath((string) ($err['file'] ?? ''))
            . '|' . (string) ((int) ($err['line'] ?? 0));

        return substr(sha1($base), 0, 40);
    }

    /**
     * Normalize strings for fingerprinting (trim, strip numbers, clamp length).
     */
    private static function normalize(string $string): string
    {
        $string = trim($string);
        $string = preg_replace('/\b\d+\b/', '{n}', $string) ?? $string;
        $string = preg_replace('/\s+/', ' ', $string) ?? $string;
        return mb_substr($string, 0, 500);
    }

    /**
     * Reduce a path to its last few segments for hashing.
     */
    private static function shortPath(string $path): string
    {
        $path = str_replace('\\', '/', $path);
        $parts = array_values(array_filter(explode('/', $path)));
        $tail = array_slice($parts, -3);
        return implode('/', $tail);
    }
}

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

class Scrubber
{
    /**
     * Recursively redact denied keys and clamp long strings.
     */
    public static function scrub(array $data, array $denyKeys = []): array
    {
        $denyLookup = [];
        foreach ($denyKeys as $denyKey) {
            $denyLookup[strtolower((string) $denyKey)] = true;
        }

        $walker = function ($value) use (&$walker, $denyLookup) {
            if (is_array($value)) {
                $out = [];
                foreach ($value as $itemKey => $itemValue) {
                    $key = is_string($itemKey) ? strtolower($itemKey) : (string) $itemKey;

                    if (is_string($itemKey) && isset($denyLookup[$key])) {
                        $out[$itemKey] = '[REDACTED]';
                        continue;
                    }

                    $out[$itemKey] = $walker($itemValue);
                }
                return $out;
            }

            if (is_string($value)) {
                if (mb_strlen($value) > 2000) {
                    return mb_substr($value, 0, 2000) . '…';
                }
            }

            return $value;
        };

        return $walker($data);
    }

    /**
     * Encode JSON within a byte cap, trimming safely if needed.
     */
    public static function safeJson(array $data, int $maxBytes = 24000): string
    {
        $json = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (! is_string($json)) {
            return '{}';
        }

        if (strlen($json) <= $maxBytes) {
            return $json;
        }

        // IMPORTANT:
        // Do NOT truncate raw JSON bytes (it produces invalid JSON).
        // Instead, shrink the payload while keeping it valid.

        // If this is a list (breadcrumbs/spans), keep the most recent tail.
        if (self::isList($data)) {
            $total = count($data);
            if ($total === 0) {
                return '[]';
            }

            $best = [$data[$total - 1]];
            $low = 1;
            $high = $total;
            while ($low <= $high) {
                $mid = (int) floor(($low + $high) / 2);
                $slice = array_slice($data, -$mid);
                $candidate = json_encode($slice, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                if (is_string($candidate) && strlen($candidate) <= $maxBytes) {
                    $best = $slice;
                    $low = $mid + 1;
                } else {
                    $high = $mid - 1;
                }
            }

            $payload = [
                '_truncated' => true,
                '_total'     => $total,
                '_kept'      => count($best),
                '_tail'      => true,
                'items'      => $best,
            ];

            $candidate = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            if (is_string($candidate) && strlen($candidate) <= $maxBytes) {
                return $candidate;
            }

            $candidate = json_encode($best, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            if (is_string($candidate) && strlen($candidate) <= $maxBytes) {
                return $candidate;
            }
        }

        // Fallback: return a tiny metadata-only payload.
        $fallback = [
            '_truncated' => true,
            '_keys'      => array_slice(array_map('strval', array_keys($data)), 0, 60),
        ];

        $json = json_encode($fallback, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return is_string($json) ? $json : '{}';
    }

    private static function isList(array $arr): bool
    {
        if ($arr === []) {
            return true;
        }
        $index = 0;
        foreach ($arr as $key => $_) {
            if ($key !== $index) {
                return false;
            }
            $index++;
        }
        return true;
    }
}

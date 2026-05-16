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

use Dex\Config\Dex as PackageDex;

final class ConfigResolver
{
    private static ?PackageDex $cached = null;

    /**
     * Resolve config from host .env first, then fall back to package defaults.
     */
    public static function resolve(): PackageDex
    {
        if (self::$cached instanceof PackageDex) {
            return self::$cached;
        }

        $cfg = new PackageDex();
        self::applyEnv($cfg);

        return self::$cached = $cfg;
    }

    /**
     * Apply DEX_* environment overrides onto the config instance.
     */
    private static function applyEnv(PackageDex $cfg): void
    {
        foreach (get_object_vars($cfg) as $prop => $current) {
            $envKey = self::propToEnvKey($prop);

            $raw = self::readEnv($envKey);
            if ($raw === null) {
                continue;
            }

            $cfg->{$prop} = self::castEnvValue($raw, $current);
        }
    }

    /**
     * Convert a config property name to its DEX_* env key.
     */
    private static function propToEnvKey(string $prop): string
    {
        $snake = preg_replace('/([a-z0-9])([A-Z])/', '$1_$2', $prop);
        $snake = strtoupper((string) $snake);

        return 'DEX_' . $snake;
    }

    private static function readEnv(string $envKey): ?string
    {
        $raw = getenv($envKey);
        if ($raw !== false) {
            return (string) $raw;
        }

        if (array_key_exists($envKey, $_ENV)) {
            return (string) $_ENV[$envKey];
        }

        if (array_key_exists($envKey, $_SERVER)) {
            return (string) $_SERVER[$envKey];
        }

        return null;
    }

    /**
     * Cast env string values to the current property type.
     */
    private static function castEnvValue(string $raw, mixed $current): mixed
    {
        $rawTrim = trim($raw);

        if (is_array($current)) {
            if ($rawTrim === '') {
                return [];
            }

            $first = $rawTrim[0] ?? '';
            if ($first === '[' || $first === '{') {
                $decoded = json_decode($rawTrim, true);
                if (is_array($decoded)) {
                    return $decoded;
                }
            }

            $parts = array_map('trim', explode(',', $rawTrim));
            $parts = array_values(array_filter($parts, static fn($v) => $v !== ''));
            return $parts;
        }

        if (is_bool($current)) {
            if ($rawTrim === '') {
                return false;
            }
            $isBoolean = filter_var($rawTrim, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            return $isBoolean ?? $current;
        }

        if (is_int($current)) {
            return is_numeric($rawTrim) ? (int) $rawTrim : $current;
        }

        if (is_float($current)) {
            return is_numeric($rawTrim) ? (float) $rawTrim : $current;
        }

        return $raw;
    }
}

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
 * This class provides utility methods to determine if a given IP address is allowed
 * based on an allowlist of IP addresses or CIDR ranges.
 *
 * The allowlist is provided as a comma-separated string, and each entry can either
 * be an individual IP address or a CIDR range.
 */
final class IpAllowList
{
    /**
     * Check an IP against a CSV allowlist of IPs/CIDR ranges.
     */
    public static function allowed(string $ip, string $allowlistCsv): bool
    {
        $ip = trim($ip);
        if ($ip === '') {
            return false;
        }

        $allowed = array_filter(array_map('trim', explode(',', (string) $allowlistCsv)));
        if (empty($allowed)) {
            return false;
        }

        foreach ($allowed as $rule) {
            if ($rule === '') {
                continue;
            }

            // exact match
            if (! str_contains($rule, '/')) {
                if (strcasecmp($ip, $rule) === 0) {
                    return true;
                }
                continue;
            }

            // CIDR
            if (self::cidrMatch($ip, $rule)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Match an IP against a CIDR rule (IPv4/IPv6).
     */
    private static function cidrMatch(string $ip, string $cidr): bool
    {
        [$net, $bits] = array_pad(explode('/', $cidr, 2), 2, null);
        $net = trim((string) $net);
        $bits = (int) trim((string) $bits);

        $ipBin  = @inet_pton($ip);
        $netBin = @inet_pton($net);

        if ($ipBin === false || $netBin === false) {
            return false;
        }
        if (strlen($ipBin) !== strlen($netBin)) {
            return false;
        }

        $maxBits = strlen($ipBin) * 8;
        if ($bits < 0) {
            $bits = 0;
        }
        if ($bits > $maxBits) {
            $bits = $maxBits;
        }

        $bytes = intdiv($bits, 8);
        $rem   = $bits % 8;

        // Compare full bytes
        if ($bytes > 0) {
            if (substr($ipBin, 0, $bytes) !== substr($netBin, 0, $bytes)) {
                return false;
            }
        }

        // Compare remaining bits
        if ($rem === 0) {
            return true;
        }

        $mask = chr((0xFF << (8 - $rem)) & 0xFF);
        return ((($ipBin[$bytes] ?? "\0") & $mask) === (($netBin[$bytes] ?? "\0") & $mask));
    }
}

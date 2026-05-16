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

namespace Dex\Adapters;

use Throwable;

final class CiDbBucketExpressionProvider
{
    public function hourBucketExpr(string $column): string
    {
        try {
            $db = db_connect();
            $driver = $db->DBDriver;
        } catch (Throwable) {
            $driver = '';
        }

        if ($driver === 'SQLite3') {
            return "strftime('%Y-%m-%d %H:00:00', {$column})";
        }
        if (stripos((string) $driver, 'Postgre') !== false) {
            return "to_char(date_trunc('hour', {$column}), 'YYYY-MM-DD HH24:00:00')";
        }

        return "DATE_FORMAT({$column}, '%Y-%m-%d %H:00:00')";
    }

    public function dayBucketExpr(string $column): string
    {
        try {
            $db = db_connect();
            $driver = $db->DBDriver;
        } catch (Throwable) {
            $driver = '';
        }

        if ($driver === 'SQLite3') {
            return "strftime('%Y-%m-%d', {$column})";
        }
        if (stripos((string) $driver, 'Postgre') !== false) {
            return "to_char(date_trunc('day', {$column}), 'YYYY-MM-DD')";
        }

        return "DATE_FORMAT({$column}, '%Y-%m-%d')";
    }
}

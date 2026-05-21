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

namespace Dex\Tests\Support\Config;

/**
 * Test database configuration registrar.
 *
 * Selects the active test database using environment variables.
 *
 * Usage:
 *   DB=SQLite3 composer test
 *   DB=MySQLi DB_HOST=127.0.0.1 DB_DATABASE=dex_test DB_USERNAME=root DB_PASSWORD=root composer test
 *
 * DB_SERVER=mysql or DB_SERVER=mariadb can be used for workflow readability,
 * but does NOT affect the CI4 driver — both use MySQLi.
 */
class Registrar
{
    /**
     * Returns an array of Database config properties to override for testing.
     *
     * @return array<string, mixed>
     */
    public static function database(): array
    {
        $driver = getenv('DB') ?: 'SQLite3';

        $sqliteGroup = self::sqliteGroup();
        $mysqlGroup  = self::mysqlGroup();

        $defaultGroup = ($driver === 'MySQLi') ? 'MySQLi' : 'SQLite3';

        return [
            'defaultGroup' => $defaultGroup,
            'tests'        => ($driver === 'MySQLi') ? $mysqlGroup : $sqliteGroup,
            'SQLite3'      => $sqliteGroup,
            'MySQLi'       => $mysqlGroup,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function sqliteGroup(): array
    {
        $dbPath = WRITEPATH . 'database/test.sqlite';

        // Ensure the directory exists when configuring.
        $dir = dirname($dbPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0o755, true);
        }

        return [
            'DSN'         => '',
            'hostname'    => 'localhost',
            'username'    => '',
            'password'    => '',
            'database'    => $dbPath,
            'DBDriver'    => 'SQLite3',
            'DBPrefix'    => '',
            'pConnect'    => false,
            'DBDebug'     => true,
            'charset'     => 'utf8',
            'DBCollat'    => 'utf8_general_ci',
            'swapPre'     => '',
            'encrypt'     => false,
            'compress'    => false,
            'strictOn'    => false,
            'failover'    => [],
            'foreignKeys' => true,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function mysqlGroup(): array
    {
        return [
            'DSN'      => '',
            'hostname' => getenv('DB_HOST') ?: '127.0.0.1',
            'username' => getenv('DB_USERNAME') ?: 'root',
            'password' => getenv('DB_PASSWORD') ?: '',
            'database' => getenv('DB_DATABASE') ?: 'dex_test',
            'DBDriver' => 'MySQLi',
            'DBPrefix' => '',
            'pConnect' => false,
            'DBDebug'  => true,
            'charset'  => 'utf8mb4',
            'DBCollat' => 'utf8mb4_general_ci',
            'swapPre'  => '',
            'encrypt'  => false,
            'compress' => false,
            'strictOn' => false,
            'failover' => [],
            'port'     => (int) (getenv('DB_PORT') ?: 3306),
        ];
    }
}

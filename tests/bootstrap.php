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

// Ensure Composer autoload is available for both the library and CI4.
require dirname(__DIR__) . '/vendor/autoload.php';

// Optionally bootstrap CI4 only when explicitly requested.
$ciBootstrap = dirname(__DIR__) . '/vendor/codeigniter4/framework/system/Test/bootstrap.php';
if (is_file($ciBootstrap) && getenv('MINISENTRY_USE_CI_BOOTSTRAP') === '1') {
    require $ciBootstrap;
}

// Stub Config\Modules when running tests outside a full CI4 app.
if (! class_exists('Config\\Modules')) {
    class DexTestModules
    {
        public function shouldDiscover(string $type): bool
        {
            return false;
        }
    }

    class_alias(DexTestModules::class, 'Config\\Modules');
}

// Sensible defaults for tests when running inside another project.
if (! defined('ENVIRONMENT')) {
    define('ENVIRONMENT', 'testing');
}

$_SERVER['CI_ENVIRONMENT'] = $_SERVER['CI_ENVIRONMENT'] ?? 'testing';

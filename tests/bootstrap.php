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

require dirname(__DIR__) . '/vendor/autoload.php';

$ciBootstrap = dirname(__DIR__) . '/vendor/codeigniter4/framework/system/Test/bootstrap.php';
if (is_file($ciBootstrap) && ! defined('ENVIRONMENT')) {
    require $ciBootstrap;
}

// Stub Config\Modules when running tests outside a full CI4 app.
if (! class_exists('Config\\Modules')) {
    class_alias(\Dex\Tests\Support\DexTestModules::class, 'Config\\Modules');
}

if (! defined('ENVIRONMENT')) {
    define('ENVIRONMENT', 'testing');
}

if (getenv('CI_ENVIRONMENT') === false) {
    putenv('CI_ENVIRONMENT=testing');
    $_ENV['CI_ENVIRONMENT'] = 'testing';
}

<?php

/**
 * This file is part of Dex.
 *
 * (c) Olajide Olanrewaju <jidemac4@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

use CodeIgniter\Events\Events;
use Config\Services as AppServices;
use Dex\Config\Services as DexServices;

// Bootstrap handlers early (web)
Events::on('pre_system', static function () {
    DexServices::dex()->bootstrap();
    DexServices::dex()->startRequest(
        AppServices::request(),
        AppServices::response()
    );
    DexServices::dex()->markLifecycleEvent('ci.pre_system', 'Pre System', [
        'memory_bytes' => memory_get_usage(true),
    ]);
});

Events::on('post_controller_constructor', static function () {
    $cfg = DexServices::configProvider()->get();

    DexServices::dex()->tagController();
    DexServices::dex()->markLifecycleEvent('ci.post_controller_constructor', 'Controller Ready');

    try {
        static $enabled = null;
        if ($enabled === null) {
            $enabled = (bool)($cfg->captureCiLifecycleBreadcrumbs ?? true);
        }
        if ($enabled) {
            DexServices::dex()->addBreadcrumb('ci.event', 'post_controller_constructor');
        }
    } catch (Throwable) {
        // ignore
    }
});

Events::on('pre_controller', static function () {
    $cfg = DexServices::configProvider()->get();
    DexServices::dex()->markLifecycleEvent('ci.pre_controller', 'Pre Controller');
    try {
        static $enabled = null;
        if ($enabled === null) {
            $enabled = (bool)($cfg->captureCiLifecycleBreadcrumbs ?? true);
        }
        if ($enabled) {
            DexServices::dex()->addBreadcrumb('ci.event', 'pre_controller');
        }
    } catch (Throwable) {
        // ignore
    }
});

Events::on('post_controller', static function () {
    $cfg = DexServices::configProvider()->get();
    DexServices::dex()->markLifecycleEvent('ci.post_controller', 'Controller Complete');
    try {
        static $enabled = null;
        if ($enabled === null) {
            $enabled = (bool)($cfg->captureCiLifecycleBreadcrumbs ?? true);
        }
        if ($enabled) {
            DexServices::dex()->addBreadcrumb('ci.event', 'post_controller');
        }
    } catch (Throwable) {
        // ignore
    }
});

Events::on('DBQuery', static function ($query) {
    DexServices::dex()->trackDbQuery($query);
});

Events::on('post_system', static function () {
    $cfg = DexServices::configProvider()->get();
    DexServices::dex()->markLifecycleEvent('ci.post_system', 'Post System', [
        'memory_peak_bytes' => memory_get_peak_usage(true),
    ]);
    try {
        static $enabled = null;
        if ($enabled === null) {
            $enabled = (bool)($cfg->captureCiLifecycleBreadcrumbs ?? true);
        }
        if ($enabled) {
            DexServices::dex()->addBreadcrumb('ci.event', 'post_system');
        }
    } catch (Throwable) {
        // ignore
    }
    DexServices::dex()->finishRequest(AppServices::response());
});

// Bootstrap handlers for CLI too (captures unhandled exceptions + shutdown fatals)
Events::on('pre_command', static function () {
    DexServices::dex()->bootstrap();
});

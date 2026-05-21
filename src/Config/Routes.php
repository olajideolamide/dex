<?php

/**
 * This file is part of Dex.
 *
 * (c) Olajide Olanrewaju <jidemac4@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

/** @var RouteCollection $routes */

use CodeIgniter\Router\RouteCollection;

helper('dex');
$cfg = \Dex\Config\Services::configProvider()->get();

$prefix = trim((string) ($cfg->routePrefix ?? 'dex'), '/');
if ($prefix === '') {
    $prefix = 'dex';
}

// Protected UI (must pass both access + UI-session)
$routes->group($prefix, [
    'namespace' => 'Dex\Controllers',
    'filter'    => 'dexui',
], static function ($routes) {
    $routes->get('', 'Issues::index');
    $routes->get('issues/data', 'Issues::data');
    $routes->get('issues/(:num)/dialog', 'Issues::dialog/$1');
    $routes->get('issues/(:num)/dialog/event', 'Issues::dialogEvent/$1');
    $routes->get('issues/(:num)/dialog/tab/(:segment)', 'Issues::dialogTab/$1/$2');
    $routes->post('issues/(:num)/resolve', 'Issues::resolve/$1');
    $routes->post('issues/(:num)/ignore', 'Issues::ignore/$1');
});

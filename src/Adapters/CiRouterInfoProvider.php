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

use Dex\DTO\ControllerInfo;
use Throwable;

final class CiRouterInfoProvider
{
    public function getControllerInfo(): ControllerInfo
    {
        try {
            $router = service('router');
        } catch (Throwable) {
            $router = null;
        }

        if (! $router) {
            return new ControllerInfo(null, null, null, null, null);
        }

        $controller = method_exists($router, 'controllerName') ? $router->controllerName() : null;
        $action = method_exists($router, 'methodName') ? $router->methodName() : null;

        $route = null;
        $params = null;
        $routeOptions = null;

        try {
            if (method_exists($router, 'getMatchedRoute')) {
                $matched = $router->getMatchedRoute();
                if (is_array($matched)) {
                    $route = $matched[0] ?? null;
                }
            }
            if (method_exists($router, 'getMatchedRouteOptions')) {
                $options = $router->getMatchedRouteOptions();
                if (is_array($options)) {
                    $routeOptions = $options;
                }
            }
            if (method_exists($router, 'params')) {
                $p = $router->params();
                $params = is_array($p) ? $p : null;
            }
        } catch (Throwable) {
            // ignore
        }

        return new ControllerInfo(
            $controller ? (string) $controller : null,
            $action ? (string) $action : null,
            $route ? (string) $route : null,
            $params,
            $routeOptions
        );
    }
}

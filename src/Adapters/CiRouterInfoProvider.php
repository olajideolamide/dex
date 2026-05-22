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

use CodeIgniter\Router\Router;
use Dex\DTO\ControllerInfo;
use Throwable;

final class CiRouterInfoProvider
{
    public function getControllerInfo(): ControllerInfo
    {
        $router = $this->resolveRouter();

        if ($router instanceof Router) {
            return $this->buildFromRouter($router);
        }

        if (
            is_object($router)
            && is_callable([$router, 'controllerName'])
            && is_callable([$router, 'methodName'])
        ) {
            return $this->buildFromRouterLike($router);
        }

        return new ControllerInfo(null, null, null, null, null);
    }

    private function buildFromRouter(Router $router): ControllerInfo
    {
        $controller = $router->controllerName();
        $action = $router->methodName();
        $route = null;
        $params = null;
        $routeOptions = null;

        try {
            $matched = $router->getMatchedRoute();
            if (is_array($matched)) {
                $route = $matched[0] ?? null;
            }

            $options = $router->getMatchedRouteOptions();
            if (is_array($options)) {
                $routeOptions = $options;
            }

            $routeParams = $router->params();
            if (is_array($routeParams)) {
                $params = $routeParams;
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

    private function buildFromRouterLike(object $router): ControllerInfo
    {
        $controller = $router->controllerName();
        $action = $router->methodName();
        $route = null;
        $params = null;
        $routeOptions = null;

        try {
            if (is_callable([$router, 'getMatchedRoute'])) {
                $matched = $router->getMatchedRoute();
                if (is_array($matched)) {
                    $route = $matched[0] ?? null;
                }
            }

            if (is_callable([$router, 'getMatchedRouteOptions'])) {
                $options = $router->getMatchedRouteOptions();
                if (is_array($options)) {
                    $routeOptions = $options;
                }
            }

            if (is_callable([$router, 'params'])) {
                $routeParams = $router->params();
                if (is_array($routeParams)) {
                    $params = $routeParams;
                }
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

    private function resolveRouter()
    {
        try {
            return service('router');
        } catch (Throwable) {
            return null;
        }
    }
}

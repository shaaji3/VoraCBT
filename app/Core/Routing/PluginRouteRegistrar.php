<?php

declare(strict_types=1);

namespace App\Core\Routing;

use FastRoute\RouteCollector;
use App\Core\Plugins\PluginRegistry;

final class PluginRouteRegistrar
{
    public static function register(RouteCollector $collector, string $type = 'web', ?string $configPath = null): void
    {
        $plugins = (new PluginRegistry())->enabled($configPath);

        foreach ($plugins as $plugin) {
            $routeFile = $plugin['path'] . '/routes/' . $type . '.php';
            if (!is_file($routeFile)) {
                continue;
            }

            $routes = require $routeFile;
            if (is_callable($routes)) {
                $routes($collector);
            }
        }
    }
}

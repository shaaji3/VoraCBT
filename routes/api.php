<?php

use FastRoute\RouteCollector;
use App\Core\Routing\PluginRouteRegistrar;
use App\Http\Controllers\Api\HealthController;

return function (RouteCollector $r) {
    $usePluginRouting = (($_ENV['APP_PLUGIN_ROUTING'] ?? 'false') === 'true');
    if ($usePluginRouting) {
        PluginRouteRegistrar::register($r, 'api');
    }

    $r->get('/health', [HealthController::class, 'show']);
};
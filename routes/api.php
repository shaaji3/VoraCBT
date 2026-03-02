<?php

use FastRoute\RouteCollector;
use App\Core\Routing\PluginRouteRegistrar;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\IntegrationController;

return function (RouteCollector $r) {
    $usePluginRouting = (($_ENV['APP_PLUGIN_ROUTING'] ?? 'false') === 'true');
    if ($usePluginRouting) {
        PluginRouteRegistrar::register($r, 'api');
    }

    $r->get('/health', [HealthController::class, 'show']);
    $r->get('/api/v1/students', [IntegrationController::class, 'students']);
    $r->get('/api/v1/staff', [IntegrationController::class, 'staff']);
    $r->post('/api/v1/sync/students', [IntegrationController::class, 'syncStudents']);
    $r->post('/api/v1/sync/staff', [IntegrationController::class, 'syncStaff']);
    $r->post('/api/v1/results/import', [IntegrationController::class, 'resultsImport']);

};
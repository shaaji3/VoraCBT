<?php

declare(strict_types=1);

use App\Plugins\Admin\Controllers\AdminApiController;
use FastRoute\RouteCollector;

return static function (RouteCollector $r): void {
    $r->addGroup('/api', function (RouteCollector $r): void {
        $r->post('/admin/students/import/preview', [AdminApiController::class, 'previewImport']);
        $r->post('/admin/students/import/commit', [AdminApiController::class, 'commitImport']);
        $r->get('/admin/students/credentials/export', [AdminApiController::class, 'exportCredentials']);
        $r->get('/admin/logs', [AdminApiController::class, 'logs']);
    });
};

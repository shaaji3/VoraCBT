<?php

declare(strict_types=1);

use App\Plugins\Admin\Controllers\AdminApiController;
use FastRoute\RouteCollector;

return static function (RouteCollector $r): void {
    $r->addGroup('/api', function (RouteCollector $r): void {
        $r->get('/admin/dashboard/overview', [AdminApiController::class, 'dashboardOverview']);
        $r->get('/admin/analytics/summary', [AdminApiController::class, 'analyticsSummary']);
        $r->get('/admin/roles/summary', [AdminApiController::class, 'rolesSummary']);
        $r->get('/admin/roles-permissions', [AdminApiController::class, 'rolesPermissions']);
        $r->post('/admin/roles-permissions', [AdminApiController::class, 'saveRolesPermissions']);
        $r->get('/admin/settings', [AdminApiController::class, 'settings']);
        $r->post('/admin/settings', [AdminApiController::class, 'saveSettings']);
        $r->get('/admin/questions/summary', [AdminApiController::class, 'questionsSummary']);
        $r->get('/admin/grading/pending', [AdminApiController::class, 'pendingGradingSummary']);
        $r->post('/admin/students/import/preview', [AdminApiController::class, 'previewImport']);
        $r->post('/admin/students/import/commit', [AdminApiController::class, 'commitImport']);
        $r->get('/admin/students/credentials/export', [AdminApiController::class, 'exportCredentials']);
        $r->get('/admin/logs', [AdminApiController::class, 'logs']);
    });
};

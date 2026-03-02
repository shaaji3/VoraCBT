<?php

declare(strict_types=1);

use App\Plugins\Admin\Controllers\AdminWebController;
use FastRoute\RouteCollector;

return static function (RouteCollector $r): void {
    $r->get('/admin/dashboard', [AdminWebController::class, 'dashboard']);
    $r->get('/admin/exams/create', [AdminWebController::class, 'createExam']);
    $r->get('/admin/analytics', [AdminWebController::class, 'analytics']);
    $r->get('/admin/roles-permissions', [AdminWebController::class, 'rolesPermissions']);
    $r->get('/admin/settings', [AdminWebController::class, 'settings']);
    $r->get('/admin/questions', [AdminWebController::class, 'questionRepository']);
    $r->get('/admin/questions/import', [AdminWebController::class, 'bulkUpload']);
    $r->get('/admin/grading/manual', [AdminWebController::class, 'manualGrading']);
};

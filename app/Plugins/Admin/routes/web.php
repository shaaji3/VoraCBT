<?php

declare(strict_types=1);

use App\Plugins\Admin\Controllers\AdminWebController;
use FastRoute\RouteCollector;

return static function (RouteCollector $r): void {
    $r->get('/admin/dashboard', [AdminWebController::class, 'dashboard']);
    $r->get('/admin/exams/create', [AdminWebController::class, 'createExam']);
};

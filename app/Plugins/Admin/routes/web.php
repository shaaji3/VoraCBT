<?php

declare(strict_types=1);

use App\Http\Controllers\Web\PageController;
use FastRoute\RouteCollector;

return static function (RouteCollector $r): void {
    $r->get('/admin/dashboard', [PageController::class, 'adminDashboard']);
    $r->get('/admin/exams/create', [PageController::class, 'adminExamCreate']);
};

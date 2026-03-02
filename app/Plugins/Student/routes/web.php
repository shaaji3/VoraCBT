<?php

declare(strict_types=1);

use App\Http\Controllers\Web\PageController;
use FastRoute\RouteCollector;

return static function (RouteCollector $r): void {
    $r->get('/student/dashboard', [PageController::class, 'studentDashboard']);
    $r->get('/student/exam', [PageController::class, 'studentExam']);
    $r->get('/student/results', [PageController::class, 'studentResults']);
};

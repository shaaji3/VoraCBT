<?php

declare(strict_types=1);

use App\Plugins\Student\Controllers\StudentWebController;
use FastRoute\RouteCollector;

return static function (RouteCollector $r): void {
    $r->get('/student/dashboard', [StudentWebController::class, 'dashboard']);
    $r->get('/student/exam', [StudentWebController::class, 'exam']);
    $r->get('/student/results', [StudentWebController::class, 'results']);
};

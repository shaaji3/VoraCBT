<?php

declare(strict_types=1);

use App\Plugins\Student\Controllers\StudentExamApiController;
use FastRoute\RouteCollector;

return static function (RouteCollector $r): void {
    $r->addGroup('/api', function (RouteCollector $r): void {
        $r->get('/student/dashboard/overview', [StudentExamApiController::class, 'overview']);
        $r->post('/proctoring/event', [StudentExamApiController::class, 'proctoringEvent']);
        $r->post('/proctoring/heartbeat', [StudentExamApiController::class, 'proctoringHeartbeat']);
        $r->post('/exam/autosave', [StudentExamApiController::class, 'autosave']);
        $r->get('/exam/resume-state', [StudentExamApiController::class, 'resumeState']);
    });
};

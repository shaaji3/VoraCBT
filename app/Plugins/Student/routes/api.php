<?php

declare(strict_types=1);

use App\Plugins\Student\Controllers\StudentExamApiController;
use FastRoute\RouteCollector;

return static function (RouteCollector $r): void {
    $r->addGroup('/api', function (RouteCollector $r): void {
        $r->get('/student/dashboard/overview', [StudentExamApiController::class, 'overview']);
        $r->get('/student/results', [StudentExamApiController::class, 'results']);
        $r->get('/student/exams/{sessionId}', [StudentExamApiController::class, 'exam']);
        $r->post('/student/exams/{sessionId}/answers', [StudentExamApiController::class, 'saveAnswer']);
        $r->post('/student/exams/{sessionId}/submit', [StudentExamApiController::class, 'submit']);

        $r->post('/exams/{sessionId}/proctoring-events', [StudentExamApiController::class, 'proctoringEventBySession']);
        $r->post('/proctoring/event', [StudentExamApiController::class, 'proctoringEvent']);
        $r->post('/proctoring/heartbeat', [StudentExamApiController::class, 'proctoringHeartbeat']);
        $r->post('/exam/autosave', [StudentExamApiController::class, 'autosave']);
        $r->get('/exam/resume-state', [StudentExamApiController::class, 'resumeState']);
    });
};

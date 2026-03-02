<?php

declare(strict_types=1);

use App\Plugins\Teacher\Controllers\TeacherApiController;
use FastRoute\RouteCollector;

return static function (RouteCollector $r): void {
    $r->addGroup('/api', function (RouteCollector $r): void {
        $r->get('/teacher/questions/repository', [TeacherApiController::class, 'questionRepository']);
        $r->get('/teacher/grading/pending', [TeacherApiController::class, 'pendingManualGrading']);
        $r->get('/teacher/grading/{sessionId}/answers', [TeacherApiController::class, 'ungradedAnswers']);
    });
};

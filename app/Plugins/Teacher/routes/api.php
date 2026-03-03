<?php

declare(strict_types=1);

use App\Plugins\Teacher\Controllers\TeacherApiController;
use FastRoute\RouteCollector;

return static function (RouteCollector $r): void {
    $r->addGroup('/api', function (RouteCollector $r): void {
        $r->get('/teacher/questions/repository', [TeacherApiController::class, 'questionRepository']);
        $r->get('/teacher/questions/{id}', [TeacherApiController::class, 'showQuestion']);
        $r->post('/teacher/questions', [TeacherApiController::class, 'createQuestion']);
        $r->put('/teacher/questions/{id}', [TeacherApiController::class, 'updateQuestion']);
        $r->delete('/teacher/questions/{id}', [TeacherApiController::class, 'deleteQuestion']);

        $r->get('/teacher/grading/pending', [TeacherApiController::class, 'pendingManualGrading']);
        $r->get('/teacher/grading/{sessionId}/answers', [TeacherApiController::class, 'ungradedAnswers']);
        $r->post('/teacher/grading/{answerId}/score', [TeacherApiController::class, 'scoreAnswer']);
    });
};

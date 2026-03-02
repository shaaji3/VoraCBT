<?php

declare(strict_types=1);

use App\Plugins\Teacher\Controllers\TeacherWebController;
use FastRoute\RouteCollector;

return static function (RouteCollector $r): void {
    $r->get('/teacher/questions', [TeacherWebController::class, 'questionRepository']);
    $r->get('/teacher/questions/create-mcq', [TeacherWebController::class, 'createMcq']);
    $r->get('/teacher/questions/create-fill-blank', [TeacherWebController::class, 'createFillBlank']);
    $r->get('/teacher/questions/create-matching', [TeacherWebController::class, 'createMatching']);
    $r->get('/teacher/questions/create-passage', [TeacherWebController::class, 'createPassage']);
    $r->get('/teacher/questions/import', [TeacherWebController::class, 'bulkUpload']);
    $r->get('/teacher/grading', [TeacherWebController::class, 'manualGrading']);
};

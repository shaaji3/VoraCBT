<?php

declare(strict_types=1);

use App\Http\Controllers\Api\ExamRecoveryController;
use App\Http\Controllers\Api\ProctoringController;
use App\Http\Controllers\Api\StudentDashboardController;
use App\Plugins\Support\RouteAuthorizer;
use FastRoute\RouteCollector;

return static function (RouteCollector $r): void {
    $authorizeStudentExam = static function (callable $action): void {
        RouteAuthorizer::authorize(['student', 'admin', 'super_admin'], $action);
    };

    $r->addGroup('/api', function (RouteCollector $r) use ($authorizeStudentExam): void {
        $r->get('/student/dashboard/overview', function () use ($authorizeStudentExam): void {
            $authorizeStudentExam(static function (): void {
                (new StudentDashboardController())->overview();
            });
        });

        $r->post('/proctoring/event', function () use ($authorizeStudentExam): void {
            $authorizeStudentExam(static function (): void {
                (new ProctoringController())->logEvent();
            });
        });

        $r->post('/proctoring/heartbeat', function () use ($authorizeStudentExam): void {
            $authorizeStudentExam(static function (): void {
                (new ProctoringController())->heartbeat();
            });
        });

        $r->post('/exam/autosave', function () use ($authorizeStudentExam): void {
            $authorizeStudentExam(static function (): void {
                (new ExamRecoveryController())->autosave();
            });
        });

        $r->get('/exam/resume-state', function () use ($authorizeStudentExam): void {
            $authorizeStudentExam(static function (): void {
                (new ExamRecoveryController())->resumeState();
            });
        });
    });
};

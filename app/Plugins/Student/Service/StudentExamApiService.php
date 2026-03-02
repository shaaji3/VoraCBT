<?php

declare(strict_types=1);

namespace App\Plugins\Student\Service;

use App\Http\Controllers\Api\ExamRecoveryController;
use App\Http\Controllers\Api\ProctoringController;
use App\Http\Controllers\Api\StudentDashboardController;
use App\Plugins\Support\RouteAuthorizer;

final class StudentExamApiService
{
    public function overview(): void
    {
        RouteAuthorizer::authorize(['student', 'admin', 'super_admin'], static function (): void {
            (new StudentDashboardController())->overview();
        });
    }

    public function proctoringEvent(): void
    {
        RouteAuthorizer::authorize(['student', 'admin', 'super_admin'], static function (): void {
            (new ProctoringController())->logEvent();
        });
    }

    public function proctoringHeartbeat(): void
    {
        RouteAuthorizer::authorize(['student', 'admin', 'super_admin'], static function (): void {
            (new ProctoringController())->heartbeat();
        });
    }

    public function autosave(): void
    {
        RouteAuthorizer::authorize(['student', 'admin', 'super_admin'], static function (): void {
            (new ExamRecoveryController())->autosave();
        });
    }

    public function resumeState(): void
    {
        RouteAuthorizer::authorize(['student', 'admin', 'super_admin'], static function (): void {
            (new ExamRecoveryController())->resumeState();
        });
    }
}

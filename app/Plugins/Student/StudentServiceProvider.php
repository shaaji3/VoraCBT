<?php

declare(strict_types=1);

namespace App\Plugins\Student;

use App\Core\Container\Container;
use App\Core\Contracts\PluginServiceProviderInterface;
use App\Core\Database\DatabaseManager;
use App\Core\Service\AuditLogService;
use App\Domain\Exam\Service\SessionRecoveryService;
use App\Domain\Exam\Service\TimerService;
use App\Domain\Proctoring\Service\DeviceFingerprintService;
use App\Domain\Proctoring\Service\ProctoringService;
use App\Domain\Proctoring\Service\SessionIntegrityService;
use App\Plugins\Student\Controllers\StudentExamApiController;
use App\Plugins\Student\Controllers\StudentWebController;
use App\Plugins\Student\Service\StudentExamApiService;
use App\Plugins\Student\Service\StudentWebService;
use App\Plugins\Support\Service\TemplateRenderService;

final class StudentServiceProvider implements PluginServiceProviderInterface
{
    public function register(): void
    {
        $container = Container::getInstance();

        $container->set(TemplateRenderService::class, static fn(Container $c) => new TemplateRenderService());
        $container->set(TimerService::class, static fn(Container $c) => new TimerService());
        $container->set(SessionIntegrityService::class, static fn(Container $c) => new SessionIntegrityService());
        $container->set(AuditLogService::class, static fn(Container $c) => new AuditLogService());
        $container->set(DeviceFingerprintService::class, static fn(Container $c) => new DeviceFingerprintService());
        $container->set(ProctoringService::class, static fn(Container $c) => new ProctoringService(
            $c->get(AuditLogService::class),
            $c->get(DeviceFingerprintService::class),
        ));
        $container->set(SessionRecoveryService::class, static fn(Container $c) => new SessionRecoveryService(
            DatabaseManager::getConnection(),
            $c->get(TimerService::class),
        ));

        $container->set(StudentExamApiService::class, static fn(Container $c) => new StudentExamApiService(
            DatabaseManager::getConnection(),
            $c->get(ProctoringService::class),
            $c->get(SessionRecoveryService::class),
            $c->get(SessionIntegrityService::class),
        ));
        $container->set(StudentExamApiController::class, static fn(Container $c) => new StudentExamApiController($c->get(StudentExamApiService::class)));

        $container->set(StudentWebService::class, static fn(Container $c) => new StudentWebService($c->get(TemplateRenderService::class)));
        $container->set(StudentWebController::class, static fn(Container $c) => new StudentWebController($c->get(StudentWebService::class)));
    }

    public function boot(): void
    {
    }
}

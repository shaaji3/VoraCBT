<?php

declare(strict_types=1);

namespace App\Plugins\Student;

use App\Core\Container\Container;
use App\Core\Contracts\PluginServiceProviderInterface;
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

        $container->set(StudentExamApiService::class, static fn(Container $c) => new StudentExamApiService());
        $container->set(StudentExamApiController::class, static fn(Container $c) => new StudentExamApiController($c->get(StudentExamApiService::class)));

        $container->set(StudentWebService::class, static fn(Container $c) => new StudentWebService($c->get(TemplateRenderService::class)));
        $container->set(StudentWebController::class, static fn(Container $c) => new StudentWebController($c->get(StudentWebService::class)));
    }

    public function boot(): void
    {
    }
}

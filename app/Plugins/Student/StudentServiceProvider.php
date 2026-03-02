<?php

declare(strict_types=1);

namespace App\Plugins\Student;

use App\Core\Container\Container;
use App\Core\Contracts\PluginServiceProviderInterface;
use App\Plugins\Student\Controllers\StudentExamApiController;
use App\Plugins\Student\Service\StudentExamApiService;

final class StudentServiceProvider implements PluginServiceProviderInterface
{
    public function register(): void
    {
        $container = Container::getInstance();

        $container->set(StudentExamApiService::class, static fn(Container $c) => new StudentExamApiService());
        $container->set(StudentExamApiController::class, static fn(Container $c) => new StudentExamApiController($c->get(StudentExamApiService::class)));
    }

    public function boot(): void
    {
    }
}

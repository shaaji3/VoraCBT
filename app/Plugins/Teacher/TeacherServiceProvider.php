<?php

declare(strict_types=1);

namespace App\Plugins\Teacher;

use App\Core\Container\Container;
use App\Core\Contracts\PluginServiceProviderInterface;
use App\Core\Database\DatabaseManager;
use App\Plugins\Support\Service\TemplateRenderService;
use App\Plugins\Teacher\Controllers\TeacherApiController;
use App\Plugins\Teacher\Controllers\TeacherWebController;
use App\Plugins\Teacher\Service\TeacherApiService;
use App\Plugins\Teacher\Service\TeacherWebService;

final class TeacherServiceProvider implements PluginServiceProviderInterface
{
    public function register(): void
    {
        $container = Container::getInstance();
        $container->set(TemplateRenderService::class, static fn(Container $c) => new TemplateRenderService());
        $container->set(TeacherWebService::class, static fn(Container $c) => new TeacherWebService($c->get(TemplateRenderService::class)));
        $container->set(TeacherApiService::class, static fn(Container $c) => new TeacherApiService(
            DatabaseManager::getConnection(),
        ));
        $container->set(TeacherApiController::class, static fn(Container $c) => new TeacherApiController($c->get(TeacherApiService::class)));
        $container->set(TeacherWebController::class, static fn(Container $c) => new TeacherWebController($c->get(TeacherWebService::class)));
    }

    public function boot(): void
    {
    }
}

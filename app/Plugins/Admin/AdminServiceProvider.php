<?php

declare(strict_types=1);

namespace App\Plugins\Admin;

use App\Core\Container\Container;
use App\Core\Contracts\PluginServiceProviderInterface;
use App\Core\Database\DatabaseManager;
use App\Domain\Identity\CredentialExportService;
use App\Domain\Identity\StaffImportService;
use App\Domain\Identity\StudentImportService;
use App\Domain\Monitoring\Service\AdminLogViewerService;
use App\Plugins\Admin\Controllers\AdminApiController;
use App\Plugins\Admin\Controllers\AdminWebController;
use App\Plugins\Admin\Service\AdminApiService;
use App\Plugins\Admin\Service\AdminWebService;
use App\Plugins\Support\Service\AuthContextService;
use App\Plugins\Support\Service\TemplateRenderService;

final class AdminServiceProvider implements PluginServiceProviderInterface
{
    public function register(): void
    {
        $container = Container::getInstance();

        $container->set(TemplateRenderService::class, static fn(Container $c) => new TemplateRenderService());
        $container->set(AuthContextService::class, static fn(Container $c) => new AuthContextService());
        $container->set(StudentImportService::class, static fn(Container $c) => new StudentImportService());
        $container->set(StaffImportService::class, static fn(Container $c) => new StaffImportService());
        $container->set(CredentialExportService::class, static fn(Container $c) => new CredentialExportService());
        $container->set(AdminLogViewerService::class, static fn(Container $c) => new AdminLogViewerService(DatabaseManager::getConnection()));

        $container->set(AdminApiService::class, static fn(Container $c) => new AdminApiService(
            $c->get(StudentImportService::class),
            $c->get(StaffImportService::class),
            $c->get(CredentialExportService::class),
            $c->get(AdminLogViewerService::class),
            $c->get(AuthContextService::class),
        ));
        $container->set(AdminApiController::class, static fn(Container $c) => new AdminApiController($c->get(AdminApiService::class)));

        $container->set(AdminWebService::class, static fn(Container $c) => new AdminWebService($c->get(TemplateRenderService::class)));
        $container->set(AdminWebController::class, static fn(Container $c) => new AdminWebController($c->get(AdminWebService::class)));
    }

    public function boot(): void
    {
    }
}

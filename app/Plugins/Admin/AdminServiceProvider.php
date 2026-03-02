<?php

declare(strict_types=1);

namespace App\Plugins\Admin;

use App\Core\Container\Container;
use App\Core\Contracts\PluginServiceProviderInterface;
use App\Plugins\Admin\Controllers\AdminApiController;
use App\Plugins\Admin\Service\AdminApiService;

final class AdminServiceProvider implements PluginServiceProviderInterface
{
    public function register(): void
    {
        $container = Container::getInstance();

        $container->set(AdminApiService::class, static fn(Container $c) => new AdminApiService());
        $container->set(AdminApiController::class, static fn(Container $c) => new AdminApiController($c->get(AdminApiService::class)));
    }

    public function boot(): void
    {
    }
}

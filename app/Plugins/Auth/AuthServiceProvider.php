<?php

declare(strict_types=1);

namespace App\Plugins\Auth;

use App\Core\Container\Container;
use App\Core\Contracts\PluginServiceProviderInterface;
use App\Plugins\Auth\Controllers\AuthWebController;
use App\Plugins\Auth\Service\AuthWebService;
use App\Plugins\Support\Service\TemplateRenderService;

final class AuthServiceProvider implements PluginServiceProviderInterface
{
    public function register(): void
    {
        $container = Container::getInstance();

        $container->set(TemplateRenderService::class, static fn(Container $c) => new TemplateRenderService());
        $container->set(AuthWebService::class, static fn(Container $c) => new AuthWebService($c->get(TemplateRenderService::class)));
        $container->set(AuthWebController::class, static fn(Container $c) => new AuthWebController($c->get(AuthWebService::class)));
    }

    public function boot(): void
    {
    }
}

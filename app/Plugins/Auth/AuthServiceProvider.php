<?php

declare(strict_types=1);

namespace App\Plugins\Auth;

use App\Core\Container\Container;
use App\Core\Contracts\PluginServiceProviderInterface;
use App\Http\Controllers\Web\PageController;

final class AuthServiceProvider implements PluginServiceProviderInterface
{
    public function register(): void
    {
        $container = Container::getInstance();
        $container->set(PageController::class, static fn(Container $c) => new PageController());
    }

    public function boot(): void
    {
    }
}

<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Core\Container\Container;
use App\Core\Routing\PluginRouteRegistrar;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use PHPUnit\Framework\TestCase;
use function FastRoute\simpleDispatcher;

final class PluginRoutingDispatchTest extends TestCase
{
    public function testPluginWebRouteDispatchesWhenEnabled(): void
    {
        $_ENV['APP_PLUGIN_ROUTING'] = 'true';

        $dispatcher = simpleDispatcher(function (RouteCollector $r): void {
            if ((($_ENV['APP_PLUGIN_ROUTING'] ?? 'true') === 'true')) {
                PluginRouteRegistrar::register($r, 'web');
            }
        });

        $routeInfo = $dispatcher->dispatch('GET', '/login');
        $this->assertSame(Dispatcher::FOUND, $routeInfo[0]);
    }

    public function testPluginWebRoutesNotRegisteredWhenDisabled(): void
    {
        $_ENV['APP_PLUGIN_ROUTING'] = 'false';

        $dispatcher = simpleDispatcher(function (RouteCollector $r): void {
            if ((($_ENV['APP_PLUGIN_ROUTING'] ?? 'true') === 'true')) {
                PluginRouteRegistrar::register($r, 'web');
            }
        });

        $routeInfo = $dispatcher->dispatch('GET', '/login');
        $this->assertSame(Dispatcher::NOT_FOUND, $routeInfo[0]);
    }

    public function testContainerResolvesControllerForRouteHandlerArray(): void
    {
        $container = Container::getInstance();
        $container->set(TestRouteService::class, static fn(Container $c) => new TestRouteService());
        $container->set(TestRouteController::class, static fn(Container $c) => new TestRouteController($c->get(TestRouteService::class)));

        $dispatcher = simpleDispatcher(function (RouteCollector $r): void {
            $r->get('/_test/container', [TestRouteController::class, 'show']);
        });

        $routeInfo = $dispatcher->dispatch('GET', '/_test/container');
        $this->assertSame(Dispatcher::FOUND, $routeInfo[0]);

        [$controller, $method] = $routeInfo[1];
        $instance = Container::getInstance()->get($controller);

        $this->assertSame('ok', $instance->{$method}());
    }

    public function testPluginApiRouteDispatchesWhenEnabled(): void
    {
        $_ENV['APP_PLUGIN_ROUTING'] = 'true';

        $dispatcher = simpleDispatcher(function (RouteCollector $r): void {
            if ((($_ENV['APP_PLUGIN_ROUTING'] ?? 'true') === 'true')) {
                PluginRouteRegistrar::register($r, 'api');
            }
        });

        $routeInfo = $dispatcher->dispatch('POST', '/api/proctoring/event');
        $this->assertSame(Dispatcher::FOUND, $routeInfo[0]);
    }
}

final class TestRouteService
{
    public function message(): string
    {
        return 'ok';
    }
}

final class TestRouteController
{
    public function __construct(private readonly TestRouteService $service)
    {
    }

    public function show(): string
    {
        return $this->service->message();
    }
}

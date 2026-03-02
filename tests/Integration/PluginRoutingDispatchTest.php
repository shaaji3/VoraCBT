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



    public function testCorePersonaWebRoutesDispatchWhenEnabled(): void
    {
        $_ENV['APP_PLUGIN_ROUTING'] = 'true';

        $dispatcher = simpleDispatcher(function (RouteCollector $r): void {
            if ((($_ENV['APP_PLUGIN_ROUTING'] ?? 'true') === 'true')) {
                PluginRouteRegistrar::register($r, 'web');
            }
        });

        $twoFaRoute = $dispatcher->dispatch('GET', '/login/2fa');
        $adminAnalyticsRoute = $dispatcher->dispatch('GET', '/admin/analytics');
        $adminRolesRoute = $dispatcher->dispatch('GET', '/admin/roles-permissions');
        $adminSettingsRoute = $dispatcher->dispatch('GET', '/admin/settings');
        $studentDashboardRoute = $dispatcher->dispatch('GET', '/student/dashboard');
        $studentResultsRoute = $dispatcher->dispatch('GET', '/student/results');
        $teacherQuestionsRoute = $dispatcher->dispatch('GET', '/teacher/questions');

        $this->assertSame(Dispatcher::FOUND, $twoFaRoute[0]);
        $this->assertSame(Dispatcher::FOUND, $adminAnalyticsRoute[0]);
        $this->assertSame(Dispatcher::FOUND, $adminRolesRoute[0]);
        $this->assertSame(Dispatcher::FOUND, $adminSettingsRoute[0]);
        $this->assertSame(Dispatcher::FOUND, $studentDashboardRoute[0]);
        $this->assertSame(Dispatcher::FOUND, $studentResultsRoute[0]);
        $this->assertSame(Dispatcher::FOUND, $teacherQuestionsRoute[0]);
    }

    public function testAdminExtendedWebRoutesDispatchWhenEnabled(): void
    {
        $_ENV['APP_PLUGIN_ROUTING'] = 'true';

        $dispatcher = simpleDispatcher(function (RouteCollector $r): void {
            if ((($_ENV['APP_PLUGIN_ROUTING'] ?? 'true') === 'true')) {
                PluginRouteRegistrar::register($r, 'web');
            }
        });

        $questionRoute = $dispatcher->dispatch('GET', '/admin/questions');
        $importRoute = $dispatcher->dispatch('GET', '/admin/questions/import');
        $gradingRoute = $dispatcher->dispatch('GET', '/admin/grading/manual');

        $this->assertSame(Dispatcher::FOUND, $questionRoute[0]);
        $this->assertSame(Dispatcher::FOUND, $importRoute[0]);
        $this->assertSame(Dispatcher::FOUND, $gradingRoute[0]);
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




    public function testStudentExamWebRoutesDispatchWhenEnabled(): void
    {
        $_ENV['APP_PLUGIN_ROUTING'] = 'true';

        $dispatcher = simpleDispatcher(function (RouteCollector $r): void {
            if ((($_ENV['APP_PLUGIN_ROUTING'] ?? 'true') === 'true')) {
                PluginRouteRegistrar::register($r, 'web');
            }
        });

        $legacyExamRoute = $dispatcher->dispatch('GET', '/student/exam');
        $sessionPathRoute = $dispatcher->dispatch('GET', '/student/exams/session-123');

        $this->assertSame(Dispatcher::FOUND, $legacyExamRoute[0]);
        $this->assertSame(Dispatcher::FOUND, $sessionPathRoute[0]);
    }


    public function testAdminExtendedApiRoutesDispatchWhenEnabled(): void
    {
        $_ENV['APP_PLUGIN_ROUTING'] = 'true';

        $dispatcher = simpleDispatcher(function (RouteCollector $r): void {
            if ((($_ENV['APP_PLUGIN_ROUTING'] ?? 'true') === 'true')) {
                PluginRouteRegistrar::register($r, 'api');
            }
        });

        $dashboardRoute = $dispatcher->dispatch('GET', '/api/admin/dashboard/overview');
        $analyticsRoute = $dispatcher->dispatch('GET', '/api/admin/analytics/summary');
        $rolesRoute = $dispatcher->dispatch('GET', '/api/admin/roles/summary');
        $questionsRoute = $dispatcher->dispatch('GET', '/api/admin/questions/summary');
        $gradingRoute = $dispatcher->dispatch('GET', '/api/admin/grading/pending');

        $this->assertSame(Dispatcher::FOUND, $dashboardRoute[0]);
        $this->assertSame(Dispatcher::FOUND, $analyticsRoute[0]);
        $this->assertSame(Dispatcher::FOUND, $rolesRoute[0]);
        $this->assertSame(Dispatcher::FOUND, $questionsRoute[0]);
        $this->assertSame(Dispatcher::FOUND, $gradingRoute[0]);
    }

    public function testTeacherApiRoutesDispatchWhenEnabled(): void
    {
        $_ENV['APP_PLUGIN_ROUTING'] = 'true';

        $dispatcher = simpleDispatcher(function (RouteCollector $r): void {
            if ((($_ENV['APP_PLUGIN_ROUTING'] ?? 'true') === 'true')) {
                PluginRouteRegistrar::register($r, 'api');
            }
        });

        $repoRoute = $dispatcher->dispatch('GET', '/api/teacher/questions/repository');
        $pendingRoute = $dispatcher->dispatch('GET', '/api/teacher/grading/pending');
        $answersRoute = $dispatcher->dispatch('GET', '/api/teacher/grading/session-123/answers');

        $this->assertSame(Dispatcher::FOUND, $repoRoute[0]);
        $this->assertSame(Dispatcher::FOUND, $pendingRoute[0]);
        $this->assertSame(Dispatcher::FOUND, $answersRoute[0]);
    }

    public function testStudentExamLifecycleRoutesDispatchWhenEnabled(): void
    {
        $_ENV['APP_PLUGIN_ROUTING'] = 'true';

        $dispatcher = simpleDispatcher(function (RouteCollector $r): void {
            if ((($_ENV['APP_PLUGIN_ROUTING'] ?? 'true') === 'true')) {
                PluginRouteRegistrar::register($r, 'api');
            }
        });

        $showRoute = $dispatcher->dispatch('GET', '/api/student/exams/session-123');
        $saveRoute = $dispatcher->dispatch('POST', '/api/student/exams/session-123/answers');
        $submitRoute = $dispatcher->dispatch('POST', '/api/student/exams/session-123/submit');
        $proctoringRoute = $dispatcher->dispatch('POST', '/api/exams/session-123/proctoring-events');

        $this->assertSame(Dispatcher::FOUND, $showRoute[0]);
        $this->assertSame(Dispatcher::FOUND, $saveRoute[0]);
        $this->assertSame(Dispatcher::FOUND, $submitRoute[0]);
        $this->assertSame(Dispatcher::FOUND, $proctoringRoute[0]);
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

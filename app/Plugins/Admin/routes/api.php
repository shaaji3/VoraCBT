<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\IdentityController;
use App\Http\Controllers\Admin\MonitoringController;
use App\Plugins\Support\RouteAuthorizer;
use FastRoute\RouteCollector;

return static function (RouteCollector $r): void {
    $authorizeAdmin = static function (callable $action): void {
        RouteAuthorizer::authorize(['admin', 'super_admin'], $action);
    };

    $r->addGroup('/api', function (RouteCollector $r) use ($authorizeAdmin): void {
        $r->post('/admin/students/import/preview', function () use ($authorizeAdmin): void {
            $authorizeAdmin(static function (): void {
                (new IdentityController())->preview();
            });
        });

        $r->post('/admin/students/import/commit', function () use ($authorizeAdmin): void {
            $authorizeAdmin(static function (): void {
                (new IdentityController())->commit();
            });
        });

        $r->get('/admin/students/credentials/export', function () use ($authorizeAdmin): void {
            $authorizeAdmin(static function (): void {
                (new IdentityController())->export();
            });
        });

        $r->get('/admin/logs', function () use ($authorizeAdmin): void {
            $authorizeAdmin(static function (): void {
                (new MonitoringController())->logs();
            });
        });
    });
};

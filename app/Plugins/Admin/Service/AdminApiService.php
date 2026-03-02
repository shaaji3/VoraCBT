<?php

declare(strict_types=1);

namespace App\Plugins\Admin\Service;

use App\Http\Controllers\Admin\IdentityController;
use App\Http\Controllers\Admin\MonitoringController;
use App\Plugins\Support\RouteAuthorizer;

final class AdminApiService
{
    public function previewImport(): void
    {
        RouteAuthorizer::authorize(['admin', 'super_admin'], static function (): void {
            (new IdentityController())->preview();
        });
    }

    public function commitImport(): void
    {
        RouteAuthorizer::authorize(['admin', 'super_admin'], static function (): void {
            (new IdentityController())->commit();
        });
    }

    public function exportCredentials(): void
    {
        RouteAuthorizer::authorize(['admin', 'super_admin'], static function (): void {
            (new IdentityController())->export();
        });
    }

    public function fetchLogs(): void
    {
        RouteAuthorizer::authorize(['admin', 'super_admin'], static function (): void {
            (new MonitoringController())->logs();
        });
    }
}

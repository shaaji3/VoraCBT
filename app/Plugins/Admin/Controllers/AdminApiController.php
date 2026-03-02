<?php

declare(strict_types=1);

namespace App\Plugins\Admin\Controllers;

use App\Plugins\Admin\Service\AdminApiService;

final class AdminApiController
{
    public function __construct(private readonly AdminApiService $service)
    {
    }

    public function previewImport(): void
    {
        $this->service->previewImport();
    }

    public function commitImport(): void
    {
        $this->service->commitImport();
    }

    public function exportCredentials(): void
    {
        $this->service->exportCredentials();
    }

    public function logs(): void
    {
        $this->service->fetchLogs();
    }
}

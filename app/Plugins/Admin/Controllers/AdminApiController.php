<?php

declare(strict_types=1);

namespace App\Plugins\Admin\Controllers;

use App\Plugins\Admin\Service\AdminApiService;

final class AdminApiController
{
    public function __construct(private readonly ?AdminApiService $service = null)
    {
    }

    public function previewImport(): void
    {
        ($this->service ?? new AdminApiService())->previewImport();
    }

    public function commitImport(): void
    {
        ($this->service ?? new AdminApiService())->commitImport();
    }

    public function exportCredentials(): void
    {
        ($this->service ?? new AdminApiService())->exportCredentials();
    }

    public function logs(): void
    {
        ($this->service ?? new AdminApiService())->fetchLogs();
    }
}

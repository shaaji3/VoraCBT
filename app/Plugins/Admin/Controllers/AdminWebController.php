<?php

declare(strict_types=1);

namespace App\Plugins\Admin\Controllers;

use App\Plugins\Admin\Service\AdminWebService;

final class AdminWebController
{
    public function __construct(private readonly AdminWebService $service)
    {
    }

    public function dashboard(): void { $this->service->dashboard(); }
    public function createExam(): void { $this->service->createExam(); }
    public function analytics(): void { $this->service->analytics(); }
    public function rolesPermissions(): void { $this->service->rolesPermissions(); }
    public function settings(): void { $this->service->settings(); }
    public function questionRepository(): void { $this->service->questionRepository(); }
    public function bulkUpload(): void { $this->service->bulkUpload(); }
    public function manualGrading(): void { $this->service->manualGrading(); }
}

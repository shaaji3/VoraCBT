<?php

declare(strict_types=1);

namespace App\Plugins\Admin\Service;

use App\Plugins\Support\Service\TemplateRenderService;

final class AdminWebService
{
    public function __construct(private readonly TemplateRenderService $renderer)
    {
    }

    public function dashboard(): void { $this->renderer->render('admin/dashboard.php'); }
    public function createExam(): void { $this->renderer->render('admin/exams/create.php'); }
    public function analytics(): void { $this->renderer->render('admin/analytics.php'); }
    public function rolesPermissions(): void { $this->renderer->render('admin/roles-permissions.php'); }
    public function settings(): void { $this->renderer->render('admin/settings.php'); }
    public function questionRepository(): void { $this->renderer->render('admin/questions/index.php'); }
    public function bulkUpload(): void { $this->renderer->render('admin/questions/import.php'); }
    public function manualGrading(): void { $this->renderer->render('admin/grading/manual.php'); }
}

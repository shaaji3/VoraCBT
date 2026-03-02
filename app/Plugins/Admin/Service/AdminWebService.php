<?php

declare(strict_types=1);

namespace App\Plugins\Admin\Service;

use App\Plugins\Support\Service\TemplateRenderService;

final class AdminWebService
{
    public function __construct(private readonly TemplateRenderService $renderer)
    {
    }

    public function dashboard(): void { $this->renderer->render('pages/admin-dashboard.html'); }
    public function createExam(): void { $this->renderer->render('pages/exam-template.html'); }
    public function analytics(): void { $this->renderer->render('pages/exam-analytics.html'); }
    public function rolesPermissions(): void { $this->renderer->render('pages/roles-permissions.html'); }
    public function settings(): void { $this->renderer->render('pages/system-settings.html'); }
}

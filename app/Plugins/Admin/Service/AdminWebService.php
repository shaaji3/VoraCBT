<?php

declare(strict_types=1);

namespace App\Plugins\Admin\Service;

use App\Plugins\Support\Service\TemplateRenderService;

final class AdminWebService
{
    public function __construct(private readonly ?TemplateRenderService $renderer = null)
    {
    }

    public function dashboard(): void
    {
        ($this->renderer ?? new TemplateRenderService())->render('pages/admin-dashboard.html');
    }

    public function createExam(): void
    {
        ($this->renderer ?? new TemplateRenderService())->render('pages/exam-template.html');
    }
}

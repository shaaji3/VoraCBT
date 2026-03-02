<?php

declare(strict_types=1);

namespace App\Plugins\Student\Service;

use App\Plugins\Support\Service\TemplateRenderService;

final class StudentWebService
{
    public function __construct(private readonly TemplateRenderService $renderer)
    {
    }

    public function dashboard(): void
    {
        $this->renderer->render('pages/exam-selection.html');
    }

    public function exam(): void
    {
        $this->renderer->render('pages/active-exam.html');
    }

    public function results(): void
    {
        $this->renderer->render('pages/student-results.html');
    }
}

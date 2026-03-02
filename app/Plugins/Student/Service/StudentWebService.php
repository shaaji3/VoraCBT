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

    public function exam(?string $sessionId = null): void
    {
        // Template currently bootstraps runtime client-side; sessionId is read from URL path/query by JS.
        $this->renderer->render('pages/active-exam.html');
    }

    public function results(): void
    {
        $this->renderer->render('pages/student-results.html');
    }
}

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
        $this->renderer->render('student/dashboard.php');
    }

    public function exam(?string $sessionId = null): void
    {
        // Runtime page bootstraps exam state client-side; sessionId is read from URL path/query by JS.
        $this->renderer->render('student/exam.php');
    }

    public function results(): void
    {
        $this->renderer->render('student/results.php');
    }
}

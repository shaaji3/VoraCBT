<?php

declare(strict_types=1);

namespace App\Plugins\Teacher\Service;

use App\Plugins\Support\Service\TemplateRenderService;

final class TeacherWebService
{
    public function __construct(private readonly TemplateRenderService $renderer)
    {
    }

    public function questionRepository(): void { $this->renderer->render('pages/question-repository.html'); }
    public function createMcq(): void { $this->renderer->render('pages/create-mcq.html'); }
    public function createFillBlank(): void { $this->renderer->render('pages/create-fill-blank.html'); }
    public function createMatching(): void { $this->renderer->render('pages/create-matching.html'); }
    public function createPassage(): void { $this->renderer->render('pages/create-passage.html'); }
    public function bulkUpload(): void { $this->renderer->render('pages/bulk-upload.html'); }
    public function manualGrading(): void { $this->renderer->render('pages/manual-grading.html'); }
}

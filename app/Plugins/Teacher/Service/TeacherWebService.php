<?php

declare(strict_types=1);

namespace App\Plugins\Teacher\Service;

use App\Plugins\Support\Service\TemplateRenderService;

final class TeacherWebService
{
    public function __construct(private readonly TemplateRenderService $renderer)
    {
    }

    public function questionRepository(): void { $this->renderer->render('teacher/questions/index.php'); }
    public function createMcq(): void { $this->renderer->render('teacher/questions/create-mcq.php'); }
    public function createFillBlank(): void { $this->renderer->render('teacher/questions/create-fill-blank.php'); }
    public function createMatching(): void { $this->renderer->render('teacher/questions/create-matching.php'); }
    public function createPassage(): void { $this->renderer->render('teacher/questions/create-passage.php'); }
    public function bulkUpload(): void { $this->renderer->render('teacher/questions/import.php'); }
    public function manualGrading(): void { $this->renderer->render('teacher/grading/manual.php'); }
}

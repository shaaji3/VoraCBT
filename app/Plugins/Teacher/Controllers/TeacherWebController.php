<?php

declare(strict_types=1);

namespace App\Plugins\Teacher\Controllers;

use App\Plugins\Teacher\Service\TeacherWebService;

final class TeacherWebController
{
    public function __construct(private readonly TeacherWebService $service)
    {
    }

    public function questionRepository(): void { $this->service->questionRepository(); }
    public function createMcq(): void { $this->service->createMcq(); }
    public function createFillBlank(): void { $this->service->createFillBlank(); }
    public function createTrueFalse(): void { $this->service->createTrueFalse(); }
    public function createDragDrop(): void { $this->service->createDragDrop(); }
    public function createCaseStudy(): void { $this->service->createCaseStudy(); }
    public function createMatching(): void { $this->service->createMatching(); }
    public function createPassage(): void { $this->service->createPassage(); }
    public function bulkUpload(): void { $this->service->bulkUpload(); }
    public function manualGrading(): void { $this->service->manualGrading(); }
}

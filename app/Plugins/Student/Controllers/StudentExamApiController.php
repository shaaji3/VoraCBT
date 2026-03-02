<?php

declare(strict_types=1);

namespace App\Plugins\Student\Controllers;

use App\Plugins\Student\Service\StudentExamApiService;

final class StudentExamApiController
{
    public function __construct(private readonly ?StudentExamApiService $service = null)
    {
    }

    public function overview(): void
    {
        ($this->service ?? new StudentExamApiService())->overview();
    }

    public function proctoringEvent(): void
    {
        ($this->service ?? new StudentExamApiService())->proctoringEvent();
    }

    public function proctoringHeartbeat(): void
    {
        ($this->service ?? new StudentExamApiService())->proctoringHeartbeat();
    }

    public function autosave(): void
    {
        ($this->service ?? new StudentExamApiService())->autosave();
    }

    public function resumeState(): void
    {
        ($this->service ?? new StudentExamApiService())->resumeState();
    }
}

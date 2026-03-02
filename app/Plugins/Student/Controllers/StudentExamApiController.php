<?php

declare(strict_types=1);

namespace App\Plugins\Student\Controllers;

use App\Plugins\Student\Service\StudentExamApiService;

final class StudentExamApiController
{
    public function __construct(private readonly StudentExamApiService $service)
    {
    }

    public function overview(): void
    {
        $this->service->overview();
    }

    public function proctoringEvent(): void
    {
        $this->service->proctoringEvent();
    }

    public function proctoringHeartbeat(): void
    {
        $this->service->proctoringHeartbeat();
    }

    public function autosave(): void
    {
        $this->service->autosave();
    }

    public function resumeState(): void
    {
        $this->service->resumeState();
    }
}

<?php

declare(strict_types=1);

namespace App\Plugins\Teacher\Controllers;

use App\Plugins\Teacher\Service\TeacherApiService;

final class TeacherApiController
{
    public function __construct(private readonly TeacherApiService $service)
    {
    }

    public function questionRepository(): void
    {
        $this->service->questionRepository();
    }

    public function pendingManualGrading(): void
    {
        $this->service->pendingManualGrading();
    }

    public function ungradedAnswers(string $sessionId): void
    {
        $this->service->ungradedAnswers($sessionId);
    }
}

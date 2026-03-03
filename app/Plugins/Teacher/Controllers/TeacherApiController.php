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

    public function showQuestion(string $id): void
    {
        $this->service->showQuestion($id);
    }

    public function createQuestion(): void
    {
        $this->service->createQuestion();
    }

    public function updateQuestion(string $id): void
    {
        $this->service->updateQuestion($id);
    }

    public function deleteQuestion(string $id): void
    {
        $this->service->deleteQuestion($id);
    }

    public function previewQuestionImport(): void
    {
        $this->service->previewQuestionImport();
    }

    public function commitQuestionImport(): void
    {
        $this->service->commitQuestionImport();
    }

    public function pendingManualGrading(): void
    {
        $this->service->pendingManualGrading();
    }

    public function ungradedAnswers(string $sessionId): void
    {
        $this->service->ungradedAnswers($sessionId);
    }

    public function scoreAnswer(string $answerId): void
    {
        $this->service->scoreAnswer($answerId);
    }
}

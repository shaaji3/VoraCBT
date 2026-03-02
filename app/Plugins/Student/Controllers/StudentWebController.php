<?php

declare(strict_types=1);

namespace App\Plugins\Student\Controllers;

use App\Plugins\Student\Service\StudentWebService;

final class StudentWebController
{
    public function __construct(private readonly StudentWebService $service)
    {
    }

    public function dashboard(): void
    {
        $this->service->dashboard();
    }

    public function exam(?string $sessionId = null): void
    {
        $this->service->exam($sessionId);
    }

    public function results(): void
    {
        $this->service->results();
    }
}

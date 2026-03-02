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

    public function exam(): void
    {
        $this->service->exam();
    }

    public function results(): void
    {
        $this->service->results();
    }
}

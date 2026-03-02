<?php

declare(strict_types=1);

namespace App\Plugins\Admin\Controllers;

use App\Plugins\Admin\Service\AdminWebService;

final class AdminWebController
{
    public function __construct(private readonly AdminWebService $service)
    {
    }

    public function dashboard(): void
    {
        $this->service->dashboard();
    }

    public function createExam(): void
    {
        $this->service->createExam();
    }
}

<?php

declare(strict_types=1);

namespace App\Plugins\Admin\Controllers;

use App\Plugins\Admin\Service\AdminWebService;

final class AdminWebController
{
    public function __construct(private readonly ?AdminWebService $service = null)
    {
    }

    public function dashboard(): void
    {
        ($this->service ?? new AdminWebService())->dashboard();
    }

    public function createExam(): void
    {
        ($this->service ?? new AdminWebService())->createExam();
    }
}

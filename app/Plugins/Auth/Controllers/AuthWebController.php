<?php

declare(strict_types=1);

namespace App\Plugins\Auth\Controllers;

use App\Plugins\Auth\Service\AuthWebService;

final class AuthWebController
{
    public function __construct(private readonly AuthWebService $service)
    {
    }

    public function home(): void
    {
        $this->service->home();
    }

    public function login(): void
    {
        $this->service->login();
    }

    public function twoFactor(): void
    {
        $this->service->twoFactor();
    }

    public function forgotPassword(): void
    {
        $this->service->forgotPassword();
    }

    public function contactSupport(): void
    {
        $this->service->contactSupport();
    }

    public function privacy(): void
    {
        $this->service->privacy();
    }

    public function terms(): void
    {
        $this->service->terms();
    }
}


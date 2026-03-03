<?php

declare(strict_types=1);

namespace App\Plugins\Auth\Service;

use App\Plugins\Support\Service\TemplateRenderService;

final class AuthWebService
{
    public function __construct(private readonly TemplateRenderService $renderer)
    {
    }

    public function home(): void
    {
        $this->renderer->render('auth/login.php');
    }

    public function login(): void
    {
        $this->renderer->render('auth/login.php');
    }

    public function twoFactor(): void
    {
        $this->renderer->render('auth/2fa.php');
    }
}

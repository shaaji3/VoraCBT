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
        $this->renderer->render('index.html');
    }

    public function login(): void
    {
        $this->renderer->render('index.html');
    }

    public function twoFactor(): void
    {
        $this->renderer->render('pages/2fa.html');
    }
}

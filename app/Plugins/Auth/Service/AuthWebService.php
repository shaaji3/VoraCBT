<?php

declare(strict_types=1);

namespace App\Plugins\Auth\Service;

use App\Plugins\Support\Service\TemplateRenderService;

final class AuthWebService
{
    public function __construct(private readonly ?TemplateRenderService $renderer = null)
    {
    }

    public function home(): void
    {
        ($this->renderer ?? new TemplateRenderService())->render('index.html');
    }

    public function login(): void
    {
        ($this->renderer ?? new TemplateRenderService())->render('index.html');
    }
}

<?php

declare(strict_types=1);

namespace App\Plugins\Auth\Controllers;

use App\Plugins\Auth\Service\AuthWebService;

final class AuthWebController
{
    public function __construct(private readonly ?AuthWebService $service = null)
    {
    }

    public function home(): void
    {
        ($this->service ?? new AuthWebService())->home();
    }

    public function login(): void
    {
        ($this->service ?? new AuthWebService())->login();
    }
}

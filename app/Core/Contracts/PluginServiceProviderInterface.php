<?php

declare(strict_types=1);

namespace App\Core\Contracts;

interface PluginServiceProviderInterface
{
    public function register(): void;

    public function boot(): void;
}

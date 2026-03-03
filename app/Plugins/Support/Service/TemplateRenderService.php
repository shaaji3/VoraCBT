<?php

declare(strict_types=1);

namespace App\Plugins\Support\Service;

final class TemplateRenderService
{
    private const VIEW_ROOT = __DIR__ . '/../../../../resources/views';

    public function render(string $template): void
    {
        $file = self::VIEW_ROOT . '/' . ltrim($template, '/');

        if (!is_file($file)) {
            http_response_code(404);
            echo 'View not found.';
            return;
        }

        require $file;
    }
}

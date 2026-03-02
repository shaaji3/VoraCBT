<?php

declare(strict_types=1);

namespace App\Core\Plugins;

final class PluginRegistry
{
    /**
     * @return list<array{name:string,path:string,manifest:array<string,mixed>}>
     */
    public function enabled(?string $configPath = null): array
    {
        $pluginConfig = $configPath ?? __DIR__ . '/../../../config/plugins.php';
        if (!is_file($pluginConfig)) {
            return [];
        }

        $plugins = require $pluginConfig;
        if (!is_array($plugins)) {
            return [];
        }

        $resolved = [];

        foreach ($plugins as $plugin) {
            if (($plugin['enabled'] ?? false) !== true) {
                continue;
            }

            $path = rtrim((string) ($plugin['path'] ?? ''), '/');
            $name = (string) ($plugin['name'] ?? '');
            if ($path === '' || $name === '') {
                continue;
            }

            $manifestPath = $path . '/manifest.json';
            if (!is_file($manifestPath)) {
                continue;
            }

            $manifest = json_decode((string) file_get_contents($manifestPath), true);
            if (!is_array($manifest)) {
                continue;
            }

            $resolved[] = [
                'name' => $name,
                'path' => $path,
                'manifest' => $manifest,
            ];
        }

        return $resolved;
    }
}

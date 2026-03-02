<?php

declare(strict_types=1);

namespace App\Core\Plugins;

use App\Core\Contracts\PluginServiceProviderInterface;

final class PluginManager
{
    /** @var list<PluginServiceProviderInterface> */
    private array $providers = [];

    public function __construct(private readonly ?PluginRegistry $registry = null)
    {
    }

    public function loadAndBoot(?string $configPath = null): void
    {
        $registry = $this->registry ?? new PluginRegistry();

        foreach ($registry->enabled($configPath) as $plugin) {
            $providers = $plugin['manifest']['providers'] ?? [];
            if (!is_array($providers)) {
                continue;
            }

            foreach ($providers as $providerClass) {
                if (!is_string($providerClass) || !class_exists($providerClass)) {
                    continue;
                }

                $instance = new $providerClass();
                if (!$instance instanceof PluginServiceProviderInterface) {
                    continue;
                }

                $instance->register();
                $this->providers[] = $instance;
            }
        }

        foreach ($this->providers as $provider) {
            $provider->boot();
        }
    }
}

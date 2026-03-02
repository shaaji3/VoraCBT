<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Plugins;

use App\Core\Contracts\PluginServiceProviderInterface;
use App\Core\Plugins\PluginManager;
use PHPUnit\Framework\TestCase;

final class PluginManagerTest extends TestCase
{
    private string $tmpDir;

    protected function setUp(): void
    {
        parent::setUp();
        DummyPluginProvider::reset();

        $this->tmpDir = sys_get_temp_dir() . '/voracbt-plugin-manager-' . uniqid('', true);
        mkdir($this->tmpDir, 0777, true);
    }

    protected function tearDown(): void
    {
        $this->deleteDir($this->tmpDir);
        parent::tearDown();
    }

    public function testLoadAndBootCallsRegisterThenBootForProviders(): void
    {
        $pluginPath = $this->tmpDir . '/DummyPlugin';
        mkdir($pluginPath, 0777, true);

        file_put_contents($pluginPath . '/manifest.json', json_encode([
            'providers' => [DummyPluginProvider::class],
        ]));

        $configPath = $this->tmpDir . '/plugins.php';
        file_put_contents($configPath, "<?php\nreturn " . var_export([
            ['name' => 'DummyPlugin', 'path' => $pluginPath, 'enabled' => true],
        ], true) . ';');

        (new PluginManager())->loadAndBoot($configPath);

        $this->assertSame(1, DummyPluginProvider::$registerCalls);
        $this->assertSame(1, DummyPluginProvider::$bootCalls);
    }

    private function deleteDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = scandir($dir);
        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir . '/' . $item;
            if (is_dir($path)) {
                $this->deleteDir($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }
}

final class DummyPluginProvider implements PluginServiceProviderInterface
{
    public static int $registerCalls = 0;
    public static int $bootCalls = 0;

    public static function reset(): void
    {
        self::$registerCalls = 0;
        self::$bootCalls = 0;
    }

    public function register(): void
    {
        self::$registerCalls++;
    }

    public function boot(): void
    {
        self::$bootCalls++;
    }
}

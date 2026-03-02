<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Plugins;

use App\Core\Plugins\PluginRegistry;
use PHPUnit\Framework\TestCase;

final class PluginRegistryTest extends TestCase
{
    private string $tmpDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tmpDir = sys_get_temp_dir() . '/voracbt-plugin-registry-' . uniqid('', true);
        mkdir($this->tmpDir, 0777, true);
    }

    protected function tearDown(): void
    {
        $this->deleteDir($this->tmpDir);
        parent::tearDown();
    }

    public function testEnabledReturnsOnlyValidEnabledPlugins(): void
    {
        $validPath = $this->tmpDir . '/ValidPlugin';
        $disabledPath = $this->tmpDir . '/DisabledPlugin';
        $invalidPath = $this->tmpDir . '/InvalidPlugin';

        mkdir($validPath, 0777, true);
        mkdir($disabledPath, 0777, true);
        mkdir($invalidPath, 0777, true);

        file_put_contents($validPath . '/manifest.json', json_encode(['providers' => []]));
        file_put_contents($disabledPath . '/manifest.json', json_encode(['providers' => []]));
        file_put_contents($invalidPath . '/manifest.json', '{bad-json');

        $configPath = $this->tmpDir . '/plugins.php';
        file_put_contents($configPath, "<?php\nreturn " . var_export([
            ['name' => 'ValidPlugin', 'path' => $validPath, 'enabled' => true],
            ['name' => 'DisabledPlugin', 'path' => $disabledPath, 'enabled' => false],
            ['name' => 'InvalidPlugin', 'path' => $invalidPath, 'enabled' => true],
        ], true) . ';');

        $result = (new PluginRegistry())->enabled($configPath);

        $this->assertCount(1, $result);
        $this->assertSame('ValidPlugin', $result[0]['name']);
        $this->assertSame($validPath, $result[0]['path']);
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

<?php

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;

final class FrontControllerFlowTest extends TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testLoginRouteRendersThroughFrontControllerWithPluginRoutingEnabled(): void
    {
        if (!is_file(__DIR__ . '/../../vendor/autoload.php')) {
            $this->markTestSkipped('Composer dependencies are required to execute front controller integration tests.');
        }

        $_ENV['APP_ENV'] = 'local';
        $_ENV['APP_DEBUG'] = 'false';
        $_ENV['APP_PLUGIN_ROUTING'] = 'true';
        $_ENV['APP_PLUGIN_ROUTING_FALLBACK'] = 'false';
        $_ENV['JWT_SECRET'] = 'test-secret';

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/login';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        ob_start();
        require __DIR__ . '/../../public/index.php';
        $output = (string) ob_get_clean();

        $this->assertStringContainsString('Enterprise Portal Login', $output);
    }

    /**
     * @runInSeparateProcess
     */
    public function testHealthRouteReturnsJsonThroughFrontController(): void
    {
        if (!is_file(__DIR__ . '/../../vendor/autoload.php')) {
            $this->markTestSkipped('Composer dependencies are required to execute front controller integration tests.');
        }

        $_ENV['APP_ENV'] = 'local';
        $_ENV['APP_DEBUG'] = 'false';
        $_ENV['APP_PLUGIN_ROUTING'] = 'true';
        $_ENV['JWT_SECRET'] = 'test-secret';

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/health';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        ob_start();
        require __DIR__ . '/../../public/index.php';
        $output = (string) ob_get_clean();

        $decoded = json_decode($output, true);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('status', $decoded);
        $this->assertArrayHasKey('checks', $decoded);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Service;

use App\Core\Service\HealthCheckService;
use PHPUnit\Framework\TestCase;

final class HealthCheckServiceTest extends TestCase
{
    public function testEvaluateReturnsStructuredPayload(): void
    {
        $_ENV['CACHE_DRIVER'] = 'file';

        $result = (new HealthCheckService())->evaluate();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('code', $result);
        $this->assertArrayHasKey('payload', $result);
        $this->assertIsInt($result['code']);

        $payload = $result['payload'];
        $this->assertIsArray($payload);
        $this->assertArrayHasKey('status', $payload);
        $this->assertArrayHasKey('timestamp', $payload);
        $this->assertArrayHasKey('checks', $payload);

        $this->assertContains($payload['status'], ['ok', 'degraded', 'down']);
        $this->assertArrayHasKey('db', $payload['checks']);
        $this->assertArrayHasKey('cache', $payload['checks']);
        $this->assertArrayHasKey('queue', $payload['checks']);
        $this->assertArrayHasKey('storage', $payload['checks']);
    }
}

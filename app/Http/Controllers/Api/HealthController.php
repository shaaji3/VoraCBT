<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Core\Service\HealthCheckService;

final class HealthController
{
    public function __construct(private readonly ?HealthCheckService $service = null)
    {
    }

    public function show(): void
    {
        header('Content-Type: application/json');

        $result = ($this->service ?? new HealthCheckService())->evaluate();
        http_response_code((int) $result['code']);
        echo json_encode($result['payload']);
    }
}

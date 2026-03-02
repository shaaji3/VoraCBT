<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Web\PageController;
use FastRoute\RouteCollector;

return static function (RouteCollector $r): void {
    $r->get('/', [PageController::class, 'home']);
    $r->get('/login', [PageController::class, 'login']);

    $r->post('/auth/login', [AuthController::class, 'login']);
    $r->post('/auth/logout', [AuthController::class, 'logout']);
};

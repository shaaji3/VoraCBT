<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AuthController;
use App\Plugins\Auth\Controllers\AuthWebController;
use FastRoute\RouteCollector;

return static function (RouteCollector $r): void {
    $r->get('/', [AuthWebController::class, 'home']);
    $r->get('/login', [AuthWebController::class, 'login']);
    $r->get('/login/2fa', [AuthWebController::class, 'twoFactor']);
    $r->get('/forgot-password', [AuthWebController::class, 'forgotPassword']);
    $r->get('/contact-support', [AuthWebController::class, 'contactSupport']);
    $r->get('/privacy', [AuthWebController::class, 'privacy']);
    $r->get('/terms', [AuthWebController::class, 'terms']);

    $r->post('/auth/login', [AuthController::class, 'login']);
    $r->post('/auth/logout', [AuthController::class, 'logout']);
    $r->post('/auth/2fa/verify', [AuthController::class, 'verifyTwoFactor']);
    $r->post('/auth/2fa/resend', [AuthController::class, 'resendTwoFactor']);
};

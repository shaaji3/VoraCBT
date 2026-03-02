<?php

declare(strict_types=1);

namespace App\Plugins\Support;

use App\Core\Http\ApiResponse;
use App\Core\Http\Response;
use App\Http\Middleware\AuthMiddleware;
use App\Http\Middleware\RoleMiddleware;
use Throwable;

final class RouteAuthorizer
{
    public static function authorize(array $roles, callable $action): void
    {
        $request = [
            'headers' => function_exists('getallheaders') ? getallheaders() : [],
        ];

        try {
            $authResult = (new AuthMiddleware())->handle($request, static function (array $authenticatedRequest) use ($roles) {
                return (new RoleMiddleware($roles))->handle($authenticatedRequest, static fn(array $authorizedRequest) => $authorizedRequest);
            });
        } catch (Throwable $e) {
            ApiResponse::error('Unauthorized', 401)->send();
            return;
        }

        if ($authResult instanceof Response) {
            $authResult->send();
            return;
        }

        $action();
    }
}

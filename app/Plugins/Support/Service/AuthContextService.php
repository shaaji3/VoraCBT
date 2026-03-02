<?php

declare(strict_types=1);

namespace App\Plugins\Support\Service;

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

final class AuthContextService
{
    public function currentUserId(): ?string
    {
        $jwtSecret = $_ENV['JWT_SECRET'] ?? '';
        if ($jwtSecret === '') {
            return null;
        }

        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return null;
        }

        try {
            $decoded = JWT::decode($matches[1], new Key($jwtSecret, 'HS256'));
        } catch (Exception $e) {
            return null;
        }

        $id = $decoded->sub ?? $decoded->id ?? null;
        return is_string($id) ? $id : null;
    }
}

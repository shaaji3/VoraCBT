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
        $decoded = $this->decodeToken();
        if ($decoded === null) {
            return null;
        }

        $id = $decoded->sub ?? $decoded->id ?? null;
        return is_string($id) ? $id : null;
    }

    public function currentRole(): ?string
    {
        $decoded = $this->decodeToken();
        if ($decoded === null) {
            return null;
        }

        $role = $decoded->role ?? null;
        return is_string($role) ? $role : null;
    }

    private function decodeToken(): ?object
    {
        $jwtSecret = $_ENV['JWT_SECRET'] ?? '';
        if ($jwtSecret === '') {
            return null;
        }

        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

        $jwt = null;
        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $jwt = $matches[1];
        } elseif (isset($_COOKIE['auth_token']) && is_string($_COOKIE['auth_token']) && $_COOKIE['auth_token'] !== '') {
            $jwt = $_COOKIE['auth_token'];
        }

        if ($jwt === null) {
            return null;
        }

        try {
            return JWT::decode($jwt, new Key($jwtSecret, 'HS256'));
        } catch (Exception $e) {
            return null;
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Core\Http\ApiResponse;
use App\Core\Database\DatabaseManager;
use DateTimeImmutable;
use Firebase\JWT\JWT;
use Ramsey\Uuid\Uuid;

class AuthController
{
    private $db;

    public function __construct()
    {
        $this->db = DatabaseManager::getConnection();
    }

    public function login(): void
    {
        $rawBody = file_get_contents('php://input');
        $data = json_decode($rawBody, true) ?? [];

        $identity = $data['identity'] ?? '';
        $password = $data['password'] ?? '';

        if (empty($identity) || empty($password)) {
            ApiResponse::error('Email/username and password are required', 400)->send();
            return;
        }

        $user = $this->db->fetchAssociative(
            "SELECT * FROM users WHERE email = :identity OR username = :identity",
            ['identity' => $identity]
        );

        if (!$user) {
            ApiResponse::error('Invalid credentials', 401)->send();
            return;
        }

        if (!password_verify($password, $user['password_hash'])) {
            ApiResponse::error('Invalid credentials', 401)->send();
            return;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];

        $redirect = '/';
        if ($user['role'] === 'admin' || $user['role'] === 'super_admin') {
            $redirect = '/admin/dashboard';
        } elseif ($user['role'] === 'student') {
            $redirect = '/student/dashboard';
        } elseif ($user['role'] === 'teacher' || $user['role'] === 'staff') {
            $redirect = '/teacher/questions';
        }

        $requiresTwoFactor = in_array((string) ($user['role'] ?? ''), ['admin', 'super_admin', 'teacher', 'staff'], true);
        if ($requiresTwoFactor) {
            $challengeId = Uuid::uuid4()->toString();
            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $expiresAt = (new DateTimeImmutable('+5 minutes'))->getTimestamp();

            $_SESSION['2fa_challenge'] = [
                'id' => $challengeId,
                'user_id' => $user['id'],
                'code' => $code,
                'expires_at' => $expiresAt,
                'attempts' => 0,
                'last_resent_at' => time(),
                'delivery' => 'mock',
                'masked_destination' => $this->maskDestination((string) ($user['email'] ?? 'unknown')),
            ];
            $_SESSION['2fa_verified'] = false;

            ApiResponse::json([
                'message' => '2FA challenge created',
                'requires_2fa' => true,
                'challenge_id' => $challengeId,
                'redirect' => '/login/2fa?challenge=' . urlencode($challengeId),
                'expires_in_seconds' => 300,
                'delivery' => 'mock',
                'masked_destination' => $_SESSION['2fa_challenge']['masked_destination'],
            ])->send();
            return;
        }

        $jwtSecret = $_ENV['JWT_SECRET'] ?? '';
        if ($jwtSecret === '') {
            ApiResponse::error('JWT secret is not configured', 500)->send();
            return;
        }

        $now = new DateTimeImmutable();
        $expiresAt = $now->modify('+8 hours');

        $token = JWT::encode([
            'sub' => $user['id'],
            'id' => $user['id'],
            'role' => $user['role'],
            'iat' => $now->getTimestamp(),
            'exp' => $expiresAt->getTimestamp(),
        ], $jwtSecret, 'HS256');

        $this->issueAuthCookie($token, $expiresAt);

        ApiResponse::json([
            'message' => 'Login successful',
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_at' => $expiresAt->format(DATE_ATOM),
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'role' => $user['role']
            ],
            'redirect' => $redirect
        ])->send();
    }

    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        session_destroy();
        $this->clearAuthCookie();

        ApiResponse::json(['message' => 'Logged out successfully'])->send();
    }


    public function verifyTwoFactor(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $rawBody = file_get_contents('php://input');
        $data = json_decode($rawBody, true) ?? [];
        $code = (string) ($data['code'] ?? '');
        $challengeId = (string) ($data['challenge_id'] ?? '');

        $challenge = $_SESSION['2fa_challenge'] ?? null;
        if (!is_array($challenge) || $challengeId === '' || ($challenge['id'] ?? '') !== $challengeId) {
            ApiResponse::error('Invalid or expired challenge', 401)->send();
            return;
        }

        if ($code === '' || strlen($code) !== 6) {
            ApiResponse::error('Invalid verification code format', 422)->send();
            return;
        }

        if (time() > (int) ($challenge['expires_at'] ?? 0)) {
            ApiResponse::error('Verification code has expired', 401)->send();
            return;
        }

        $attempts = (int) ($challenge['attempts'] ?? 0);
        if ($attempts >= 5) {
            ApiResponse::error('Too many verification attempts', 429)->send();
            return;
        }

        if ($code !== (string) ($challenge['code'] ?? '')) {
            $challenge['attempts'] = $attempts + 1;
            $_SESSION['2fa_challenge'] = $challenge;
            ApiResponse::error('Incorrect verification code', 401)->send();
            return;
        }

        $_SESSION['2fa_verified'] = true;

        $user = $this->db->fetchAssociative('SELECT id, role, username FROM users WHERE id = ? LIMIT 1', [$_SESSION['user_id'] ?? null]);
        if (!$user) {
            ApiResponse::error('User not found', 404)->send();
            return;
        }

        $redirect = '/';
        if (($user['role'] ?? '') === 'admin' || ($user['role'] ?? '') === 'super_admin') {
            $redirect = '/admin/dashboard';
        } elseif (($user['role'] ?? '') === 'student') {
            $redirect = '/student/dashboard';
        } elseif (($user['role'] ?? '') === 'teacher' || ($user['role'] ?? '') === 'staff') {
            $redirect = '/teacher/questions';
        }

        $jwtSecret = $_ENV['JWT_SECRET'] ?? '';
        if ($jwtSecret === '') {
            ApiResponse::error('JWT secret is not configured', 500)->send();
            return;
        }

        $now = new DateTimeImmutable();
        $expiresAt = $now->modify('+8 hours');

        $token = JWT::encode([
            'sub' => $user['id'],
            'id' => $user['id'],
            'role' => $user['role'],
            'iat' => $now->getTimestamp(),
            'exp' => $expiresAt->getTimestamp(),
        ], $jwtSecret, 'HS256');

        $this->issueAuthCookie($token, $expiresAt);
        unset($_SESSION['2fa_challenge']);

        ApiResponse::json([
            'verified' => true,
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_at' => $expiresAt->format(DATE_ATOM),
            'redirect' => $redirect,
        ])->send();
    }

    public function resendTwoFactor(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $rawBody = file_get_contents('php://input');
        $data = json_decode($rawBody, true) ?? [];
        $challengeId = (string) ($data['challenge_id'] ?? '');
        $challenge = $_SESSION['2fa_challenge'] ?? null;
        if (!is_array($challenge) || $challengeId === '' || ($challenge['id'] ?? '') !== $challengeId) {
            ApiResponse::error('Invalid or expired challenge', 401)->send();
            return;
        }

        $lastResentAt = (int) ($challenge['last_resent_at'] ?? 0);
        $cooldown = 30;
        $nowTs = time();
        if (($nowTs - $lastResentAt) < $cooldown) {
            ApiResponse::error('Please wait before requesting another code', 429)->send();
            return;
        }

        $challenge['code'] = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $challenge['expires_at'] = (new DateTimeImmutable('+5 minutes'))->getTimestamp();
        $challenge['last_resent_at'] = $nowTs;
        $challenge['attempts'] = 0;
        $_SESSION['2fa_challenge'] = $challenge;

        ApiResponse::json([
            'resent' => true,
            'message' => 'Verification code resent.',
            'expires_in_seconds' => 300,
            'delivery' => $challenge['delivery'] ?? 'mock',
            'masked_destination' => $challenge['masked_destination'] ?? 'hidden',
        ])->send();
    }

    private function maskDestination(string $identity): string
    {
        if (str_contains($identity, '@')) {
            [$local, $domain] = explode('@', $identity, 2);
            $prefix = substr($local, 0, 1);
            return $prefix . str_repeat('*', max(1, strlen($local) - 1)) . '@' . $domain;
        }

        if (strlen($identity) <= 4) {
            return str_repeat('*', strlen($identity));
        }

        return substr($identity, 0, 2) . str_repeat('*', strlen($identity) - 4) . substr($identity, -2);
    }

    private function issueAuthCookie(string $token, DateTimeImmutable $expiresAt): void
    {
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';

        setcookie('auth_token', $token, [
            'expires' => $expiresAt->getTimestamp(),
            'path' => '/',
            'secure' => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    private function clearAuthCookie(): void
    {
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';

        setcookie('auth_token', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
}

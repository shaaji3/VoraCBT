<?php

declare(strict_types=1);

namespace App\Integration\Service;

use App\Core\Config\Environment;
use RuntimeException;

final class PayloadSignatureService
{
    public function __construct(private readonly ?Environment $env = null)
    {
    }

    public function sign(array $payload): string
    {
        return hash_hmac('sha256', $this->canonicalize($payload), $this->secret());
    }

    public function verify(array $payload, string $signature): bool
    {
        return hash_equals($this->sign($payload), $signature);
    }

    private function secret(): string
    {
        $secret = (string) (($this->env ?? Environment::getInstance())->get('RESULT_SIGNING_SECRET', ''));
        if ($secret === '') {
            throw new RuntimeException('RESULT_SIGNING_SECRET is not configured.');
        }

        return $secret;
    }

    private function canonicalize(array $payload): string
    {
        ksort($payload);
        return json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
}

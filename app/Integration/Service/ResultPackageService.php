<?php

declare(strict_types=1);

namespace App\Integration\Service;

use App\Core\Config\Environment;
use App\Core\Database\DatabaseManager;
use Doctrine\DBAL\Connection;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;
use RuntimeException;

final class ResultPackageService
{
    private Connection $db;
    private const ALLOWED_STATUSES = ['generated', 'accepted', 'rejected_signature', 'rejected_replay', 'rejected_payload'];

    public function __construct(
        private readonly ?PayloadSignatureService $signer = null,
        private readonly ?Environment $env = null,
    ) {
        $this->db = DatabaseManager::getConnection();
    }

    public function createPackage(array $payload, ?string $actorUserId = null): array
    {
        $this->validatePayload($payload, true);
        $signature = ($this->signer ?? new PayloadSignatureService($this->env))->sign($payload);
        return $this->storePackage($payload, $signature, $actorUserId, 'generated');
    }

    public function acceptImportedPackage(array $payload, string $signature, ?string $actorUserId = null): array
    {
        $this->validatePayload($payload, false);

        $idempotencyKey = (string) ($payload['idempotency_key'] ?? '');
        if ($idempotencyKey === '') {
            throw new InvalidArgumentException('idempotency_key is required');
        }

        $existing = $this->db->fetchAssociative('SELECT id, status FROM result_sync_packages WHERE idempotency_key = ?', [$idempotencyKey]);
        if ($existing) {
            return [
                'package_id' => $existing['id'],
                'payload' => $payload,
                'signature' => $signature,
                'mode' => (($this->env ?? Environment::getInstance())->isStandaloneMode()) ? 'standalone' : 'integrated',
                'status' => 'rejected_replay',
                'idempotency_key' => $idempotencyKey,
                'is_valid_signature' => false,
            ];
        }

        $signer = $this->signer ?? new PayloadSignatureService($this->env);
        $isValid = $signer->verify($payload, $signature);
        $status = $isValid ? 'accepted' : 'rejected_signature';
        $record = $this->storePackage($payload, $signature, $actorUserId, $status);
        $record['is_valid_signature'] = $isValid;
        return $record;
    }

    private function storePackage(array $payload, string $signature, ?string $actorUserId, string $status): array
    {
        if (!in_array($status, self::ALLOWED_STATUSES, true)) {
            throw new RuntimeException('Invalid result package status: ' . $status);
        }

        $mode = (($this->env ?? Environment::getInstance())->isStandaloneMode()) ? 'standalone' : 'integrated';
        $id = Uuid::uuid4()->toString();
        $idempotencyKey = (string) ($payload['idempotency_key'] ?? Uuid::uuid4()->toString());
        $payloadHash = hash('sha256', json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));

        $this->db->insert('result_sync_packages', [
            'id' => $id,
            'exam_id' => $payload['exam_id'] ?? null,
            'exam_session_id' => $payload['exam_session_id'] ?? null,
            'mode' => $mode,
            'payload' => json_encode($payload, JSON_THROW_ON_ERROR),
            'signature' => $signature,
            'status' => $status,
            'idempotency_key' => $idempotencyKey,
            'payload_hash' => $payloadHash,
            'signed_at' => $payload['signed_at'] ?? date('Y-m-d H:i:s'),
            'created_by' => $actorUserId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return [
            'package_id' => $id,
            'payload' => $payload,
            'signature' => $signature,
            'mode' => $mode,
            'status' => $status,
            'idempotency_key' => $idempotencyKey,
        ];
    }

    private function validatePayload(array $payload, bool $allowMissingIdempotency): void
    {
        foreach (['exam_id', 'exam_session_id', 'students'] as $key) {
            if (!array_key_exists($key, $payload)) {
                throw new InvalidArgumentException($key . ' is required');
            }
        }

        if (!$allowMissingIdempotency && !array_key_exists('idempotency_key', $payload)) {
            throw new InvalidArgumentException('idempotency_key is required');
        }
    }
}

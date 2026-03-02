<?php

declare(strict_types=1);

namespace App\Integration\Service;

use App\Core\Config\Environment;
use App\Core\Database\DatabaseManager;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use RuntimeException;

final class ModeAwareIdentityService
{
    private Connection $db;

    public function __construct(
        private readonly ?SmsApiClient $api = null,
        private readonly ?Environment $env = null,
    ) {
        $this->db = DatabaseManager::getConnection();
    }

    public function syncIntegratedStudents(?string $classId, ?string $sessionId): array
    {
        $this->assertIntegratedMode();

        $query = http_build_query(array_filter([
            'class_id' => $classId,
            'session' => $sessionId,
        ], static fn ($v): bool => $v !== null && $v !== ''));

        $response = ($this->api ?? new SmsApiClient())->request('GET', '/api/v1/students' . ($query ? ('?' . $query) : ''));
        $body = json_decode((string) ($response['body'] ?? '{}'), true, 512, JSON_THROW_ON_ERROR);
        $students = $body['students'] ?? [];

        foreach ($students as $student) {
            $studentId = (string) ($student['student_id'] ?? '');
            if ($studentId === '') {
                continue;
            }
            $this->upsertCache('student', $studentId, $student, true);
        }

        return ['synced' => count($students), 'students' => $students];
    }

    public function syncIntegratedStaff(?string $role): array
    {
        $this->assertIntegratedMode();

        $query = $role ? ('?role=' . urlencode($role)) : '';
        $response = ($this->api ?? new SmsApiClient())->request('GET', '/api/v1/staff' . $query);
        $body = json_decode((string) ($response['body'] ?? '{}'), true, 512, JSON_THROW_ON_ERROR);
        $staffItems = $body['staff'] ?? [];

        foreach ($staffItems as $staff) {
            $staffId = (string) ($staff['staff_id'] ?? '');
            if ($staffId === '') {
                continue;
            }
            $this->upsertCache('staff', $staffId, $staff, true);
        }

        return ['synced' => count($staffItems), 'staff' => $staffItems];
    }

    public function syncStandaloneStudents(array $students): array
    {
        $this->assertStandaloneMode();
        return $this->ingestStandalone($students, 'student');
    }

    public function syncStandaloneStaff(array $staff): array
    {
        $this->assertStandaloneMode();
        return $this->ingestStandalone($staff, 'staff');
    }

    private function ingestStandalone(array $rows, string $entityType): array
    {
        $success = 0;
        $failed = 0;
        $errors = [];
        $seenExternal = [];

        foreach ($rows as $idx => $row) {
            $rowNum = $idx + 1;
            $externalId = trim((string) ($row['external_id'] ?? $row['student_id'] ?? $row['staff_id'] ?? ''));
            if ($externalId === '') {
                $failed++;
                $errors[] = "Row {$rowNum}: external_id is required";
                continue;
            }

            if (isset($seenExternal[$externalId])) {
                $failed++;
                $errors[] = "Row {$rowNum}: duplicate external_id '{$externalId}' in payload";
                continue;
            }
            $seenExternal[$externalId] = true;

            $conflict = $this->findMappingConflict($externalId, $row['sms_student_id'] ?? null, $row['user_id'] ?? null);
            if ($conflict !== null) {
                $failed++;
                $errors[] = "Row {$rowNum}: {$conflict}";
                continue;
            }

            $this->upsertMapping($externalId, $row['sms_student_id'] ?? null, $row['user_id'] ?? null);
            $this->upsertCache($entityType, $externalId, $row, false);
            $success++;
        }

        $this->logIngestionOutcome($entityType, $success, $failed, $errors);

        return ['success' => $success, 'failed' => $failed, 'errors' => $errors];
    }


    private function logIngestionOutcome(string $entityType, int $success, int $failed, array $errors): void
    {
        $logger = new IntegrationLogService();
        $key = Uuid::uuid4()->toString();
        $logId = $logger->logRequest($key, [
            'entity_type' => $entityType,
            'success' => $success,
            'failed' => $failed,
        ]);

        $logger->logResponse($logId, $failed > 0 ? 'PARTIAL' : 'SUCCESS', [
            'errors' => $errors,
            'summary' => ['success' => $success, 'failed' => $failed],
        ]);
    }
    private function findMappingConflict(string $externalRef, ?string $smsStudentId, ?string $userId): ?string
    {
        $existing = $this->db->fetchAssociative('SELECT sms_student_id, user_id FROM student_identity_mappings WHERE external_student_ref = ?', [$externalRef]);
        if (!$existing) {
            return null;
        }

        if ($smsStudentId !== null && $existing['sms_student_id'] !== null && $existing['sms_student_id'] !== $smsStudentId) {
            return 'external_id already mapped to a different sms_student_id';
        }

        if ($userId !== null && $existing['user_id'] !== null && $existing['user_id'] !== $userId) {
            return 'external_id already mapped to a different user_id';
        }

        return null;
    }

    private function upsertMapping(string $externalRef, ?string $smsStudentId, ?string $userId): void
    {
        $existing = $this->db->fetchAssociative('SELECT id FROM student_identity_mappings WHERE external_student_ref = ?', [$externalRef]);
        $payload = [
            'sms_student_id' => $smsStudentId,
            'user_id' => $userId,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($existing) {
            $this->db->update('student_identity_mappings', $payload, ['id' => $existing['id']]);
            return;
        }

        $this->db->insert('student_identity_mappings', [
            'id' => Uuid::uuid4()->toString(),
            'external_student_ref' => $externalRef,
            'sms_student_id' => $smsStudentId,
            'user_id' => $userId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function upsertCache(string $entityType, string $externalId, array $payload, bool $readOnly): void
    {
        $existing = $this->db->fetchAssociative(
            'SELECT id FROM identity_sync_cache WHERE entity_type = ? AND external_id = ?',
            [$entityType, $externalId]
        );

        $data = [
            'full_name' => $payload['full_name'] ?? trim((string) (($payload['first_name'] ?? '') . ' ' . ($payload['last_name'] ?? ''))),
            'class_id' => $payload['class_id'] ?? null,
            'session_id' => $payload['session_id'] ?? null,
            'term_id' => $payload['term_id'] ?? null,
            'role' => $payload['role'] ?? null,
            'email' => $payload['email'] ?? null,
            'status' => $payload['status'] ?? 'active',
            'is_read_only' => $readOnly ? 1 : 0,
            'payload' => json_encode($payload, JSON_THROW_ON_ERROR),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($existing) {
            $this->db->update('identity_sync_cache', $data, ['id' => $existing['id']]);
            return;
        }

        $this->db->insert('identity_sync_cache', array_merge($data, [
            'id' => Uuid::uuid4()->toString(),
            'entity_type' => $entityType,
            'external_id' => $externalId,
        ]));
    }

    private function assertIntegratedMode(): void
    {
        $env = $this->env ?? Environment::getInstance();
        if (!$env->isConnectedMode()) {
            throw new RuntimeException('This operation is only available in integrated mode.');
        }
    }

    private function assertStandaloneMode(): void
    {
        $env = $this->env ?? Environment::getInstance();
        if (!$env->isStandaloneMode()) {
            throw new RuntimeException('This operation is only available in standalone mode.');
        }
    }
}

<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Core\Config\Environment;
use App\Core\Database\DatabaseManager;
use App\Core\Database\Migration\MigrationRunner;
use App\Integration\Service\ModeAwareIdentityService;
use App\Integration\Service\SmsApiClient;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;

class ModeAwareIdentityServiceTest extends TestCase
{
    private Connection $db;

    protected function setUp(): void
    {
        $_ENV['SYSTEM_MODE'] = 'integrated';
        ob_start();
        (new MigrationRunner())->migrate();
        ob_end_clean();

        $this->db = DatabaseManager::getConnection();
        $this->db->executeStatement('DELETE FROM identity_sync_cache');
        $this->db->executeStatement('DELETE FROM student_identity_mappings');
    }

    public function testIntegratedStudentSyncWritesReadOnlyCache(): void
    {
        $api = $this->createMock(SmsApiClient::class);
        $api->method('request')->willReturn([
            'status' => 200,
            'body' => json_encode(['students' => [[
                'student_id' => 'S001',
                'full_name' => 'John Doe',
                'class_id' => 'JSS1A',
                'session_id' => '2025/2026',
                'term_id' => 'TERM1',
                'email' => 'john@example.com',
                'status' => 'active',
            ]]], JSON_THROW_ON_ERROR),
        ]);

        $service = new ModeAwareIdentityService($api, Environment::getInstance());
        $result = $service->syncIntegratedStudents('JSS1A', '2025/2026');

        self::assertSame(1, $result['synced']);
        $row = $this->db->fetchAssociative("SELECT * FROM identity_sync_cache WHERE entity_type = 'student' AND external_id = 'S001'");
        self::assertNotFalse($row);
        self::assertSame('1', (string) $row['is_read_only']);
    }

    public function testStandaloneSyncRejectsDuplicateExternalIdsInRequest(): void
    {
        $_ENV['SYSTEM_MODE'] = 'standalone';

        $service = new ModeAwareIdentityService(null, Environment::getInstance());
        $result = $service->syncStandaloneStudents([
            ['external_id' => 'EXT-1', 'sms_student_id' => 'S-1'],
            ['external_id' => 'EXT-1', 'sms_student_id' => 'S-2'],
        ]);

        self::assertSame(1, $result['success']);
        self::assertSame(1, $result['failed']);
        self::assertNotEmpty($result['errors']);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Core\Database\DatabaseManager;
use App\Core\Database\Migration\MigrationRunner;
use App\Integration\Service\PayloadSignatureService;
use App\Integration\Service\ResultPackageService;
use PHPUnit\Framework\TestCase;

class ResultPackageServiceTest extends TestCase
{
    protected function setUp(): void
    {
        $_ENV['RESULT_SIGNING_SECRET'] = 'test-signing-secret';

        ob_start();
        (new MigrationRunner())->migrate();
        ob_end_clean();

        $db = DatabaseManager::getConnection();
        $db->executeStatement('DELETE FROM result_sync_packages');
    }

    public function testAcceptImportedPackageRejectsInvalidSignature(): void
    {
        $service = new ResultPackageService();

        $payload = [
            'exam_id' => 'e-1',
            'exam_session_id' => 's-1',
            'students' => [['student_id' => 'S1', 'score' => 70]],
            'idempotency_key' => 'idem-1',
            'signed_at' => '2026-01-01 00:00:00',
        ];

        $result = $service->acceptImportedPackage($payload, 'bad-signature');

        self::assertFalse($result['is_valid_signature']);
        self::assertSame('rejected_signature', $result['status']);
    }

    public function testAcceptImportedPackageAcceptsValidSignature(): void
    {
        $payload = [
            'exam_id' => 'e-2',
            'exam_session_id' => 's-2',
            'students' => [['student_id' => 'S2', 'score' => 80]],
            'idempotency_key' => 'idem-2',
            'signed_at' => '2026-01-01 00:00:00',
        ];

        $signature = (new PayloadSignatureService())->sign($payload);
        $result = (new ResultPackageService())->acceptImportedPackage($payload, $signature);

        self::assertTrue($result['is_valid_signature']);
        self::assertSame('accepted', $result['status']);
    }

    public function testAcceptImportedPackageRejectsReplayByIdempotencyKey(): void
    {
        $payload = [
            'exam_id' => 'e-3',
            'exam_session_id' => 's-3',
            'students' => [['student_id' => 'S3', 'score' => 55]],
            'idempotency_key' => 'idem-3',
            'signed_at' => '2026-01-01 00:00:00',
        ];

        $signature = (new PayloadSignatureService())->sign($payload);
        $service = new ResultPackageService();

        $first = $service->acceptImportedPackage($payload, $signature);
        $second = $service->acceptImportedPackage($payload, $signature);

        self::assertSame('accepted', $first['status']);
        self::assertSame('rejected_replay', $second['status']);
    }
}

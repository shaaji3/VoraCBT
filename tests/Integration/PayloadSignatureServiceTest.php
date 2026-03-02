<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Integration\Service\PayloadSignatureService;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class PayloadSignatureServiceTest extends TestCase
{
    protected function setUp(): void
    {
        $_ENV['RESULT_SIGNING_SECRET'] = 'test-signing-secret';
    }

    public function testSignatureIsStableAcrossKeyOrdering(): void
    {
        $service = new PayloadSignatureService();
        $a = ['exam_id' => 'E01', 'class_id' => 'JSS1A', 'students' => [['student_id' => 'S1', 'score' => 50]]];
        $b = ['students' => [['student_id' => 'S1', 'score' => 50]], 'class_id' => 'JSS1A', 'exam_id' => 'E01'];

        $sigA = $service->sign($a);
        $sigB = $service->sign($b);

        self::assertSame($sigA, $sigB);
        self::assertTrue($service->verify($a, $sigA));
    }

    public function testThrowsWhenSigningSecretMissing(): void
    {
        unset($_ENV['RESULT_SIGNING_SECRET']);
        $this->expectException(RuntimeException::class);
        (new PayloadSignatureService())->sign(['x' => 'y']);
    }
}

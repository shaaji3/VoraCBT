<?php

declare(strict_types=1);

namespace App\Infrastructure\Queue;

use App\Integration\Service\SmsApiClient;
use App\Core\Database\DatabaseManager;
use App\Integration\Service\PayloadSignatureService;
use Exception;

class ResultPushJob implements JobInterface
{
    private array $data;
    private SmsApiClient $api;
    private QueueInterface $queue;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->api = new SmsApiClient();
        $this->queue = new DatabaseQueue(DatabaseManager::getConnection());
    }

    public function handle(): void
    {
        $resultData = $this->data['result_data'];
        $idempotencyKey = $this->data['idempotency_key'];
        $attempt = $this->data['attempt'] ?? 1;

        try {
            $signer = new PayloadSignatureService();
            $payload = [
                'exam_id' => $resultData['exam_id'] ?? null,
                'subject_code' => $resultData['subject_code'] ?? null,
                'class_id' => $resultData['class_id'] ?? null,
                'session_id' => $resultData['session_id'] ?? null,
                'students' => $resultData['students'] ?? [],
                'exam_session_id' => $this->data['exam_session_id'] ?? null,
                'idempotency_key' => $idempotencyKey,
                'signed_at' => date('Y-m-d H:i:s'),
            ];

            $response = $this->api->request('POST', '/api/v1/results/import', [
                'body' => array_merge($payload, ['signature' => $signer->sign($payload)])
            ], $idempotencyKey);

            if ($response['status'] >= 400) {
                 throw new Exception("API error: " . $response['status']);
            }

        } catch (Exception $e) {
            $this->retry($attempt, $e);
        }
    }

    private function retry(int $attempt, Exception $e): void
    {
        $maxRetries = 5;
        if ($attempt < $maxRetries) {
            $delay = pow(2, $attempt);
            $this->data['attempt'] = $attempt + 1;

            $this->queue->pushDelayed(self::class, $this->data, $delay);
        } else {
            // Log permanent failure
            $logger = new \App\Integration\Service\IntegrationLogService();
            $log = $logger->getLogByIdempotencyKey($this->data['idempotency_key']);
            if ($log) {
                $logger->logResponse($log['id'], 'FAILED_PERMANENTLY', ['error' => $e->getMessage()]);
            }
        }
    }
}

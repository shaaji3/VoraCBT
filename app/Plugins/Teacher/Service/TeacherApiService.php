<?php

declare(strict_types=1);

namespace App\Plugins\Teacher\Service;

use App\Core\Http\ApiResponse;
use App\Plugins\Support\RouteAuthorizer;
use App\Plugins\Support\Service\AuthContextService;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Exception;
use Ramsey\Uuid\Uuid;
use App\Question\Type\QuestionTypeFactory;
use App\Question\Exception\QuestionValidationException;

final class TeacherApiService
{
    public function __construct(
        private readonly Connection $db,
        private readonly ?AuthContextService $authContextService = null,
    ) {
    }

    public function questionRepository(): void
    {
        RouteAuthorizer::authorize(['teacher', 'staff', 'admin', 'super_admin'], function (): void {
            try {
                $limit = max(1, min(100, (int) ($_GET['limit'] ?? 25)));
                $offset = max(0, (int) ($_GET['offset'] ?? 0));

                $rows = $this->db->fetchAllAssociative(
                    'SELECT id, type, content, metadata, version, created_at, updated_at
                     FROM questions
                     WHERE archived_at IS NULL
                     ORDER BY updated_at DESC
                     LIMIT ? OFFSET ?',
                    [$limit, $offset],
                    [\PDO::PARAM_INT, \PDO::PARAM_INT]
                );

                $items = array_map(static function (array $row): array {
                    $content = is_string($row['content']) ? json_decode($row['content'], true) : ($row['content'] ?? []);
                    $metadata = is_string($row['metadata'] ?? null) ? json_decode((string) $row['metadata'], true) : ($row['metadata'] ?? []);

                    return [
                        'id' => $row['id'],
                        'type' => $row['type'],
                        'prompt' => $content['prompt'] ?? $content['question'] ?? '',
                        'metadata' => is_array($metadata) ? $metadata : [],
                        'version' => (int) ($row['version'] ?? 1),
                        'updated_at' => $row['updated_at'] ?? null,
                    ];
                }, $rows);

                $total = (int) $this->db->fetchOne('SELECT COUNT(*) FROM questions WHERE archived_at IS NULL');

                ApiResponse::json([
                    'items' => $items,
                    'pagination' => [
                        'limit' => $limit,
                        'offset' => $offset,
                        'total' => $total,
                    ],
                ])->send();
            } catch (Exception $e) {
                ApiResponse::error($e->getMessage(), 500)->send();
            }
        });
    }

    public function showQuestion(string $id): void
    {
        RouteAuthorizer::authorize(['teacher', 'staff', 'admin', 'super_admin'], function () use ($id): void {
            try {
                $row = $this->db->fetchAssociative(
                    'SELECT id, type, content, metadata, version, updated_at
                     FROM questions
                     WHERE id = ? AND archived_at IS NULL
                     LIMIT 1',
                    [$id]
                );

                if (!$row) {
                    ApiResponse::error('Question not found', 404)->send();
                    return;
                }

                ApiResponse::json([
                    'id' => $row['id'],
                    'type' => $row['type'],
                    'content' => is_string($row['content']) ? json_decode($row['content'], true) : $row['content'],
                    'metadata' => is_string($row['metadata'] ?? null) ? json_decode((string) $row['metadata'], true) : ($row['metadata'] ?? []),
                    'version' => (int) ($row['version'] ?? 1),
                    'updated_at' => $row['updated_at'] ?? null,
                ])->send();
            } catch (Exception $e) {
                ApiResponse::error($e->getMessage(), 500)->send();
            }
        });
    }

    public function createQuestion(): void
    {
        RouteAuthorizer::authorize(['teacher', 'staff', 'admin', 'super_admin'], function (): void {
            try {
                $payload = json_decode((string) file_get_contents('php://input'), true);
                if (!is_array($payload)) {
                    ApiResponse::error('Invalid payload', 422)->send();
                    return;
                }

                $type = (string) ($payload['type'] ?? 'mcq');
                $content = is_array($payload['content'] ?? null) ? $payload['content'] : [];
                $metadata = is_array($payload['metadata'] ?? null) ? $payload['metadata'] : [];
                $handler = QuestionTypeFactory::create($type);
                $handler->validate($content);
                $content = $handler->normalize($content);
                $prompt = trim((string) ($content['prompt'] ?? $content['case_text'] ?? $content['text'] ?? $content['passage'] ?? ''));
                if ($prompt === '') {
                    ApiResponse::error('Question prompt is required', 422)->send();
                    return;
                }

                $id = Uuid::uuid4()->toString();
                $now = (new DateTimeImmutable())->format('Y-m-d H:i:s');
                $actorId = $this->authContextService?->currentUserId();

                $this->db->insert('questions', [
                    'id' => $id,
                    'type' => $type,
                    'content' => json_encode($content, JSON_UNESCAPED_UNICODE),
                    'metadata' => json_encode($metadata, JSON_UNESCAPED_UNICODE),
                    'version' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                    'created_by' => $actorId,
                    'updated_by' => $actorId,
                ]);

                ApiResponse::json(['created' => true, 'id' => $id], 201)->send();
            } catch (Exception $e) {
                ApiResponse::error($e->getMessage(), 500)->send();
            }
        });
    }

    public function updateQuestion(string $id): void
    {
        RouteAuthorizer::authorize(['teacher', 'staff', 'admin', 'super_admin'], function () use ($id): void {
            try {
                $payload = json_decode((string) file_get_contents('php://input'), true);
                if (!is_array($payload)) {
                    ApiResponse::error('Invalid payload', 422)->send();
                    return;
                }

                $expectedVersion = isset($payload['version']) ? (int) $payload['version'] : null;
                if ($expectedVersion === null || $expectedVersion < 1) {
                    ApiResponse::error('Question version is required for optimistic locking', 409)->send();
                    return;
                }

                $current = $this->db->fetchAssociative('SELECT id, version FROM questions WHERE id = ? AND archived_at IS NULL LIMIT 1', [$id]);
                if (!$current) {
                    ApiResponse::error('Question not found', 404)->send();
                    return;
                }

                $currentVersion = (int) ($current['version'] ?? 0);
                if ($expectedVersion !== $currentVersion) {
                    ApiResponse::error('Question has been modified by another user', 409)->send();
                    return;
                }

                $type = (string) ($payload['type'] ?? 'mcq');
                $content = is_array($payload['content'] ?? null) ? $payload['content'] : [];
                $metadata = is_array($payload['metadata'] ?? null) ? $payload['metadata'] : [];
                $handler = QuestionTypeFactory::create($type);
                $handler->validate($content);
                $content = $handler->normalize($content);
                $actorId = $this->authContextService?->currentUserId();
                $now = (new DateTimeImmutable())->format('Y-m-d H:i:s');

                $this->db->update('questions', [
                    'type' => $type,
                    'content' => json_encode($content, JSON_UNESCAPED_UNICODE),
                    'metadata' => json_encode($metadata, JSON_UNESCAPED_UNICODE),
                    'version' => $currentVersion + 1,
                    'updated_at' => $now,
                    'updated_by' => $actorId,
                ], ['id' => $id]);

                ApiResponse::json(['updated' => true, 'id' => $id, 'version' => $currentVersion + 1])->send();
            } catch (Exception $e) {
                ApiResponse::error($e->getMessage(), 500)->send();
            }
        });
    }

    public function deleteQuestion(string $id): void
    {
        RouteAuthorizer::authorize(['teacher', 'staff', 'admin', 'super_admin'], function () use ($id): void {
            try {
                $now = (new DateTimeImmutable())->format('Y-m-d H:i:s');
                $affected = $this->db->executeStatement('UPDATE questions SET archived_at = ? WHERE id = ? AND archived_at IS NULL', [$now, $id]);
                if ($affected === 0) {
                    ApiResponse::error('Question not found', 404)->send();
                    return;
                }

                ApiResponse::json(['deleted' => true, 'id' => $id])->send();
            } catch (Exception $e) {
                ApiResponse::error($e->getMessage(), 500)->send();
            }
        });
    }


    public function previewQuestionImport(): void
    {
        RouteAuthorizer::authorize(['teacher', 'staff', 'admin', 'super_admin'], function (): void {
            try {
                if (!isset($_FILES['file'])) {
                    ApiResponse::error('No file uploaded', 400)->send();
                    return;
                }

                $rows = $this->parseQuestionImportFile((string) $_FILES['file']['tmp_name'], (string) ($_FILES['file']['name'] ?? ''));
                $report = ['total_rows' => count($rows), 'valid_rows' => 0, 'invalid_rows' => 0, 'errors' => []];

                foreach ($rows as $index => $row) {
                    try {
                        $this->buildQuestionFromImportRow($row);
                        $report['valid_rows']++;
                    } catch (Exception $e) {
                        $report['invalid_rows']++;
                        $report['errors'][] = sprintf('Row %d: %s', $index + 2, $e->getMessage());
                    }
                }

                ApiResponse::json($report)->send();
            } catch (Exception $e) {
                ApiResponse::error($e->getMessage(), 500)->send();
            }
        });
    }

    public function commitQuestionImport(): void
    {
        RouteAuthorizer::authorize(['teacher', 'staff', 'admin', 'super_admin'], function (): void {
            try {
                if (!isset($_FILES['file'])) {
                    ApiResponse::error('No file uploaded', 400)->send();
                    return;
                }

                $rows = $this->parseQuestionImportFile((string) $_FILES['file']['tmp_name'], (string) ($_FILES['file']['name'] ?? ''));
                $created = 0;
                $failed = 0;
                $now = (new DateTimeImmutable())->format('Y-m-d H:i:s');
                $actorId = $this->authContextService?->currentUserId();

                foreach ($rows as $row) {
                    try {
                        $question = $this->buildQuestionFromImportRow($row);
                        $id = Uuid::uuid4()->toString();
                        $this->db->insert('questions', [
                            'id' => $id,
                            'type' => $question['type'],
                            'content' => json_encode($question['content'], JSON_UNESCAPED_UNICODE),
                            'metadata' => json_encode($question['metadata'], JSON_UNESCAPED_UNICODE),
                            'version' => 1,
                            'created_at' => $now,
                            'updated_at' => $now,
                            'created_by' => $actorId,
                            'updated_by' => $actorId,
                        ]);
                        $created++;
                    } catch (Exception) {
                        $failed++;
                    }
                }

                ApiResponse::json(['created' => $created, 'failed' => $failed])->send();
            } catch (Exception $e) {
                ApiResponse::error($e->getMessage(), 500)->send();
            }
        });
    }

    private function parseQuestionImportFile(string $filePath, string $originalName): array
    {
        $extension = strtolower((string) pathinfo($originalName, PATHINFO_EXTENSION));

        if ($extension === 'xlsx') {
            return $this->parseQuestionXlsx($filePath);
        }

        return $this->parseQuestionCsv($filePath);
    }

    private function parseQuestionCsv(string $filePath): array
    {
        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new Exception('Unable to open uploaded CSV');
        }

        $header = fgetcsv($handle);
        if (!is_array($header)) {
            fclose($handle);
            throw new Exception('CSV header is missing');
        }

        $header = array_map(static fn($h) => strtolower(trim((string) $h)), $header);
        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) !== count($header)) {
                continue;
            }
            $rows[] = array_combine($header, $row);
        }

        fclose($handle);

        return $rows;
    }

    private function parseQuestionXlsx(string $filePath): array
    {
        if (!class_exists('ZipArchive')) {
            throw new Exception('Excel import requires ZipArchive extension on the server.');
        }

        $zip = new \ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new Exception('Unable to open uploaded Excel file.');
        }

        $sharedStrings = [];
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        if (is_string($sharedXml)) {
            $xml = simplexml_load_string($sharedXml);
            if ($xml !== false && isset($xml->si)) {
                foreach ($xml->si as $si) {
                    $sharedStrings[] = trim((string) $si->t);
                }
            }
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if (!is_string($sheetXml)) {
            throw new Exception('Excel sheet1 is missing.');
        }

        $xml = simplexml_load_string($sheetXml);
        if ($xml === false || !isset($xml->sheetData->row)) {
            throw new Exception('Excel file has no tabular data.');
        }

        $rows = [];
        foreach ($xml->sheetData->row as $row) {
            $cells = [];
            foreach ($row->c as $cell) {
                $ref = (string) ($cell['r'] ?? '');
                $col = preg_replace('/\d+/', '', $ref);
                $index = $this->excelColumnToIndex($col);
                $type = (string) ($cell['t'] ?? '');
                $value = '';
                if ($type === 's') {
                    $idx = (int) ($cell->v ?? -1);
                    $value = $sharedStrings[$idx] ?? '';
                } elseif (isset($cell->v)) {
                    $value = (string) $cell->v;
                }
                $cells[$index] = trim((string) $value);
            }
            if (!empty($cells)) {
                ksort($cells);
                $rows[] = $cells;
            }
        }

        if (count($rows) < 2) {
            throw new Exception('Excel header/data rows are missing.');
        }

        $header = array_map(static fn($h) => strtolower(trim((string) $h)), array_values($rows[0]));
        $items = [];
        foreach (array_slice($rows, 1) as $values) {
            $values = array_values($values);
            if (count($values) !== count($header)) {
                continue;
            }
            $items[] = array_combine($header, $values);
        }

        return $items;
    }

    private function excelColumnToIndex(string $letters): int
    {
        $letters = strtoupper($letters);
        $index = 0;
        for ($i = 0; $i < strlen($letters); $i++) {
            $index = $index * 26 + (ord($letters[$i]) - 64);
        }

        return max(0, $index - 1);
    }

    private function buildQuestionFromImportRow(array $row): array
    {
        $type = trim((string) ($row['type'] ?? 'mcq'));
        $prompt = trim((string) ($row['prompt'] ?? ''));
        if ($prompt === '') {
            throw new Exception('prompt is required');
        }

        $options = json_decode((string) ($row['options'] ?? '[]'), true);
        if (!is_array($options)) {
            $options = [];
        }

        $correct = json_decode((string) ($row['correct_options'] ?? '[]'), true);
        if (!is_array($correct)) {
            $correct = [];
        }

        $content = match ($type) {
            'mcq' => ['prompt' => $prompt, 'options' => $options, 'correct_options' => $correct],
            'true_false' => ['prompt' => $prompt, 'correct_answer' => filter_var($row['correct_answer'] ?? 'true', FILTER_VALIDATE_BOOLEAN)],
            'fill_in_the_blank' => json_decode((string) ($row['content'] ?? '{}'), true) ?: ['text' => $prompt, 'blanks' => ['1' => ['correct' => $correct]]],
            'matching' => json_decode((string) ($row['content'] ?? '{}'), true) ?: ['text' => $prompt, 'pairs' => $options],
            'drag_drop' => json_decode((string) ($row['content'] ?? '{}'), true) ?: ['prompt' => $prompt, 'items' => [], 'targets' => [], 'correct_mapping' => []],
            'case_study' => json_decode((string) ($row['content'] ?? '{}'), true) ?: ['case_text' => $prompt, 'questions' => []],
            default => ['prompt' => $prompt, 'options' => $options, 'correct_options' => $correct],
        };

        $metadata = [
            'difficulty' => trim((string) ($row['difficulty'] ?? 'medium')),
            'tags' => array_values(array_filter(array_map('trim', explode(',', (string) ($row['tags'] ?? ''))))),
            'learning_objective' => trim((string) ($row['learning_objective'] ?? '')),
        ];

        try {
            $handler = QuestionTypeFactory::create($type);
            $handler->validate($content);
            $content = $handler->normalize($content);
        } catch (QuestionValidationException $e) {
            throw new Exception($e->getMessage(), 0, $e);
        }

        return ['type' => $type, 'content' => $content, 'metadata' => $metadata];
    }

    public function pendingManualGrading(): void
    {
        RouteAuthorizer::authorize(['teacher', 'staff', 'admin', 'super_admin'], function (): void {
            try {
                $limit = max(1, min(100, (int) ($_GET['limit'] ?? 20)));

                $rows = $this->db->fetchAllAssociative(
                    "SELECT DISTINCT
                        s.id AS session_id,
                        s.user_id,
                        s.exam_template_id,
                        t.title AS exam_title,
                        u.first_name,
                        u.last_name,
                        u.email AS student_email,
                        s.end_time AS submitted_at
                     FROM exam_sessions s
                     JOIN exam_session_answers a ON s.id = a.exam_session_id
                     JOIN exam_templates t ON s.exam_template_id = t.id
                     JOIN users u ON s.user_id = u.id
                     WHERE a.marks_obtained IS NULL
                     AND s.status IN ('submitted', 'completed')
                     ORDER BY s.end_time ASC
                     LIMIT ?",
                    [$limit],
                    [\PDO::PARAM_INT]
                );

                ApiResponse::json(['items' => $rows, 'count' => count($rows)])->send();
            } catch (Exception $e) {
                ApiResponse::error($e->getMessage(), 500)->send();
            }
        });
    }

    public function ungradedAnswers(string $sessionId): void
    {
        RouteAuthorizer::authorize(['teacher', 'staff', 'admin', 'super_admin'], function () use ($sessionId): void {
            try {
                $rows = $this->db->fetchAllAssociative(
                    "SELECT
                        a.id AS answer_id,
                        a.answer_payload,
                        a.updated_at,
                        q.content AS question_content,
                        q.type AS question_type,
                        eq.marks AS max_marks
                     FROM exam_session_answers a
                     JOIN questions q ON a.question_id = q.id
                     JOIN exam_sessions s ON a.exam_session_id = s.id
                     JOIN exam_sections sec ON sec.exam_template_id = s.exam_template_id
                     JOIN exam_questions eq ON eq.question_id = q.id AND eq.exam_section_id = sec.id
                     WHERE a.exam_session_id = ?
                     AND a.marks_obtained IS NULL",
                    [$sessionId]
                );

                ApiResponse::json(['items' => $rows, 'count' => count($rows)])->send();
            } catch (Exception $e) {
                ApiResponse::error($e->getMessage(), 500)->send();
            }
        });
    }

    public function scoreAnswer(string $answerId): void
    {
        RouteAuthorizer::authorize(['teacher', 'staff', 'admin', 'super_admin'], function () use ($answerId): void {
            try {
                $raw = json_decode((string) file_get_contents('php://input'), true);
                $score = (float) ($raw['score'] ?? -1);
                $feedback = (string) ($raw['feedback'] ?? '');
                $moderationStatus = (string) ($raw['moderation_status'] ?? 'reviewed');
                $moderationNote = (string) ($raw['moderation_note'] ?? '');
                $expectedUpdatedAt = isset($raw['expected_updated_at']) ? (string) $raw['expected_updated_at'] : null;

                if ($score < 0) {
                    ApiResponse::error('Score is required', 422)->send();
                    return;
                }

                if ($expectedUpdatedAt === null || trim($expectedUpdatedAt) === '') {
                    ApiResponse::error('expected_updated_at is required for optimistic locking', 409)->send();
                    return;
                }

                $max = $this->db->fetchOne(
                    "SELECT eq.marks
                     FROM exam_session_answers a
                     JOIN exam_sessions s ON a.exam_session_id = s.id
                     JOIN exam_sections sec ON sec.exam_template_id = s.exam_template_id
                     JOIN exam_questions eq ON eq.question_id = a.question_id AND eq.exam_section_id = sec.id
                     WHERE a.id = ?
                     LIMIT 1",
                    [$answerId]
                );

                if ($max === false || $max === null) {
                    ApiResponse::error('Answer not found', 404)->send();
                    return;
                }

                $maxScore = (float) $max;
                if ($score > $maxScore) {
                    ApiResponse::error('Score exceeds maximum marks', 422)->send();
                    return;
                }

                $moderationPayload = json_encode([
                    'moderation_status' => $moderationStatus,
                    'moderation_note' => $moderationNote,
                    'feedback' => $feedback,
                ], JSON_UNESCAPED_UNICODE);

                $now = (new DateTimeImmutable())->format('Y-m-d H:i:s');
                $affected = $this->db->executeStatement(
                    'UPDATE exam_session_answers
                     SET marks_obtained = ?, comments = ?, updated_at = ?
                     WHERE id = ? AND updated_at = ?',
                    [$score, $moderationPayload, $now, $answerId, $expectedUpdatedAt]
                );

                if ($affected === 0) {
                    ApiResponse::error('Answer was modified by another grader. Reload and retry.', 409)->send();
                    return;
                }

                ApiResponse::json([
                    'saved' => true,
                    'answer_id' => $answerId,
                    'score' => $score,
                    'moderation_status' => $moderationStatus,
                    'updated_at' => $now,
                ])->send();
            } catch (Exception $e) {
                ApiResponse::error($e->getMessage(), 500)->send();
            }
        });
    }
}

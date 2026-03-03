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
                $prompt = trim((string) ($content['prompt'] ?? ''));
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

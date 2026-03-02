<?php

declare(strict_types=1);

namespace App\Plugins\Teacher\Service;

use App\Core\Http\ApiResponse;
use App\Plugins\Support\RouteAuthorizer;
use Doctrine\DBAL\Connection;
use Exception;

final class TeacherApiService
{
    public function __construct(private readonly Connection $db)
    {
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
}

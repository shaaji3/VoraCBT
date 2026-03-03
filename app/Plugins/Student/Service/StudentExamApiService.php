<?php

declare(strict_types=1);

namespace App\Plugins\Student\Service;

use App\Core\Http\ApiResponse;
use App\Domain\Exam\Service\SessionRecoveryService;
use App\Domain\Proctoring\Service\ProctoringService;
use App\Domain\Proctoring\Service\SessionIntegrityService;
use App\Plugins\Support\RouteAuthorizer;
use App\Plugins\Support\Service\AuthContextService;
use DateTime;
use Doctrine\DBAL\Connection;
use Exception;
use Ramsey\Uuid\Uuid;

final class StudentExamApiService
{
    public function __construct(
        private readonly Connection $db,
        private readonly ProctoringService $proctoringService,
        private readonly SessionRecoveryService $sessionRecoveryService,
        private readonly SessionIntegrityService $sessionIntegrityService,
        private readonly AuthContextService $authContextService,
    ) {
    }

    public function overview(): void
    {
        RouteAuthorizer::authorize(['student', 'admin', 'super_admin'], function (): void {
            try {
                $currentUser = $this->currentUser();
                if ($currentUser === null) {
                    ApiResponse::error('Unauthorized', 401)->send();
                    return;
                }

                $requestedUserId = $_GET['user_id'] ?? null;
                $isAdmin = in_array($currentUser['role'] ?? '', ['admin', 'super_admin'], true);

                // Students can only access their own overview. Admin roles may inspect another user overview.
                $userId = ($isAdmin && is_string($requestedUserId) && $requestedUserId !== '')
                    ? $requestedUserId
                    : $currentUser['id'];

                $inProgress = $this->db->fetchAllAssociative(
                    "SELECT s.id, s.status, s.start_time, e.title, e.subject, e.duration_minutes
                     FROM exam_sessions s
                     INNER JOIN exam_templates e ON e.id = s.exam_template_id
                     WHERE s.user_id = ? AND s.status IN ('started', 'in_progress')
                     ORDER BY s.updated_at DESC
                     LIMIT 5",
                    [$userId]
                );

                $upcoming = $this->db->fetchAllAssociative(
                    "SELECT id, title, subject, duration_minutes, total_marks, start_time, end_time
                     FROM exam_templates ORDER BY start_time ASC LIMIT 12"
                );

                $history = $this->db->fetchAllAssociative(
                    "SELECT s.id, s.score, s.end_time, e.title, e.subject
                     FROM exam_sessions s
                     INNER JOIN exam_templates e ON e.id = s.exam_template_id
                     WHERE s.user_id = ? AND s.status IN ('submitted', 'completed', 'graded')
                     ORDER BY s.end_time DESC
                     LIMIT 20",
                    [$userId]
                );

                ApiResponse::json(['in_progress' => $inProgress, 'upcoming' => $upcoming, 'history' => $history])->send();
            } catch (Exception $e) {
                ApiResponse::error($e->getMessage(), 500)->send();
            }
        });
    }


    public function results(): void
    {
        RouteAuthorizer::authorize(['student', 'admin', 'super_admin'], function (): void {
            try {
                $currentUser = $this->currentUser();
                if ($currentUser === null) {
                    ApiResponse::error('Unauthorized', 401)->send();
                    return;
                }

                $requestedUserId = $_GET['user_id'] ?? null;
                $isAdmin = in_array($currentUser['role'] ?? '', ['admin', 'super_admin'], true);
                $userId = ($isAdmin && is_string($requestedUserId) && $requestedUserId !== '') ? $requestedUserId : $currentUser['id'];

                $rows = $this->db->fetchAllAssociative(
                    "SELECT s.id, s.score, s.total_marks, s.end_time, e.title, e.subject
                     FROM exam_sessions s
                     INNER JOIN exam_templates e ON e.id = s.exam_template_id
                     WHERE s.user_id = ? AND s.status IN ('submitted', 'completed', 'graded')
                     ORDER BY s.end_time DESC
                     LIMIT 50",
                    [$userId]
                );

                $items = array_map(static function (array $row): array {
                    $score = (float) ($row['score'] ?? 0);
                    $total = (float) ($row['total_marks'] ?? 0);
                    $percent = $total > 0 ? round(($score / $total) * 100, 2) : null;

                    return [
                        'session_id' => $row['id'],
                        'title' => $row['title'] ?? 'Untitled Exam',
                        'subject' => $row['subject'] ?? 'General',
                        'score' => $score,
                        'total_marks' => $total,
                        'percent' => $percent,
                        'completed_at' => $row['end_time'] ?? null,
                    ];
                }, $rows);

                ApiResponse::json(['items' => $items, 'count' => count($items)])->send();
            } catch (Exception $e) {
                ApiResponse::error($e->getMessage(), 500)->send();
            }
        });
    }

    public function exam(string $sessionId): void
    {
        RouteAuthorizer::authorize(['student', 'admin', 'super_admin'], function () use ($sessionId): void {
            try {
                $session = $this->db->fetchAssociative(
                    'SELECT s.id, s.user_id, s.status, e.title, COALESCE(e.duration_minutes, 0) AS duration_minutes
                     FROM exam_sessions s
                     INNER JOIN exam_templates e ON e.id = s.exam_template_id
                     WHERE s.id = ?',
                    [$sessionId]
                );

                if (!$session) {
                    ApiResponse::error('Exam session not found', 404)->send();
                    return;
                }

                if (!$this->canAccessSession($session)) {
                    ApiResponse::error('Forbidden', 403)->send();
                    return;
                }

                $rows = $this->db->fetchAllAssociative(
                    'SELECT q.id, q.type, q.content, q.metadata, sq.exam_section_id, sq.question_order
                     FROM exam_session_questions sq
                     INNER JOIN questions q ON q.id = sq.question_id
                     WHERE sq.exam_session_id = ?
                     ORDER BY sq.question_order ASC',
                    [$sessionId]
                );

                $questions = array_map(function (array $row): array {
                    $content = is_string($row['content']) ? json_decode($row['content'], true) : ($row['content'] ?? []);
                    $metadata = is_string($row['metadata'] ?? null) ? json_decode((string) $row['metadata'], true) : ($row['metadata'] ?? []);

                    return [
                        'id' => $row['id'],
                        'type' => $row['type'],
                        'section_id' => $row['exam_section_id'],
                        'prompt' => $content['prompt'] ?? $content['question'] ?? '',
                        'options' => $content['options'] ?? [],
                        'content' => $content,
                        'metadata' => is_array($metadata) ? $metadata : [],
                    ];
                }, $rows);

                ApiResponse::json([
                    'id' => $session['id'],
                    'title' => $session['title'],
                    'status' => $session['status'],
                    'duration_seconds' => ((int) $session['duration_minutes']) * 60,
                    'session_token' => $this->proctoringService->getSessionToken($sessionId),
                    'questions' => $questions,
                ])->send();
            } catch (Exception $e) {
                ApiResponse::error($e->getMessage(), 500)->send();
            }
        });
    }

    public function saveAnswer(string $sessionId): void
    {
        RouteAuthorizer::authorize(['student', 'admin', 'super_admin'], function () use ($sessionId): void {
            try {
                $session = $this->db->fetchAssociative('SELECT id, user_id, status FROM exam_sessions WHERE id = ?', [$sessionId]);
                if (!$session) {
                    ApiResponse::error('Exam session not found', 404)->send();
                    return;
                }

                if (!$this->canAccessSession($session)) {
                    ApiResponse::error('Forbidden', 403)->send();
                    return;
                }

                $input = json_decode((string) file_get_contents('php://input'), true);
                $questionId = $input['question_id'] ?? null;
                $token = $input['token'] ?? null;

                if (!$questionId || !$token) {
                    ApiResponse::error('Missing question_id or token', 400)->send();
                    return;
                }

                if (!$this->sessionIntegrityService->validateSessionToken($sessionId, (string) $token)) {
                    ApiResponse::error('Invalid session token', 403)->send();
                    return;
                }

                if (($session['status'] ?? '') !== 'in_progress' && ($session['status'] ?? '') !== 'started') {
                    ApiResponse::error('Session is not active', 409)->send();
                    return;
                }

                $now = (new DateTime())->format('Y-m-d H:i:s');
                $existingId = $this->db->fetchOne(
                    'SELECT id FROM exam_session_answers WHERE exam_session_id = ? AND question_id = ?',
                    [$sessionId, $questionId]
                );

                $answerPayload = [
                    'answer' => $input['answer'] ?? null,
                    'saved_at' => $now,
                ];

                if ($existingId) {
                    $this->db->update('exam_session_answers', [
                        'answer_payload' => json_encode($answerPayload, JSON_THROW_ON_ERROR),
                        'updated_at' => $now,
                    ], ['id' => $existingId]);
                } else {
                    $this->db->insert('exam_session_answers', [
                        'id' => Uuid::uuid4()->toString(),
                        'exam_session_id' => $sessionId,
                        'question_id' => $questionId,
                        'answer_payload' => json_encode($answerPayload, JSON_THROW_ON_ERROR),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                ApiResponse::json(['status' => 'saved'])->send();
            } catch (Exception $e) {
                ApiResponse::error($e->getMessage(), 500)->send();
            }
        });
    }

    public function submit(string $sessionId): void
    {
        RouteAuthorizer::authorize(['student', 'admin', 'super_admin'], function () use ($sessionId): void {
            try {
                $session = $this->db->fetchAssociative('SELECT id, user_id, status FROM exam_sessions WHERE id = ?', [$sessionId]);
                if (!$session) {
                    ApiResponse::error('Exam session not found', 404)->send();
                    return;
                }

                if (!$this->canAccessSession($session)) {
                    ApiResponse::error('Forbidden', 403)->send();
                    return;
                }

                $input = json_decode((string) file_get_contents('php://input'), true);
                $token = $input['token'] ?? null;
                if (!$token || !$this->sessionIntegrityService->validateSessionToken($sessionId, (string) $token)) {
                    ApiResponse::error('Invalid session token', 403)->send();
                    return;
                }

                if (($session['status'] ?? '') === 'submitted' || ($session['status'] ?? '') === 'graded') {
                    ApiResponse::json(['status' => 'submitted'])->send();
                    return;
                }

                $now = (new DateTime())->format('Y-m-d H:i:s');
                $this->sessionIntegrityService->lockSession($sessionId);
                $this->sessionIntegrityService->generateIntegrityHash($sessionId);
                $this->db->update('exam_sessions', ['end_time' => $now, 'updated_at' => $now], ['id' => $sessionId]);

                ApiResponse::json(['status' => 'submitted'])->send();
            } catch (Exception $e) {
                ApiResponse::error($e->getMessage(), 500)->send();
            }
        });
    }

    public function proctoringEventBySession(string $sessionId): void
    {
        RouteAuthorizer::authorize(['student', 'admin', 'super_admin'], function () use ($sessionId): void {
            try {
                $session = $this->db->fetchAssociative('SELECT id, user_id FROM exam_sessions WHERE id = ?', [$sessionId]);
                if (!$session) {
                    ApiResponse::error('Exam session not found', 404)->send();
                    return;
                }

                if (!$this->canAccessSession($session)) {
                    ApiResponse::error('Forbidden', 403)->send();
                    return;
                }

                $input = json_decode((string) file_get_contents('php://input'), true);
                $token = $input['token'] ?? null;
                $proctoringSessionId = is_string($token) ? $this->proctoringService->getSessionIdByToken($token) : null;

                if (!$proctoringSessionId) {
                    $proctoringSessionId = $this->db->fetchOne(
                        'SELECT id FROM proctoring_sessions WHERE exam_session_id = ? ORDER BY start_time DESC LIMIT 1',
                        [$sessionId]
                    );
                }

                if (!$proctoringSessionId) {
                    ApiResponse::error('Proctoring session not found', 404)->send();
                    return;
                }

                $eventType = (string) ($input['type'] ?? 'client_event');
                $description = (string) ($input['description'] ?? '');
                $severity = (string) ($input['severity'] ?? 'warning');

                $this->proctoringService->logEvent(
                    (string) $proctoringSessionId,
                    $eventType,
                    ['description' => $description, 'raw' => $input],
                    $severity
                );

                ApiResponse::json(['status' => 'logged'])->send();
            } catch (Exception $e) {
                ApiResponse::error($e->getMessage(), 500)->send();
            }
        });
    }

    public function proctoringEvent(): void
    {
        RouteAuthorizer::authorize(['student', 'admin', 'super_admin'], function (): void {
            try {
                $input = json_decode((string) file_get_contents('php://input'), true);
                $token = $input['token'] ?? null;
                $eventType = $input['event_type'] ?? null;

                if (!$token || !$eventType) {
                    ApiResponse::error('Missing token or event_type', 400)->send();
                    return;
                }

                $sessionId = $this->proctoringService->getSessionIdByToken($token);
                if (!$sessionId) {
                    ApiResponse::error('Invalid session token', 403)->send();
                    return;
                }

                $payload = is_array($input['payload'] ?? null) ? $input['payload'] : [];
                $severity = (string) ($input['severity'] ?? 'info');
                $this->proctoringService->logEvent($sessionId, (string) $eventType, $payload, $severity);
                ApiResponse::json(['status' => 'logged'])->send();
            } catch (Exception $e) {
                ApiResponse::error($e->getMessage(), 500)->send();
            }
        });
    }

    public function proctoringHeartbeat(): void
    {
        RouteAuthorizer::authorize(['student', 'admin', 'super_admin'], function (): void {
            try {
                $input = json_decode((string) file_get_contents('php://input'), true);
                $token = $input['token'] ?? null;

                if (!$token) {
                    ApiResponse::error('Missing token', 400)->send();
                    return;
                }

                $sessionId = $this->proctoringService->getSessionIdByToken($token);
                if (!$sessionId) {
                    ApiResponse::error('Invalid session token', 403)->send();
                    return;
                }

                $this->proctoringService->logEvent($sessionId, 'heartbeat', [], 'info');
                ApiResponse::json(['status' => 'alive'])->send();
            } catch (Exception $e) {
                ApiResponse::error($e->getMessage(), 500)->send();
            }
        });
    }

    public function autosave(): void
    {
        RouteAuthorizer::authorize(['student', 'admin', 'super_admin'], function (): void {
            try {
                $input = json_decode((string) file_get_contents('php://input'), true);
                $sessionId = $input['session_id'] ?? null;
                $token = $input['token'] ?? null;

                if (!$sessionId || !$token) {
                    ApiResponse::error('Missing session_id or token', 400)->send();
                    return;
                }

                if (!$this->sessionIntegrityService->validateSessionToken((string) $sessionId, (string) $token)) {
                    ApiResponse::error('Invalid session token', 403)->send();
                    return;
                }

                $result = $this->sessionRecoveryService->autosave(
                    (string) $sessionId,
                    $input['last_question_id'] ?? null,
                    is_array($input['draft_payload'] ?? null) ? $input['draft_payload'] : [],
                    $input['client_timestamp'] ?? null,
                );

                ApiResponse::json($result)->send();
            } catch (Exception $e) {
                ApiResponse::error($e->getMessage(), 500)->send();
            }
        });
    }

    public function resumeState(): void
    {
        RouteAuthorizer::authorize(['student', 'admin', 'super_admin'], function (): void {
            try {
                $sessionId = $_GET['session_id'] ?? null;
                $token = $_GET['token'] ?? null;
                if (!$sessionId || !$token) {
                    ApiResponse::error('Missing session_id or token', 400)->send();
                    return;
                }

                if (!$this->sessionIntegrityService->validateSessionToken((string) $sessionId, (string) $token)) {
                    ApiResponse::error('Invalid session token', 403)->send();
                    return;
                }

                ApiResponse::json($this->sessionRecoveryService->resumeState((string) $sessionId))->send();
            } catch (Exception $e) {
                ApiResponse::error($e->getMessage(), 500)->send();
            }
        });
    }

    private function canAccessSession(array $session): bool
    {
        $user = $this->currentUser();
        if ($user === null) {
            return false;
        }

        if (($user['role'] ?? '') === 'student') {
            return (string) ($session['user_id'] ?? '') === (string) ($user['id'] ?? '');
        }

        return in_array($user['role'] ?? '', ['admin', 'super_admin'], true);
    }

    private function currentUser(): ?array
    {
        $id = $this->authContextService->currentUserId();
        if ($id === null) {
            return null;
        }

        $user = $this->db->fetchAssociative('SELECT id, role FROM users WHERE id = ? LIMIT 1', [$id]);
        if (!$user || !is_string($user['id'] ?? null) || !is_string($user['role'] ?? null)) {
            return null;
        }

        return ['id' => $user['id'], 'role' => $user['role']];
    }
}

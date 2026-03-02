<?php

declare(strict_types=1);

namespace App\Plugins\Student\Service;

use App\Core\Database\DatabaseManager;
use App\Core\Http\ApiResponse;
use App\Core\Service\AuditLogService;
use App\Domain\Exam\Service\SessionRecoveryService;
use App\Domain\Exam\Service\TimerService;
use App\Domain\Proctoring\Service\DeviceFingerprintService;
use App\Domain\Proctoring\Service\ProctoringService;
use App\Domain\Proctoring\Service\SessionIntegrityService;
use App\Plugins\Support\RouteAuthorizer;
use Exception;

final class StudentExamApiService
{
    public function overview(): void
    {
        RouteAuthorizer::authorize(['student', 'admin', 'super_admin'], static function (): void {
            try {
                $db = DatabaseManager::getConnection();
                $userId = $_GET['user_id'] ?? null;
                if (!$userId) {
                    ApiResponse::error('Missing user_id', 400)->send();
                    return;
                }

                $inProgress = $db->fetchAllAssociative(
                    "SELECT s.id, s.status, s.start_time, e.title, e.subject, e.duration_minutes
                     FROM exam_sessions s
                     INNER JOIN exam_templates e ON e.id = s.exam_template_id
                     WHERE s.user_id = ? AND s.status IN ('started', 'in_progress')
                     ORDER BY s.updated_at DESC
                     LIMIT 5",
                    [$userId]
                );

                $upcoming = $db->fetchAllAssociative(
                    "SELECT id, title, subject, duration_minutes, total_marks, start_time, end_time
                     FROM exam_templates ORDER BY start_time ASC LIMIT 12"
                );

                $history = $db->fetchAllAssociative(
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

    public function proctoringEvent(): void
    {
        RouteAuthorizer::authorize(['student', 'admin', 'super_admin'], function (): void {
            try {
                $input = json_decode((string) file_get_contents('php://input'), true);
                $token = $input['token'] ?? null;
                $eventType = $input['event_type'] ?? null;
                $payload = $input['payload'] ?? [];
                $severity = $input['severity'] ?? 'info';

                if (!$token || !$eventType) {
                    ApiResponse::error('Missing token or event_type', 400)->send();
                    return;
                }

                $service = $this->proctoringService();
                $sessionId = $service->getSessionIdByToken($token);
                if (!$sessionId) {
                    ApiResponse::error('Invalid session token', 403)->send();
                    return;
                }

                $service->logEvent($sessionId, $eventType, is_array($payload) ? $payload : [], (string) $severity);
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

                $service = $this->proctoringService();
                $sessionId = $service->getSessionIdByToken($token);
                if (!$sessionId) {
                    ApiResponse::error('Invalid session token', 403)->send();
                    return;
                }

                $service->logEvent($sessionId, 'heartbeat', [], 'info');
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

                $integrity = new SessionIntegrityService();
                if (!$integrity->validateSessionToken((string) $sessionId, (string) $token)) {
                    ApiResponse::error('Invalid session token', 403)->send();
                    return;
                }

                $service = new SessionRecoveryService(DatabaseManager::getConnection(), new TimerService());
                $result = $service->autosave(
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

                $integrity = new SessionIntegrityService();
                if (!$integrity->validateSessionToken((string) $sessionId, (string) $token)) {
                    ApiResponse::error('Invalid session token', 403)->send();
                    return;
                }

                $service = new SessionRecoveryService(DatabaseManager::getConnection(), new TimerService());
                ApiResponse::json($service->resumeState((string) $sessionId))->send();
            } catch (Exception $e) {
                ApiResponse::error($e->getMessage(), 500)->send();
            }
        });
    }

    private function proctoringService(): ProctoringService
    {
        return new ProctoringService(new AuditLogService(), new DeviceFingerprintService());
    }
}

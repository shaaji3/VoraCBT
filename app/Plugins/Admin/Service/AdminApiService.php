<?php

declare(strict_types=1);

namespace App\Plugins\Admin\Service;

use App\Core\Database\DatabaseManager;
use App\Core\Http\ApiResponse;
use App\Domain\Identity\CredentialExportService;
use App\Domain\Identity\StaffImportService;
use App\Domain\Identity\StudentImportService;
use App\Domain\Monitoring\Service\AdminLogViewerService;
use App\Plugins\Support\RouteAuthorizer;
use App\Plugins\Support\Service\AuthContextService;
use Exception;

final class AdminApiService
{
    public function __construct(
        private readonly StudentImportService $studentImportService,
        private readonly StaffImportService $staffImportService,
        private readonly CredentialExportService $credentialExportService,
        private readonly AdminLogViewerService $adminLogViewerService,
        private readonly AuthContextService $authContextService,
    ) {
    }


    public function dashboardOverview(): void
    {
        RouteAuthorizer::authorize(['admin', 'super_admin'], function (): void {
            try {
                $db = DatabaseManager::getConnection();
                $summary = [
                    'students' => (int) $db->fetchOne("SELECT COUNT(*) FROM users WHERE role = 'student'"),
                    'teachers' => (int) $db->fetchOne("SELECT COUNT(*) FROM users WHERE role IN ('teacher', 'staff')"),
                    'active_exams' => (int) $db->fetchOne("SELECT COUNT(*) FROM exam_sessions WHERE status IN ('started', 'in_progress')"),
                    'pending_manual_grading' => (int) $db->fetchOne("SELECT COUNT(*) FROM exam_session_answers WHERE marks_obtained IS NULL"),
                ];

                ApiResponse::json($summary)->send();
            } catch (Exception $e) {
                ApiResponse::error($e->getMessage(), 500)->send();
            }
        });
    }

    public function analyticsSummary(): void
    {
        RouteAuthorizer::authorize(['admin', 'super_admin'], function (): void {
            try {
                $db = DatabaseManager::getConnection();
                $rows = $db->fetchAllAssociative(
                    "SELECT status, COUNT(*) AS count
                     FROM exam_sessions
                     GROUP BY status"
                );

                ApiResponse::json(['sessions_by_status' => $rows])->send();
            } catch (Exception $e) {
                ApiResponse::error($e->getMessage(), 500)->send();
            }
        });
    }

    public function rolesSummary(): void
    {
        RouteAuthorizer::authorize(['admin', 'super_admin'], function (): void {
            try {
                $rows = DatabaseManager::getConnection()->fetchAllAssociative(
                    "SELECT role, COUNT(*) AS count FROM users GROUP BY role ORDER BY role ASC"
                );
                ApiResponse::json(['users_by_role' => $rows])->send();
            } catch (Exception $e) {
                ApiResponse::error($e->getMessage(), 500)->send();
            }
        });
    }

    public function questionsSummary(): void
    {
        RouteAuthorizer::authorize(['admin', 'super_admin'], function (): void {
            try {
                $db = DatabaseManager::getConnection();
                $total = (int) $db->fetchOne('SELECT COUNT(*) FROM questions WHERE archived_at IS NULL');
                $byType = $db->fetchAllAssociative(
                    'SELECT type, COUNT(*) AS count FROM questions WHERE archived_at IS NULL GROUP BY type ORDER BY type ASC'
                );

                ApiResponse::json(['total' => $total, 'by_type' => $byType])->send();
            } catch (Exception $e) {
                ApiResponse::error($e->getMessage(), 500)->send();
            }
        });
    }

    public function pendingGradingSummary(): void
    {
        RouteAuthorizer::authorize(['admin', 'super_admin'], function (): void {
            try {
                $rows = DatabaseManager::getConnection()->fetchAllAssociative(
                    "SELECT a.exam_session_id, COUNT(*) AS ungraded_answers
                     FROM exam_session_answers a
                     JOIN exam_sessions s ON s.id = a.exam_session_id
                     WHERE a.marks_obtained IS NULL
                       AND s.status IN ('submitted', 'completed')
                     GROUP BY a.exam_session_id
                     ORDER BY ungraded_answers DESC
                     LIMIT 50"
                );

                ApiResponse::json(['items' => $rows, 'count' => count($rows)])->send();
            } catch (Exception $e) {
                ApiResponse::error($e->getMessage(), 500)->send();
            }
        });
    }

    public function previewImport(): void
    {
        RouteAuthorizer::authorize(['admin', 'super_admin'], function (): void {
            try {
                if (!isset($_FILES['file'])) {
                    ApiResponse::error('No file uploaded', 400)->send();
                    return;
                }

                $file = $_FILES['file'];
                if (($file['size'] ?? 0) > 5 * 1024 * 1024 || ($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
                    ApiResponse::error('Invalid upload', 400)->send();
                    return;
                }

                $adminId = $this->authContextService->currentUserId();
                if (!$adminId) {
                    ApiResponse::error('Unauthorized', 401)->send();
                    return;
                }

                $tenantId = $this->tenantIdFor($adminId);
                $service = (($_POST['type'] ?? 'student') === 'staff') ? $this->staffImportService : $this->studentImportService;

                $service->setTenantId($tenantId);
                ApiResponse::json($service->preview((string) $file['tmp_name']))->send();
            } catch (Exception $e) {
                ApiResponse::error($e->getMessage(), 500)->send();
            }
        });
    }

    public function commitImport(): void
    {
        RouteAuthorizer::authorize(['admin', 'super_admin'], function (): void {
            try {
                if (!isset($_FILES['file'])) {
                    ApiResponse::error('No file uploaded', 400)->send();
                    return;
                }

                $file = $_FILES['file'];
                if (($file['size'] ?? 0) > 5 * 1024 * 1024 || ($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
                    ApiResponse::error('Invalid upload', 400)->send();
                    return;
                }

                $adminId = $this->authContextService->currentUserId();
                if (!$adminId) {
                    ApiResponse::error('Unauthorized', 401)->send();
                    return;
                }

                $tenantId = $this->tenantIdFor($adminId);
                $service = (($_POST['type'] ?? 'student') === 'staff') ? $this->staffImportService : $this->studentImportService;

                $service->setTenantId($tenantId);
                ApiResponse::json($service->commit((string) $file['tmp_name'], $adminId))->send();
            } catch (Exception $e) {
                ApiResponse::error($e->getMessage(), 500)->send();
            }
        });
    }

    public function exportCredentials(): void
    {
        RouteAuthorizer::authorize(['admin', 'super_admin'], function (): void {
            try {
                $filters = [
                    'type' => $_GET['type'] ?? 'student',
                    'class_id' => $_GET['class_id'] ?? null,
                    'import_id' => $_GET['import_id'] ?? null,
                    'student_ids' => isset($_GET['student_ids']) ? explode(',', (string) $_GET['student_ids']) : [],
                    'role' => $_GET['role'] ?? null,
                    'department' => $_GET['department'] ?? null,
                ];

                $format = $_GET['format'] ?? 'csv';
                $regenerate = filter_var($_GET['regenerate'] ?? false, FILTER_VALIDATE_BOOLEAN);
                $adminId = $this->authContextService->currentUserId();
                if (!$adminId) {
                    ApiResponse::error('Unauthorized', 401)->send();
                    return;
                }

                $content = $this->credentialExportService->export($filters, (string) $format, (bool) $regenerate, $adminId);

                if ($format === 'csv') {
                    header('Content-Type: text/csv');
                    header('Content-Disposition: attachment; filename="credentials.csv"');
                }

                echo $content;
            } catch (Exception $e) {
                ApiResponse::error($e->getMessage(), 500)->send();
            }
        });
    }

    public function fetchLogs(): void
    {
        RouteAuthorizer::authorize(['admin', 'super_admin'], function (): void {
            try {
                $filters = [
                    'from' => $_GET['from'] ?? null,
                    'to' => $_GET['to'] ?? null,
                    'user_id' => $_GET['user_id'] ?? null,
                    'exam_template_id' => $_GET['exam_template_id'] ?? null,
                    'event_source' => $_GET['event_source'] ?? null,
                    'limit' => $_GET['limit'] ?? 100,
                ];

                $logs = $this->adminLogViewerService->listLogs($filters);
                ApiResponse::json(['data' => $logs, 'count' => count($logs), 'filters' => $filters])->send();
            } catch (Exception $e) {
                ApiResponse::error($e->getMessage(), 500)->send();
            }
        });
    }

    private function tenantIdFor(string $adminId): int
    {
        $tenantId = DatabaseManager::getConnection()->fetchOne('SELECT tenant_id FROM users WHERE id = ?', [$adminId]);
        return $tenantId ? (int) $tenantId : 1;
    }
}

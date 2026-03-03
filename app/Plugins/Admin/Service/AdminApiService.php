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

                $conditions = [];
                $params = [];

                if (isset($_GET['from']) && is_string($_GET['from']) && $_GET['from'] !== '') {
                    $conditions[] = 'DATE(s.created_at) >= ?';
                    $params[] = $_GET['from'];
                }

                if (isset($_GET['to']) && is_string($_GET['to']) && $_GET['to'] !== '') {
                    $conditions[] = 'DATE(s.created_at) <= ?';
                    $params[] = $_GET['to'];
                }

                $whereClause = $conditions ? (' WHERE ' . implode(' AND ', $conditions)) : '';

                $rows = $db->fetchAllAssociative(
                    "SELECT s.status, COUNT(*) AS count
                     FROM exam_sessions s" . $whereClause . "
                     GROUP BY s.status",
                    $params
                );

                $aggregates = $db->fetchAssociative(
                    "SELECT AVG(CASE WHEN s.score IS NOT NULL AND t.total_score > 0 THEN (s.score / t.total_score) * 100 ELSE NULL END) AS average_score,
                            AVG(CASE WHEN t.passing_score IS NOT NULL AND s.score IS NOT NULL THEN CASE WHEN s.score >= t.passing_score THEN 100 ELSE 0 END ELSE NULL END) AS pass_rate
                     FROM exam_sessions s
                     JOIN exam_templates t ON t.id = s.exam_template_id" . $whereClause,
                    $params
                ) ?: [];

                ApiResponse::json([
                    'sessions_by_status' => $rows,
                    'average_score' => isset($aggregates['average_score']) ? round((float) $aggregates['average_score'], 2) : null,
                    'pass_rate' => isset($aggregates['pass_rate']) ? round((float) $aggregates['pass_rate'], 2) : null,
                ])->send();
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


    public function rolesPermissions(): void
    {
        RouteAuthorizer::authorize(['admin', 'super_admin'], function (): void {
            try {
                $db = DatabaseManager::getConnection();
                $roles = $db->fetchAllAssociative('SELECT id, name, slug FROM roles ORDER BY name ASC');
                $permissions = $db->fetchAllAssociative('SELECT id, name, slug FROM permissions ORDER BY name ASC');
                $pairs = $db->fetchAllAssociative('SELECT role_id, permission_id FROM role_permissions');

                $grants = [];
                foreach ($pairs as $pair) {
                    $roleId = (string) ($pair['role_id'] ?? '');
                    $permissionId = (string) ($pair['permission_id'] ?? '');
                    if ($roleId !== '' && $permissionId !== '') {
                        $grants[$roleId][] = $permissionId;
                    }
                }

                ApiResponse::json([
                    'roles' => $roles,
                    'permissions' => $permissions,
                    'grants' => $grants,
                ])->send();
            } catch (Exception $e) {
                ApiResponse::error($e->getMessage(), 500)->send();
            }
        });
    }

    public function saveRolesPermissions(): void
    {
        RouteAuthorizer::authorize(['admin', 'super_admin'], function (): void {
            try {
                $raw = json_decode((string) file_get_contents('php://input'), true);
                $grants = $raw['grants'] ?? null;
                if (!is_array($grants)) {
                    ApiResponse::error('Invalid grants payload', 422)->send();
                    return;
                }

                $db = DatabaseManager::getConnection();
                $db->beginTransaction();
                try {
                    foreach ($grants as $roleId => $permissionIds) {
                        if (!is_string($roleId) || $roleId === '' || !is_array($permissionIds)) {
                            continue;
                        }

                        $db->executeStatement('DELETE FROM role_permissions WHERE role_id = ?', [$roleId]);
                        foreach ($permissionIds as $permissionId) {
                            if (!is_string($permissionId) || $permissionId === '') {
                                continue;
                            }

                            $db->insert('role_permissions', [
                                'role_id' => $roleId,
                                'permission_id' => $permissionId,
                            ]);
                        }
                    }
                    $db->commit();
                } catch (Exception $e) {
                    $db->rollBack();
                    throw $e;
                }

                ApiResponse::json(['saved' => true])->send();
            } catch (Exception $e) {
                ApiResponse::error($e->getMessage(), 500)->send();
            }
        });
    }

    public function settings(): void
    {
        RouteAuthorizer::authorize(['admin', 'super_admin'], function (): void {
            try {
                $default = [
                    'platform_name' => 'VoraCBT',
                    'support_email' => 'support@example.com',
                    'session_timeout_minutes' => 120,
                    'allow_result_download' => true,
                ];

                $path = __DIR__ . '/../../../../storage/app/admin-settings.json';
                if (!is_file($path)) {
                    ApiResponse::json($default)->send();
                    return;
                }

                $json = json_decode((string) file_get_contents($path), true);
                ApiResponse::json(is_array($json) ? array_merge($default, $json) : $default)->send();
            } catch (Exception $e) {
                ApiResponse::error($e->getMessage(), 500)->send();
            }
        });
    }

    public function saveSettings(): void
    {
        RouteAuthorizer::authorize(['admin', 'super_admin'], function (): void {
            try {
                $raw = json_decode((string) file_get_contents('php://input'), true);
                if (!is_array($raw)) {
                    ApiResponse::error('Invalid settings payload', 422)->send();
                    return;
                }

                $payload = [
                    'platform_name' => (string) ($raw['platform_name'] ?? 'VoraCBT'),
                    'support_email' => (string) ($raw['support_email'] ?? 'support@example.com'),
                    'session_timeout_minutes' => max(15, (int) ($raw['session_timeout_minutes'] ?? 120)),
                    'allow_result_download' => (bool) ($raw['allow_result_download'] ?? true),
                ];

                $path = __DIR__ . '/../../../../storage/app/admin-settings.json';
                file_put_contents($path, (string) json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

                ApiResponse::json(['saved' => true, 'settings' => $payload])->send();
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

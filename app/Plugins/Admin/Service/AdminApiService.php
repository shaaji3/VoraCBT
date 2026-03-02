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
        private readonly ?StudentImportService $studentImportService = null,
        private readonly ?StaffImportService $staffImportService = null,
        private readonly ?CredentialExportService $credentialExportService = null,
        private readonly ?AdminLogViewerService $adminLogViewerService = null,
        private readonly ?AuthContextService $authContextService = null,
    ) {
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

                $adminId = ($this->authContextService ?? new AuthContextService())->currentUserId();
                if (!$adminId) {
                    ApiResponse::error('Unauthorized', 401)->send();
                    return;
                }

                $tenantId = $this->tenantIdFor($adminId);
                $type = $_POST['type'] ?? 'student';

                $service = $type === 'staff'
                    ? ($this->staffImportService ?? new StaffImportService())
                    : ($this->studentImportService ?? new StudentImportService());

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

                $adminId = ($this->authContextService ?? new AuthContextService())->currentUserId();
                if (!$adminId) {
                    ApiResponse::error('Unauthorized', 401)->send();
                    return;
                }

                $tenantId = $this->tenantIdFor($adminId);
                $type = $_POST['type'] ?? 'student';
                $service = $type === 'staff'
                    ? ($this->staffImportService ?? new StaffImportService())
                    : ($this->studentImportService ?? new StudentImportService());

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
                $adminId = ($this->authContextService ?? new AuthContextService())->currentUserId();
                if (!$adminId) {
                    ApiResponse::error('Unauthorized', 401)->send();
                    return;
                }

                $content = ($this->credentialExportService ?? new CredentialExportService())
                    ->export($filters, (string) $format, (bool) $regenerate, $adminId);

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

                $service = $this->adminLogViewerService ?? new AdminLogViewerService(DatabaseManager::getConnection());
                $logs = $service->listLogs($filters);

                ApiResponse::json([
                    'data' => $logs,
                    'count' => count($logs),
                    'filters' => $filters,
                ])->send();
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

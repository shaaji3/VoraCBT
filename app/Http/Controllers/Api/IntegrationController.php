<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Core\Http\ApiResponse;
use App\Integration\Service\ModeAwareIdentityService;
use App\Integration\Service\ResultPackageService;
use App\Plugins\Support\Service\AuthContextService;
use Throwable;

final class IntegrationController
{
    private const ALLOWED_JWT_ROLES = ['admin', 'super_admin', 'integration_service'];

    public function __construct(
        private readonly ?ModeAwareIdentityService $identityService = null,
        private readonly ?ResultPackageService $resultPackageService = null,
        private readonly ?AuthContextService $authContextService = null,
    ) {
    }

    public function students(): void
    {
        if (!$this->authorizeIntegrationRequest()) {
            return;
        }

        try {
            $result = ($this->identityService ?? new ModeAwareIdentityService())
                ->syncIntegratedStudents($_GET['class_id'] ?? null, $_GET['session'] ?? null);
            ApiResponse::json($result)->send();
        } catch (Throwable $e) {
            ApiResponse::error($e->getMessage(), 400)->send();
        }
    }

    public function staff(): void
    {
        if (!$this->authorizeIntegrationRequest()) {
            return;
        }

        try {
            $result = ($this->identityService ?? new ModeAwareIdentityService())
                ->syncIntegratedStaff($_GET['role'] ?? null);
            ApiResponse::json($result)->send();
        } catch (Throwable $e) {
            ApiResponse::error($e->getMessage(), 400)->send();
        }
    }

    public function syncStudents(): void
    {
        if (!$this->authorizeIntegrationRequest()) {
            return;
        }

        try {
            $data = $this->readJsonBody();
            $result = ($this->identityService ?? new ModeAwareIdentityService())
                ->syncStandaloneStudents($data['students'] ?? []);
            ApiResponse::json($result)->send();
        } catch (Throwable $e) {
            ApiResponse::error($e->getMessage(), 400)->send();
        }
    }

    public function syncStaff(): void
    {
        if (!$this->authorizeIntegrationRequest()) {
            return;
        }

        try {
            $data = $this->readJsonBody();
            $result = ($this->identityService ?? new ModeAwareIdentityService())
                ->syncStandaloneStaff($data['staff'] ?? []);
            ApiResponse::json($result)->send();
        } catch (Throwable $e) {
            ApiResponse::error($e->getMessage(), 400)->send();
        }
    }

    public function resultsImport(): void
    {
        if (!$this->authorizeIntegrationRequest()) {
            return;
        }

        try {
            $data = $this->readJsonBody();
            $signature = (string) ($data['signature'] ?? '');
            if ($signature === '') {
                ApiResponse::error('signature is required', 422)->send();
                return;
            }
            unset($data['signature']);

            $actor = ($this->authContextService ?? new AuthContextService())->currentUserId();
            $result = ($this->resultPackageService ?? new ResultPackageService())->acceptImportedPackage($data, $signature, $actor);

            if (($result['status'] ?? '') === 'rejected_replay') {
                ApiResponse::error('Replay detected for idempotency_key', 409)->send();
                return;
            }

            if (($result['is_valid_signature'] ?? false) !== true) {
                ApiResponse::error('Invalid signature', 422)->send();
                return;
            }

            ApiResponse::json($result, 202)->send();
        } catch (Throwable $e) {
            ApiResponse::error($e->getMessage(), 400)->send();
        }
    }

    private function readJsonBody(): array
    {
        $raw = (string) file_get_contents('php://input');
        if ($raw === '') {
            return [];
        }

        return json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    }

    private function authorizeIntegrationRequest(): bool
    {
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $integrationKey = $headers['X-Integration-Key'] ?? $headers['x-integration-key'] ?? '';
        $expectedKey = (string) ($_ENV['INTEGRATION_SHARED_KEY'] ?? '');

        if ($expectedKey !== '' && hash_equals($expectedKey, (string) $integrationKey)) {
            return true;
        }

        $ctx = $this->authContextService ?? new AuthContextService();
        $userId = $ctx->currentUserId();
        $role = $ctx->currentRole();
        if ($userId !== null && in_array((string) $role, self::ALLOWED_JWT_ROLES, true)) {
            return true;
        }

        ApiResponse::error('Unauthorized', 401)->send();
        return false;
    }
}

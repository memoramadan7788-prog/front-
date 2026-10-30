<?php

namespace App\Models;

use App\Frontend\Api\CurlClient;
use App\Frontend\Auth\ApiSession;

class AdminAPI
{
    public $api = null;

    public function __construct()
    {
        $this->api = new CurlClient(
            baseUrl: config('services.backend.url'),
            defaults: ['token_key' => ApiSession::token() ?? '']
        );
    }

    /**
     * POST /api/admin/login — platform Admin (Admin model) only.
     * Never log plaintext passwords.
     *
     * @param  array{email: string, password: string}  $data
     * @return array{message: string, data: mixed, errors: array<string, mixed>|null, statusCode: int}
     */
    public function login(array $data): array
    {
        $payload = [
            'email' => (string) ($data['email'] ?? ''),
            'password' => (string) ($data['password'] ?? ''),
        ];

        $res = $this->api->reset()->method('POST')
            ->url('/admin/login')
            ->body($payload)
            ->send();

        unset($payload, $data);

        if ($res->ok()) {
            return [
                'message' => $res->message ?: 'Login successful.',
                'data' => is_array($res->data) ? $res->data : null,
                'errors' => null,
                'statusCode' => 200,
            ];
        }

        return [
            'message' => $res->message ?: "HTTP {$res->statusCode}",
            'data' => is_array($res->data) ? $res->data : null,
            'errors' => is_array($res->errors) ? $res->errors : null,
            'statusCode' => (int) $res->statusCode,
        ];
    }

    /**
     * GET /api/admin/me — platform Admin identity (not GET /auth/me).
     *
     * @return array{message: string, data: mixed, errors: array<string, mixed>|null, statusCode: int}
     */
    public function me(): array
    {
        $res = $this->api->reset()->method('GET')
            ->url('/admin/me')
            ->bearer(ApiSession::token() ?? '')
            ->send();

        if ($res->ok()) {
            return [
                'message' => $res->message ?: 'OK',
                'data' => $res->data,
                'errors' => null,
                'statusCode' => 200,
            ];
        }

        return [
            'message' => $res->message ?: "HTTP {$res->statusCode}",
            'data' => null,
            'errors' => is_array($res->errors) ? $res->errors : null,
            'statusCode' => (int) $res->statusCode,
        ];
    }

    /**
     * POST /api/admin/logout — revokes current platform Admin Sanctum token.
     *
     * @return array{message: string, data: mixed, errors: array<string, mixed>|null, statusCode: int}
     */
    public function logout(): array
    {
        $res = $this->api->reset()->method('POST')
            ->url('/admin/logout')
            ->bearer(ApiSession::token() ?? '')
            ->send();

        if ($res->ok()) {
            return [
                'message' => $res->message ?: 'Logout successful.',
                'data' => $res->data,
                'errors' => null,
                'statusCode' => 200,
            ];
        }

        return [
            'message' => $res->message ?: "HTTP {$res->statusCode}",
            'data' => null,
            'errors' => is_array($res->errors) ? $res->errors : null,
            'statusCode' => (int) $res->statusCode,
        ];
    }

    public function getCompanies($filters = [])
    {
        $message = '';
        $statusCode = 200;
        $query = http_build_query($filters);

        $res = $this->api->reset()->method('GET')
            ->url('/admin/companies' . ($query ? '?' . $query : ''))
            ->bearer(ApiSession::token() ?? '')->send();

        if ($res->ok()) {
            $message = $res->message;
            $statusCode = 200; // any 2xx (the backend answers 201 for creates) counts as success
        } else {
            $message = "Error: {$res->message}\n";
            $statusCode = $res->statusCode;
        }

        return ["message" => $message, "data" => $res->data, "meta" => $res->pagination(), "statusCode" => $statusCode];
    }

    /**
     * GET /api/admin/companies/{id} — single company (no company_id list filter on Backend).
     *
     * @return array{message: string, data: mixed, meta: mixed, statusCode: int}
     */
    public function getCompany(int|string $companyId): array
    {
        return $this->call('GET', '/admin/companies/'.$companyId);
    }

    public function approveCompany($companyId)
    {
        $message = '';
        $statusCode = 200;

        $res = $this->api->reset()->method('POST')
            ->url("/admin/companies/{$companyId}/approve")
            ->bearer(ApiSession::token() ?? '')->send();

        if ($res->ok()) {
            $message = $res->message;
            $statusCode = 200; // any 2xx (the backend answers 201 for creates) counts as success
        } else {
            $message = "Error: {$res->message}\n";
            $statusCode = $res->statusCode;
        }

        return ["message" => $message, "data" => $res->data, "meta" => $res->pagination(), "statusCode" => $statusCode];
    }

    public function rejectCompany($companyId, $data = [])
    {
        $message = '';
        $statusCode = 200;

        $res = $this->api->reset()->method('POST')
            ->url("/admin/companies/{$companyId}/reject")
            ->bearer(ApiSession::token() ?? '')
            ->body($data)->send();

        if ($res->ok()) {
            $message = $res->message;
            $statusCode = 200; // any 2xx (the backend answers 201 for creates) counts as success
        } else {
            $message = "Error: {$res->message}\n";
            $statusCode = $res->statusCode;
        }

        return ["message" => $message, "data" => $res->data, "meta" => $res->pagination(), "statusCode" => $statusCode];
    }

    public function getUsers($filters = [])
    {
        $message = '';
        $statusCode = 200;
        $query = http_build_query($filters);

        $res = $this->api->reset()->method('GET')
            ->url('/admin/users' . ($query ? '?' . $query : ''))
            ->bearer(ApiSession::token() ?? '')->send();

        if ($res->ok()) {
            $message = $res->message;
            $statusCode = 200; // any 2xx (the backend answers 201 for creates) counts as success
        } else {
            $message = "Error: {$res->message}\n";
            $statusCode = $res->statusCode;
        }

        return ["message" => $message, "data" => $res->data, "meta" => $res->pagination(), "statusCode" => $statusCode];
    }

    public function updateUserStatus($userId, $data)
    {
        $message = '';
        $statusCode = 200;

        $res = $this->api->reset()->method('PATCH')
            ->url("/admin/users/{$userId}/status")
            ->bearer(ApiSession::token() ?? '')
            ->body($data)->send();

        if ($res->ok()) {
            $message = $res->message;
            $statusCode = 200; // any 2xx (the backend answers 201 for creates) counts as success
        } else {
            $message = "Error: {$res->message}\n";
            $statusCode = $res->statusCode;
        }

        return ["message" => $message, "data" => $res->data, "meta" => $res->pagination(), "statusCode" => $statusCode];
    }

    public function getAuditLogs($filters = [])
    {
        $message = '';
        $statusCode = 200;
        $query = http_build_query($filters);

        $res = $this->api->reset()->method('GET')
            ->url('/admin/audit-logs' . ($query ? '?' . $query : ''))
            ->bearer(ApiSession::token() ?? '')->send();

        if ($res->ok()) {
            $message = $res->message;
            $statusCode = 200; // any 2xx (the backend answers 201 for creates) counts as success
        } else {
            $message = "Error: {$res->message}\n";
            $statusCode = $res->statusCode;
        }

        return ["message" => $message, "data" => $res->data, "meta" => $res->pagination(), "statusCode" => $statusCode];
    }

    /** GET /api/admin/roles — read-only role catalog (Phase 1). */
    public function getRoles()
    {
        return $this->call('GET', '/admin/roles');
    }

    /**
     * GET /api/admin/dashboard — Super Admin + reports.view.
     *
     * @return array{message: string, data: mixed, meta: mixed, statusCode: int, errors?: mixed}
     */
    public function getDashboard(): array
    {
        return $this->call('GET', '/admin/dashboard');
    }

    /**
     * GET /api/admin/analytics?months= — Super Admin + reports.view.
     * Backend clamps months to 3–12 (default 6).
     *
     * @param  array{months?: int}  $query
     * @return array{message: string, data: mixed, meta: mixed, statusCode: int, errors?: mixed}
     */
    public function getAnalytics(array $query = []): array
    {
        $months = isset($query['months']) ? (int) $query['months'] : null;
        $params = [];
        if ($months !== null) {
            $params['months'] = min(max($months, 3), 12);
        }
        $qs = $params !== [] ? ('?'.http_build_query($params)) : '';

        return $this->call('GET', '/admin/analytics'.$qs);
    }

    public function getUser($userId)
    {
        return $this->call('GET', "/admin/users/{$userId}");
    }

    public function suspendCompany($companyId, $data = [])
    {
        return $this->call('POST', "/admin/companies/{$companyId}/suspend", $data);
    }

    /** Small helper for the newer calls (same return shape as the methods above). */
    private function call(string $method, string $url, ?array $body = null): array
    {
        $req = $this->api->reset()->method($method)->url($url)->bearer(ApiSession::token() ?? '');
        if ($body !== null) {
            $req->body($body);
        }
        $res = $req->send();

        return [
            'message'    => $res->ok() ? $res->message : "Error: {$res->message}\n",
            'data'       => $res->data,
            'meta'       => $res->pagination(),
            'statusCode' => $res->ok() ? 200 : $res->statusCode,
        ];
    }

    /** Normalised result used by the single-company calls. */
    private function companyResult($res): array
    {
        return [
            'message'    => $res->ok() ? $res->message : "Error: {$res->message}\n",
            'data'       => $res->data,
            'errors'     => $res->errors ?? [],
            'statusCode' => $res->ok() ? 200 : $res->statusCode,
        ];
    }

    /**
     * GET /api/admin/companies/{id}/details — profile + documents payload.
     * Distinct from getCompany() which hits management show (/admin/companies/{id}).
     *
     * @return array{message: string, data: mixed, errors: array, statusCode: int}
     */
    public function getCompanyDetails($companyId): array
    {
        $res = $this->api->reset()->method('GET')
            ->url("/admin/companies/{$companyId}/details")
            ->bearer(ApiSession::token() ?? '')->send();

        return $this->companyResult($res);
    }

    public function updateCompany($companyId, $data)
    {
        $res = $this->api->reset()->method('PUT')
            ->url("/admin/companies/{$companyId}")
            ->bearer(ApiSession::token() ?? '')
            ->body($data)->send();

        return $this->companyResult($res);
    }

    public function uploadCompanyDocument($companyId, string $path, string $name, string $mime, array $fields = [])
    {
        $res = $this->api->reset()->method('POST')
            ->url("/admin/companies/{$companyId}/documents")
            ->bearer(ApiSession::token() ?? '')
            ->form($fields)
            ->file('file', $path, $mime, $name)
            ->send();

        return $this->companyResult($res);
    }

    public function downloadCompanyDocument($companyId, $documentId)
    {
        $res = $this->api->reset()->method('GET')
            ->url("/admin/companies/{$companyId}/documents/{$documentId}/download")
            ->bearer(ApiSession::token() ?? '')->send();

        return $this->companyResult($res);
    }

    public function deleteCompanyDocument($companyId, $documentId)
    {
        $res = $this->api->reset()->method('DELETE')
            ->url("/admin/companies/{$companyId}/documents/{$documentId}")
            ->bearer(ApiSession::token() ?? '')->send();

        return $this->companyResult($res);
    }
}

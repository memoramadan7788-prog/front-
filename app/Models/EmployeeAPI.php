<?php

namespace App\Models;

use App\Frontend\Api\CurlClient;
use App\Frontend\Auth\ApiSession;

/**
 * Company employee / team client (Mission 08).
 *
 * Members: GET|POST approve|reject|DELETE /company/employees…
 * Roles: GET /company/roles ; PUT /company/employees/{id}/role
 * Invitations: GET|POST|DELETE /company/invitations (requires company_invitations table / migration 000009)
 */
class EmployeeAPI
{
    public $api = null;

    public function __construct()
    {
        $this->api = new CurlClient(
            baseUrl: config('services.backend.url'),
            defaults: ['token_key' => ApiSession::token() ?? '']
        );
    }

    public function requestToJoin($data)
    {
        $res = $this->api->reset()->method('POST')
            ->url('/company/join-requests')
            ->bearer(ApiSession::token() ?? '')
            ->body($data)->send();

        return $this->result($res);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{message: mixed, data: mixed, errors: mixed, statusCode: int}
     */
    public function getEmployees($filters = [])
    {
        $query = http_build_query(array_filter($filters, static fn ($v) => $v !== null && $v !== ''));

        $res = $this->api->reset()->method('GET')
            ->url('/company/employees'.($query ? '?'.$query : ''))
            ->bearer(ApiSession::token() ?? '')->send();

        return $this->result($res);
    }

    /**
     * @param  array<string, mixed>  $data  must include role_id (Backend ApproveEmployeeRequest)
     * @return array{message: mixed, data: mixed, errors: mixed, statusCode: int}
     */
    public function approveEmployee($employeeId, array $data = [])
    {
        $res = $this->api->reset()->method('POST')
            ->url("/company/employees/{$employeeId}/approve")
            ->bearer(ApiSession::token() ?? '')
            ->body($data)
            ->send();

        return $this->result($res);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: mixed, data: mixed, errors: mixed, statusCode: int}
     */
    public function rejectEmployee($employeeId, $data = [])
    {
        $res = $this->api->reset()->method('POST')
            ->url("/company/employees/{$employeeId}/reject")
            ->bearer(ApiSession::token() ?? '')
            ->body($data)->send();

        return $this->result($res);
    }

    /**
     * @return array{message: mixed, data: mixed, errors: mixed, statusCode: int}
     */
    public function removeEmployee($employeeId)
    {
        $res = $this->api->reset()->method('DELETE')
            ->url("/company/employees/{$employeeId}")
            ->bearer(ApiSession::token() ?? '')->send();

        return $this->result($res);
    }

    /**
     * GET /company/roles — company-scoped role catalog.
     *
     * @return array{message: mixed, data: mixed, errors: mixed, statusCode: int}
     */
    public function getCompanyRoles(): array
    {
        $res = $this->api->reset()->method('GET')
            ->url('/company/roles')
            ->bearer(ApiSession::token() ?? '')
            ->send();

        return $this->result($res);
    }

    /**
     * PUT /company/employees/{id}/role
     *
     * @param  array{role_id: int}  $data
     * @return array{message: mixed, data: mixed, errors: mixed, statusCode: int}
     */
    public function updateEmployeeRole(int $employeeId, array $data): array
    {
        $res = $this->api->reset()->method('PUT')
            ->url("/company/employees/{$employeeId}/role")
            ->bearer(ApiSession::token() ?? '')
            ->body($data)
            ->send();

        return $this->result($res);
    }

    /**
     * GET /company/invitations
     *
     * @param  array<string, mixed>  $filters
     * @return array{message: mixed, data: mixed, errors: mixed, statusCode: int}
     */
    public function getInvitations(array $filters = []): array
    {
        $query = http_build_query(array_filter($filters, static fn ($v) => $v !== null && $v !== ''));

        $res = $this->api->reset()->method('GET')
            ->url('/company/invitations'.($query ? '?'.$query : ''))
            ->bearer(ApiSession::token() ?? '')
            ->send();

        return $this->result($res);
    }

    /**
     * POST /company/invitations — creates invitation **record** only (no email send in Backend).
     *
     * @param  array<string, mixed>  $data
     * @return array{message: mixed, data: mixed, errors: mixed, statusCode: int}
     */
    public function createInvitation(array $data): array
    {
        $res = $this->api->reset()->method('POST')
            ->url('/company/invitations')
            ->bearer(ApiSession::token() ?? '')
            ->body($data)
            ->send();

        return $this->result($res);
    }

    /**
     * DELETE /company/invitations/{id} — revoke/delete record (no resend endpoint exists).
     *
     * @return array{message: mixed, data: mixed, errors: mixed, statusCode: int}
     */
    public function deleteInvitation(int $invitationId): array
    {
        $res = $this->api->reset()->method('DELETE')
            ->url("/company/invitations/{$invitationId}")
            ->bearer(ApiSession::token() ?? '')
            ->send();

        return $this->result($res);
    }

    /**
     * @return array{message: mixed, data: mixed, errors: mixed, statusCode: int, meta: ?array}
     */
    private function result(object $res): array
    {
        $meta = null;
        if (method_exists($res, 'pagination')) {
            $meta = $res->pagination();
        } elseif (isset($res->meta) && is_array($res->meta)) {
            $meta = $res->meta['pagination'] ?? $res->meta;
        }

        if ($res->ok()) {
            return [
                'message' => $res->message,
                'data' => $res->data,
                'errors' => null,
                'statusCode' => (int) ($res->statusCode ?: 200),
                'meta' => is_array($meta) ? $meta : null,
            ];
        }

        return [
            'message' => $res->message ?: "HTTP {$res->statusCode}",
            'data' => $res->data,
            'errors' => $res->errors ?? null,
            'statusCode' => (int) $res->statusCode,
            'meta' => null,
        ];
    }
}

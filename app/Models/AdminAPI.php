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

    public function getDashboard()
    {
        return $this->call('GET', '/admin/dashboard');
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
}

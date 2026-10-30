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

    public function login($data)
    {
        // No bearer here: the admin has no token yet.
        $res = $this->api->reset()->method('POST')
            ->url('/admin/login')
            ->body($data)->send();

        return $this->companyResult($res);
    }

    public function me()
    {
        return $this->call('GET', '/admin/me');
    }

    public function logout()
    {
        $result = $this->call('POST', '/admin/logout');
        ApiSession::forget();

        return $result;
    }

    /** GET /api/admin/analytics?months=N — dashboard trends & recent activity. */
    public function getAnalytics($filters = [])
    {
        $query = http_build_query($filters);

        return $this->call('GET', '/admin/analytics'.($query ? '?'.$query : ''));
    }

    public function getCompanies($filters = [])
    {
        $message = '';
        $statusCode = 200;
        $query = http_build_query($filters);

        $res = $this->api->reset()->method('GET')
            ->url('/admin/companies'.($query ? '?'.$query : ''))
            ->bearer(ApiSession::token() ?? '')->send();

        if ($res->ok()) {
            $message = $res->message;
            $statusCode = 200; // any 2xx (the backend answers 201 for creates) counts as success
        } else {
            $message = "Error: {$res->message}\n";
            $statusCode = $res->statusCode;
        }

        return ['message' => $message, 'data' => $res->data, 'meta' => $res->pagination(), 'statusCode' => $statusCode];
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

        return ['message' => $message, 'data' => $res->data, 'meta' => $res->pagination(), 'statusCode' => $statusCode];
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

        return ['message' => $message, 'data' => $res->data, 'meta' => $res->pagination(), 'statusCode' => $statusCode];
    }

    public function getUsers($filters = [])
    {
        $message = '';
        $statusCode = 200;
        $query = http_build_query($filters);

        $res = $this->api->reset()->method('GET')
            ->url('/admin/users'.($query ? '?'.$query : ''))
            ->bearer(ApiSession::token() ?? '')->send();

        if ($res->ok()) {
            $message = $res->message;
            $statusCode = 200; // any 2xx (the backend answers 201 for creates) counts as success
        } else {
            $message = "Error: {$res->message}\n";
            $statusCode = $res->statusCode;
        }

        return ['message' => $message, 'data' => $res->data, 'meta' => $res->pagination(), 'statusCode' => $statusCode];
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

        return ['message' => $message, 'data' => $res->data, 'meta' => $res->pagination(), 'statusCode' => $statusCode];
    }

    public function getAuditLogs($filters = [])
    {
        $message = '';
        $statusCode = 200;
        $query = http_build_query($filters);

        $res = $this->api->reset()->method('GET')
            ->url('/admin/audit-logs'.($query ? '?'.$query : ''))
            ->bearer(ApiSession::token() ?? '')->send();

        if ($res->ok()) {
            $message = $res->message;
            $statusCode = 200; // any 2xx (the backend answers 201 for creates) counts as success
        } else {
            $message = "Error: {$res->message}\n";
            $statusCode = $res->statusCode;
        }

        return ['message' => $message, 'data' => $res->data, 'meta' => $res->pagination(), 'statusCode' => $statusCode];
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
            'message' => $res->ok() ? $res->message : "Error: {$res->message}\n",
            'data' => $res->data,
            'meta' => $res->pagination(),
            'statusCode' => $res->ok() ? 200 : $res->statusCode,
        ];
    }

    /** Normalised result used by the single-company calls. */
    private function companyResult($res): array
    {
        return [
            'message' => $res->ok() ? $res->message : "Error: {$res->message}\n",
            'data' => $res->data,
            'errors' => $res->errors ?? [],
            'statusCode' => $res->ok() ? 200 : $res->statusCode,
        ];
    }

    public function getCompanyDetails($companyId): array


    {
        $res = $this->api->reset()->method('GET')
            ->url("/admin/companies/{$companyId}/details")
            ->bearer(ApiSession::token() ?? '')->send();

        return $this->companyResult($res);
    }

    public function updateCompanyDetails($companyId, $data)
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
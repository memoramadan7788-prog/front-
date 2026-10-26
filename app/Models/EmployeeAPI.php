<?php

namespace App\Models;

use App\Frontend\Api\CurlClient;
use App\Frontend\Auth\ApiSession;

/**
 * Company employee onboarding / join-request client.
 *
 * Candidate-facing: POST /company/join-requests
 * Company-facing: employees list/approve/reject/remove (permission-gated on Backend)
 *
 * No Candidate-accessible company directory endpoint exists; do not invent one.
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
        $message = '';
        $statusCode = 200;
        $errors = null;

        $res = $this->api->reset()->method('POST')
            ->url('/company/join-requests')
            ->bearer(ApiSession::token() ?? '')
            ->body($data)->send();

        if ($res->ok()) {
            $message = $res->message;
            $statusCode = 200; // any 2xx (Backend answers 201 for creates)
        } else {
            $message = $res->message ?: "HTTP {$res->statusCode}";
            $statusCode = $res->statusCode;
            $errors = $res->errors;
        }

        return [
            'message' => $message,
            'data' => $res->data,
            'errors' => $errors,
            'statusCode' => $statusCode,
        ];
    }

    public function getEmployees($filters = [])
    {
        $message = '';
        $statusCode = 200;
        $query = http_build_query($filters);

        $res = $this->api->reset()->method('GET')
            ->url('/company/employees' . ($query ? '?' . $query : ''))
            ->bearer(ApiSession::token() ?? '')->send();

        if ($res->ok()) {
            $message = $res->message;
            $statusCode = 200; // any 2xx (the backend answers 201 for creates) counts as success
        } else {
            $message = "Error: {$res->message}\n";
            $statusCode = $res->statusCode;
        }

        return ["message" => $message, "data" => $res->data, "statusCode" => $statusCode];
    }

    public function approveEmployee($employeeId)
    {
        $message = '';
        $statusCode = 200;

        $res = $this->api->reset()->method('POST')
            ->url("/company/employees/{$employeeId}/approve")
            ->bearer(ApiSession::token() ?? '')->send();

        if ($res->ok()) {
            $message = $res->message;
            $statusCode = 200; // any 2xx (the backend answers 201 for creates) counts as success
        } else {
            $message = "Error: {$res->message}\n";
            $statusCode = $res->statusCode;
        }

        return ["message" => $message, "data" => $res->data, "statusCode" => $statusCode];
    }

    public function rejectEmployee($employeeId, $data = [])
    {
        $message = '';
        $statusCode = 200;

        $res = $this->api->reset()->method('POST')
            ->url("/company/employees/{$employeeId}/reject")
            ->bearer(ApiSession::token() ?? '')
            ->body($data)->send();

        if ($res->ok()) {
            $message = $res->message;
            $statusCode = 200; // any 2xx (the backend answers 201 for creates) counts as success
        } else {
            $message = "Error: {$res->message}\n";
            $statusCode = $res->statusCode;
        }

        return ["message" => $message, "data" => $res->data, "statusCode" => $statusCode];
    }

    public function removeEmployee($employeeId)
    {
        $message = '';
        $statusCode = 200;

        $res = $this->api->reset()->method('DELETE')
            ->url("/company/employees/{$employeeId}")
            ->bearer(ApiSession::token() ?? '')->send();

        if ($res->ok()) {
            $message = $res->message;
            $statusCode = 200; // any 2xx (the backend answers 201 for creates) counts as success
        } else {
            $message = "Error: {$res->message}\n";
            $statusCode = $res->statusCode;
        }

        return ["message" => $message, "data" => $res->data, "statusCode" => $statusCode];
    }
}
<?php

namespace App\Models;

use App\Frontend\Api\CurlClient;
use App\Frontend\Auth\ApiSession;

class CompanyAPI
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
        $message = '';
        $statusCode = 200;

        $res = $this->api->reset()->method('POST')
            ->url('/auth/login')->body($data)->send();

        if ($res->ok()) {
            $message = $res->message;
            $statusCode = 200; // any 2xx (the backend answers 201 for creates) counts as success
        } else {
            $message = "Error: {$res->message}\n";
            $statusCode = $res->statusCode;
        }

        return ['message' => $message, 'data' => $res->data, 'statusCode' => $statusCode];
    }

    public function register($data)
    {
        $message = '';
        $statusCode = 200;

        $res = $this->api->reset()->method('POST')
            ->url('/auth/register/company')->body($data)->send();

        if ($res->ok()) {
            $message = $res->message;
            $statusCode = 200; // any 2xx (the backend answers 201 for creates) counts as success
        } else {
            $message = "Error: {$res->message}\n";
            $statusCode = $res->statusCode;
        }

        return ['message' => $message, 'data' => $res->data, 'statusCode' => $statusCode];
    }

    public function getProfile()
    {
        $res = $this->api->reset()->method('GET')
            ->url('/company/profile')
            ->bearer(ApiSession::token() ?? '')->send();

        return [
            'message' => $res->ok() ? $res->message : "Error: {$res->message}\n",
            'data' => $res->data,
            'errors' => $res->errors ?? [],
            'statusCode' => $res->ok() ? 200 : $res->statusCode,
        ];
    }

    /**
     * Public company profile by id (no login required) — used by /companies/{id}.
     * ASSUMPTION: the backend exposes GET /companies/{id}. If the real endpoint is
     * different (e.g. /public/companies/{id}), change only the URL below.
     */
    public function getPublicProfile(int $companyId): array
    {
        $res = $this->api->reset()->method('GET')
            ->url("/companies/{$companyId}")
            ->send();

        return $this->profileResult($res);
    }

    public function updateProfile($data)
    {
        $message = '';
        $statusCode = 200;

        $res = $this->api->reset()->method('PUT')
            ->url('/company/profile')
            ->bearer(ApiSession::token() ?? '')
            ->body($data)->send();

        if ($res->ok()) {
            $message = $res->message;
            $statusCode = 200; // any 2xx (the backend answers 201 for creates) counts as success
        } else {
            $message = "Error: {$res->message}\n";
            $statusCode = $res->statusCode;
        }

        return ['message' => $message, 'data' => $res->data, 'errors' => $res->errors ?? [], 'statusCode' => $statusCode];
    }

    public function logout()
    {
        $message = '';
        $statusCode = 200;

        $res = $this->api->reset()->method('POST')
            ->url('/auth/logout')
            ->bearer(ApiSession::token() ?? '')->send();

        if ($res->ok()) {
            $message = $res->message;
            $statusCode = 200; // any 2xx (the backend answers 201 for creates) counts as success
            ApiSession::forget();
        } else {
            $message = "Error: {$res->message}\n";
            $statusCode = $res->statusCode;
        }

        return ['message' => $message, 'data' => $res->data, 'statusCode' => $statusCode];
    }

    public function resetPassword($data)
    {
        $message = '';
        $statusCode = 200;

        $res = $this->api->reset()->method('POST')
            ->url('/company/reset-password')->body($data)->send();

        if ($res->ok()) {
            $message = $res->message;
            $statusCode = 200; // any 2xx (the backend answers 201 for creates) counts as success
        } else {
            $message = "Error: {$res->message}\n";
            $statusCode = $res->statusCode;
        }

        return ['message' => $message, 'data' => $res->data, 'statusCode' => $statusCode];
    }

    public function changePassword($data)
    {
        $message = '';
        $statusCode = 200;

        $res = $this->api->reset()->method('POST')
            ->url('/company/change-password')
            ->bearer(ApiSession::token() ?? '')
            ->body($data)->send();

        if ($res->ok()) {
            $message = $res->message;
            $statusCode = 200; // any 2xx (the backend answers 201 for creates) counts as success
        } else {
            $message = "Error: {$res->message}\n";
            $statusCode = $res->statusCode;
        }

        return ['message' => $message, 'data' => $res->data, 'statusCode' => $statusCode];
    }

    public function deleteAccount()
    {
        $message = '';
        $statusCode = 200;

        $res = $this->api->reset()->method('DELETE')
            ->url('/company/delete-account')
            ->bearer(ApiSession::token() ?? '')->send();

        if ($res->ok()) {
            $message = $res->message;
            $statusCode = 200; // any 2xx (the backend answers 201 for creates) counts as success
            ApiSession::forget();
        } else {
            $message = "Error: {$res->message}\n";
            $statusCode = $res->statusCode;
        }

        return ['message' => $message, 'data' => $res->data, 'statusCode' => $statusCode];
    }

    public function getVacancies(?int $page = null): array
    {
        $message = '';
        $statusCode = 200;

        $res = $this->api->reset()->method('GET')
            ->url('/company/jobs')
            ->query($page === null ? [] : ['page' => $page])
            ->bearer(ApiSession::token() ?? '')->send();

        if ($res->ok()) {
            $message = $res->message;
            $statusCode = 200; // any 2xx (the backend answers 201 for creates) counts as success
        } else {
            $message = "Error: {$res->message}\n";
            $statusCode = $res->statusCode;
        }

        return [
            'message' => $message,
            'data' => $res->data,
            'meta' => $res->pagination(),
            'statusCode' => $statusCode,
        ];
    }

    /** The backend asks for company_id on create: it is the company of the logged-in user. */
    private function companyId(): ?int
    {
        $id = data_get(ApiSession::user(), 'company.id');

        if (! $id) {
            // session saved before the company was known (e.g. right after register): ask the backend once
            $me = $this->api->reset()->method('GET')->url('/auth/me')->bearer(ApiSession::token() ?? '')->send();
            $id = data_get($me->data, 'company.id');

            if ($id && ApiSession::user()) {
                $user = ApiSession::user();
                $user['company'] = data_get($me->data, 'company');
                ApiSession::store(ApiSession::token(), $user);
            }
        }

        return $id ? (int) $id : null;
    }

    public function createVacancy($data)
    {
        $message = '';
        $statusCode = 200;
        $data += ['company_id' => $this->companyId()];

        $res = $this->api->reset()->method('POST')
            ->url('/company/jobs')
            ->bearer(ApiSession::token() ?? '')
            ->body($data)->send();

        if ($res->ok()) {
            $message = $res->message;
            $statusCode = 200; // any 2xx (the backend answers 201 for creates) counts as success
        } else {
            $message = "Error: {$res->message}\n";
            $statusCode = $res->statusCode;
        }

        return ['message' => $message, 'data' => $res->data, 'statusCode' => $statusCode];
    }

    public function updateVacancy($vacancyId, $data)
    {
        $message = '';
        $statusCode = 200;

        $res = $this->api->reset()->method('PUT')
            ->url("/company/jobs/{$vacancyId}")
            ->bearer(ApiSession::token() ?? '')
            ->body($data)->send();

        if ($res->ok()) {
            $message = $res->message;
            $statusCode = 200; // any 2xx (the backend answers 201 for creates) counts as success
        } else {
            $message = "Error: {$res->message}\n";
            $statusCode = $res->statusCode;
        }

        return ['message' => $message, 'data' => $res->data, 'statusCode' => $statusCode];
    }

    public function deleteVacancy($vacancyId)
    {
        $message = '';
        $statusCode = 200;

        $res = $this->api->reset()->method('DELETE')
            ->url("/company/jobs/{$vacancyId}")
            ->bearer(ApiSession::token() ?? '')->send();

        if ($res->ok()) {
            $message = $res->message;
            $statusCode = 200; // any 2xx (the backend answers 201 for creates) counts as success
        } else {
            $message = "Error: {$res->message}\n";
            $statusCode = $res->statusCode;
        }

        return ['message' => $message, 'data' => $res->data, 'statusCode' => $statusCode];
    }

    public function getApplications($vacancyId)
    {
        $message = '';
        $statusCode = 200;

        $res = $this->api->reset()->method('GET')
            ->url("/company/jobs/{$vacancyId}/applications")
            ->bearer(ApiSession::token() ?? '')->send();

        if ($res->ok()) {
            $message = $res->message;
            $statusCode = 200; // any 2xx (the backend answers 201 for creates) counts as success
        } else {
            $message = "Error: {$res->message}\n";
            $statusCode = $res->statusCode;
        }

        return ['message' => $message, 'data' => $res->data, 'statusCode' => $statusCode];
    }

    public function updateApplicationStatus($applicationId, $data)
    {
        $message = '';
        $statusCode = 200;

        $res = $this->api->reset()->method('PATCH')
            ->url("/company/applications/{$applicationId}/status")
            ->bearer(ApiSession::token() ?? '')
            ->body($data)->send();

        if ($res->ok()) {
            $message = $res->message;
            $statusCode = 200; // any 2xx (the backend answers 201 for creates) counts as success
        } else {
            $message = "Error: {$res->message}\n";
            $statusCode = $res->statusCode;
        }

        return ['message' => $message, 'data' => $res->data, 'statusCode' => $statusCode];
    }

    public function createExam($data)
    {
        $message = '';
        $statusCode = 200;
        $data += ['company_id' => $this->companyId()];

        $res = $this->api->reset()->method('POST')
            ->url('/company/exams')
            ->bearer(ApiSession::token() ?? '')
            ->body($data)->send();

        if ($res->ok()) {
            $message = $res->message;
            $statusCode = 200; // any 2xx (the backend answers 201 for creates) counts as success
        } else {
            $message = "Error: {$res->message}\n";
            $statusCode = $res->statusCode;
        }

        return ['message' => $message, 'data' => $res->data, 'statusCode' => $statusCode];
    }

    public function createOffer($data)
    {
        $message = '';
        $statusCode = 200;

        $res = $this->api->reset()->method('POST')
            ->url('/company/offers')
            ->bearer(ApiSession::token() ?? '')
            ->body($data)->send();

        if ($res->ok()) {
            $message = $res->message;
            $statusCode = 200; // any 2xx (the backend answers 201 for creates) counts as success
        } else {
            $message = "Error: {$res->message}\n";
            $statusCode = $res->statusCode;
        }

        return ['message' => $message, 'data' => $res->data, 'statusCode' => $statusCode];
    }

    /** Normalised result used by the company profile / documents calls. */
    private function profileResult($res): array
    {
        return [
            'message' => $res->ok() ? $res->message : "Error: {$res->message}\n",
            'data' => $res->data,
            'errors' => $res->errors ?? [],
            'statusCode' => $res->ok() ? 200 : $res->statusCode,
        ];
    }

    public function uploadDocument(string $path, string $name, string $mime, array $fields = [])
    {
        $res = $this->api->reset()->method('POST')
            ->url('/company/documents')
            ->bearer(ApiSession::token() ?? '')
            ->form($fields)
            ->file('file', $path, $mime, $name)
            ->send();

        return $this->profileResult($res);
    }

    public function downloadDocument($documentId)
    {
        $res = $this->api->reset()->method('GET')
            ->url("/company/documents/{$documentId}/download")
            ->bearer(ApiSession::token() ?? '')->send();

        return $this->profileResult($res);
    }

    public function deleteDocument($documentId)
    {
        $res = $this->api->reset()->method('DELETE')
            ->url("/company/documents/{$documentId}")
            ->bearer(ApiSession::token() ?? '')->send();

        return $this->profileResult($res);
    }

    /**
     * GET /company/applications/{id} — company-scoped application detail.
     */
    public function getApplication(int $applicationId): array
    {
        $res = $this->api->reset()->method('GET')
            ->url("/company/applications/{$applicationId}")
            ->bearer(ApiSession::token() ?? '')
            ->send();

        return $this->interviewResult($res);
    }

    /**
     * GET /company/applications/{id}/available-slots
     *
     * @param  array<string, mixed>  $filters
     */
    public function getApplicationAvailableSlots(int $applicationId, array $filters = []): array
    {
        $query = http_build_query(array_filter($filters, static fn ($v) => $v !== null && $v !== ''));
        $res = $this->api->reset()->method('GET')
            ->url("/company/applications/{$applicationId}/available-slots".($query ? "?{$query}" : ''))
            ->bearer(ApiSession::token() ?? '')
            ->send();

        return $this->interviewResult($res);
    }

    /**
     * POST /company/interviews/check-conflicts
     *
     * @param  array<string, mixed>  $data
     */
    public function checkInterviewConflicts(array $data): array
    {
        $res = $this->api->reset()->method('POST')
            ->url('/company/interviews/check-conflicts')
            ->bearer(ApiSession::token() ?? '')
            ->body($data)
            ->send();

        return $this->interviewResult($res);
    }

    /**
     * POST /interviews — HR schedule with availability bookings.
     *
     * @param  array<string, mixed>  $data
     */
    public function scheduleInterview(array $data): array
    {
        $res = $this->api->reset()->method('POST')
            ->url('/interviews')
            ->bearer(ApiSession::token() ?? '')
            ->body($data)
            ->send();

        return $this->interviewResult($res);
    }

    private function interviewResult(object $res): array
    {
        if ($res->ok()) {
            return [
                'message' => $res->message,
                'data' => $res->data,
                'errors' => null,
                'statusCode' => (int) ($res->statusCode ?: 200),
            ];
        }

        return [
            'message' => $res->message ?: "HTTP {$res->statusCode}",
            'data' => $res->data,
            'errors' => $res->errors ?? null,
            'statusCode' => (int) $res->statusCode,
        ];
    }
}
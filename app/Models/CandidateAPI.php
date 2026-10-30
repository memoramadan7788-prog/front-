<?php

namespace App\Models;

use App\Frontend\Api\CurlClient;
use App\Frontend\Auth\ApiSession;

class CandidateAPI
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

        return ["message" => $message, "data" => $res->data, "statusCode" => $statusCode];
    }

    public function register($data)
    {
        $message = '';
        $statusCode = 200;

        $res = $this->api->reset()->method('POST')
            ->url('/auth/register/candidate')->body($data)->send();

        if ($res->ok()) {
            $message = $res->message;
            $statusCode = 200; // any 2xx (the backend answers 201 for creates) counts as success
        } else {
            $message = "Error: {$res->message}\n";
            $statusCode = $res->statusCode;
        }

        return ["message" => $message, "data" => $res->data, "statusCode" => $statusCode];
    }

    public function getProfile()
    {
        $message = '';
        $statusCode = 200;

        $res = $this->api->reset()->method('GET')
            ->url('/candidate/profile')
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

    public function updateProfile($data)
    {
        $message = '';
        $statusCode = 200;

        $res = $this->api->reset()->method('PUT')
            ->url('/candidate/profile')
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

        return ["message" => $message, "data" => $res->data, "statusCode" => $statusCode];
    }

    // TODO(backend): no reset-password endpoint for candidates yet
    public function resetPassword($data)
    {
        $message = '';
        $statusCode = 200;

        $res = $this->api->reset()->method('POST')
            ->url('/candidates/reset-password')->body($data)->send();

        if ($res->ok()) {
            $message = $res->message;
            $statusCode = 200; // any 2xx (the backend answers 201 for creates) counts as success
        } else {
            $message = "Error: {$res->message}\n";
            $statusCode = $res->statusCode;
        }

        return ["message" => $message, "data" => $res->data, "statusCode" => $statusCode];
    }

    /**
     * PUT /api/candidate/change-password
     * Payload keys: current_password, new_password, new_password_confirmation.
     * Never log or persist plaintext passwords from $data.
     *
     * @param  array{current_password: string, new_password: string, new_password_confirmation: string}  $data
     * @return array{message: string, data: mixed, statusCode: int, errors: array<string, mixed>|null}
     */
    public function changePassword(array $data): array
    {
        $payload = [
            'current_password' => (string) ($data['current_password'] ?? ''),
            'new_password' => (string) ($data['new_password'] ?? ''),
            'new_password_confirmation' => (string) ($data['new_password_confirmation'] ?? ''),
        ];

        $res = $this->api->reset()->method('PUT')
            ->url('/candidate/change-password')
            ->bearer(ApiSession::token() ?? '')
            ->body($payload)
            ->send();

        // Drop local plaintext copies promptly (request body already sent).
        unset($payload, $data);

        if ($res->ok()) {
            return [
                'message' => $res->message ?: 'Password changed successfully.',
                'data' => $res->data,
                'statusCode' => 200,
                'errors' => null,
            ];
        }

        return [
            'message' => $res->message ?: "HTTP {$res->statusCode}",
            'data' => $res->data,
            'statusCode' => (int) $res->statusCode,
            'errors' => is_array($res->errors) ? $res->errors : null,
        ];
    }

    // TODO(backend): no delete-account endpoint yet
    public function deleteAccount()
    {
        $message = '';
        $statusCode = 200;

        $res = $this->api->reset()->method('DELETE')
            ->url('/candidates/delete-account')
            ->bearer(ApiSession::token() ?? '')->send();

        if ($res->ok()) {
            $message = $res->message;
            $statusCode = 200; // any 2xx (the backend answers 201 for creates) counts as success
            ApiSession::forget();
        } else {
            $message = "Error: {$res->message}\n";
            $statusCode = $res->statusCode;
        }

        return ["message" => $message, "data" => $res->data, "statusCode" => $statusCode];
    }

    public function uploadResume($filePath, ?string $originalName = null, ?string $mime = null)
    {
        $message = '';
        $statusCode = 200;

        $res = $this->api->reset()->method('POST')
            ->url('/candidate/cvs')
            ->bearer(ApiSession::token() ?? '')
            ->file('cv', $filePath, $mime, $originalName)->send();

        if ($res->ok()) {
            $message = $res->message;
            $statusCode = 200; // any 2xx (the backend answers 201 for creates) counts as success
        } else {
            $message = "Error: {$res->message}\n";
            $statusCode = $res->statusCode;
        }

        return ["message" => $message, "data" => $res->data, "statusCode" => $statusCode];
    }

    // TODO(backend): no CV download endpoint yet (GET /candidate/cvs only lists)
    public function downloadResume($resumeId)
    {
        $message = '';
        $statusCode = 200;

        $res = $this->api->reset()->method('GET')
            ->url("/candidates/download-resume/{$resumeId}")
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

    public function deleteResume($resumeId)
    {
        $message = '';
        $statusCode = 200;

        $res = $this->api->reset()->method('DELETE')
            ->url("/candidate/cvs/{$resumeId}")
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

    public function getVacancies()
    {
        $message = '';
        $statusCode = 200;

        $res = $this->api->reset()->method('GET')
            ->url('/jobs')
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

    public function applyForVacancy($vacancyId, $data = [])
    {
        $message = '';
        $statusCode = 200;

        $res = $this->api->reset()->method('POST')
            ->url("/jobs/{$vacancyId}/apply")
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

    public function getApplications()
    {
        $message = '';
        $statusCode = 200;

        $res = $this->api->reset()->method('GET')
            ->url('/candidate/applications')
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

    public function getApplication($applicationId)
    {
        $message = '';
        $statusCode = 200;

        $res = $this->api->reset()->method('GET')
            ->url("/candidate/applications/{$applicationId}")
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

    public function addEducation($data)
    {
        $message = '';
        $statusCode = 200;

        $res = $this->api->reset()->method('POST')
            ->url('/candidate/educations')
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

    public function addExperience($data)
    {
        $message = '';
        $statusCode = 200;

        $res = $this->api->reset()->method('POST')
            ->url('/candidate/experiences')
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

    /**
     * GET /api/candidate/skills — assigned skills for the authenticated candidate.
     *
     * @return array{message: string, data: mixed, errors: array<string, mixed>|null, statusCode: int}
     */
    public function getSkills(): array
    {
        $res = $this->api->reset()->method('GET')
            ->url('/candidate/skills')
            ->bearer(ApiSession::token() ?? '')
            ->send();

        if ($res->ok()) {
            $rows = is_array($res->data) ? $res->data : [];
            if (isset($rows['data']) && is_array($rows['data'])) {
                $rows = $rows['data'];
            }

            return [
                'message' => $res->message ?: 'OK',
                'data' => array_values(array_filter(
                    is_array($rows) && array_is_list($rows) ? $rows : [],
                    static fn ($row) => is_array($row)
                )),
                'errors' => null,
                'statusCode' => 200,
            ];
        }

        return [
            'message' => $res->message ?: "HTTP {$res->statusCode}",
            'data' => [],
            'errors' => is_array($res->errors) ? $res->errors : null,
            'statusCode' => (int) $res->statusCode,
        ];
    }

    /**
     * POST /api/candidate/skills — full sync replace (Backend uses belongsToMany sync).
     *
     * @param  list<array{skill_id: int, level?: string|null}|int>  $skills
     * @return array{message: string, data: mixed, errors: array<string, mixed>|null, statusCode: int}
     */
    public function syncSkills(array $skills): array
    {
        $res = $this->api->reset()->method('POST')
            ->url('/candidate/skills')
            ->bearer(ApiSession::token() ?? '')
            ->body(['skills' => array_values($skills)])
            ->send();

        if ($res->ok()) {
            $rows = is_array($res->data) ? $res->data : [];
            if (isset($rows['data']) && is_array($rows['data'])) {
                $rows = $rows['data'];
            }

            return [
                'message' => $res->message ?: 'Candidate skills updated successfully.',
                'data' => array_values(array_filter(
                    is_array($rows) && array_is_list($rows) ? $rows : [],
                    static fn ($row) => is_array($row)
                )),
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
     * @deprecated Prefer syncSkills(); kept for older callers — merges one skill via full sync.
     * @param  array{skill_id?: int, level?: string|null}  $data
     */
    public function addSkill(array $data): array
    {
        $skillId = (int) ($data['skill_id'] ?? 0);
        if ($skillId < 1) {
            return [
                'message' => 'Invalid skill.',
                'data' => null,
                'errors' => ['skill_id' => ['A valid skill is required.']],
                'statusCode' => 422,
            ];
        }

        $current = $this->getSkills();
        if ((int) ($current['statusCode'] ?? 500) !== 200) {
            return $current;
        }

        $payload = [];
        $replaced = false;
        foreach ((array) ($current['data'] ?? []) as $row) {
            if (! is_array($row)) {
                continue;
            }
            $id = (int) (data_get($row, 'id') ?? data_get($row, 'skill_id') ?? 0);
            if ($id < 1) {
                continue;
            }
            if ($id === $skillId) {
                $payload[] = [
                    'skill_id' => $id,
                    'level' => $data['level'] ?? data_get($row, 'level'),
                ];
                $replaced = true;
            } else {
                $payload[] = [
                    'skill_id' => $id,
                    'level' => data_get($row, 'level'),
                ];
            }
        }

        if (! $replaced) {
            $payload[] = [
                'skill_id' => $skillId,
                'level' => $data['level'] ?? null,
            ];
        }

        return $this->syncSkills($payload);
    }

    // TODO(backend): no candidate exams list yet - use ExamAPI
    public function getExams()
    {
        $message = '';
        $statusCode = 200;

        $res = $this->api->reset()->method('GET')
            ->url('/candidates/exams')
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

    // TODO(backend): exams are submitted per attempt - use ExamAPI::submitExam
    public function submitExam($examId, $data)
    {
        $message = '';
        $statusCode = 200;

        $res = $this->api->reset()->method('POST')
            ->url("/candidates/exams/{$examId}/submit")
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

    public function getInterviews()
    {
        $message = '';
        $statusCode = 200;

        $res = $this->api->reset()->method('GET')
            ->url('/candidate/interviews')
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
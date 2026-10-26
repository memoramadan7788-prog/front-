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

    // TODO(backend): no change-password endpoint yet
    public function changePassword($data)
    {
        $message = '';
        $statusCode = 200;

        $res = $this->api->reset()->method('POST')
            ->url('/candidates/change-password')
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

    public function uploadResume($filePath)
    {
        $message = '';
        $statusCode = 200;

        $res = $this->api->reset()->method('POST')
            ->url('/candidate/cvs')
            ->bearer(ApiSession::token() ?? '')
            ->file('cv', $filePath)->send();

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

    // TODO(backend): no candidate skills endpoint yet
    public function addSkill($data)
    {
        $message = '';
        $statusCode = 200;

        $res = $this->api->reset()->method('POST')
            ->url('/candidates/skills')
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
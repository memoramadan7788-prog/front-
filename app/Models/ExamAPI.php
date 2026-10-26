<?php

namespace App\Models;

use App\Frontend\Api\CurlClient;
use App\Frontend\Auth\ApiSession;

class ExamAPI
{
    public $api = null;

    public function __construct()
    {
        $this->api = new CurlClient(
            baseUrl: config('services.backend.url'),
            defaults: ['token_key' => ApiSession::token() ?? '']
        );
    }

    public function getCandidateExams()
    {
        // TODO(backend): there is no "my exams" endpoint yet. An exam is started from an application:
        // POST /candidate/exams/{examId}/applications/{applicationId}/start. Until it exists the list is empty.
        return ["message" => '', "data" => [], "statusCode" => 200];
    }

    /** $data['application_id'] is part of the URL; camera_consent goes in the body. */
    public function startExam($examId, $data = [])
    {
        $message = '';
        $statusCode = 200;
        $applicationId = (int) ($data['application_id'] ?? 0);
        unset($data['application_id']);

        $res = $this->api->reset()->method('POST')
            ->url("/candidate/exams/{$examId}/applications/{$applicationId}/start")
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

    /** $attemptId = the id of the attempt returned by startExam (data.attempt.id) */
    public function submitExam($attemptId, $data)
    {
        $message = '';
        $statusCode = 200;

        $res = $this->api->reset()->method('POST')
            ->url("/candidate/exam-attempts/{$attemptId}/submit")
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

    public function getExamResult($attemptId)
    {
        $message = '';
        $statusCode = 200;

        $res = $this->api->reset()->method('GET')
            ->url("/candidate/exam-attempts/{$attemptId}/result")
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

    public function createExam($data)
    {
        $message = '';
        $statusCode = 200;

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

        return ["message" => $message, "data" => $res->data, "statusCode" => $statusCode];
    }

    public function addQuestion($examId, $data)
    {
        $message = '';
        $statusCode = 200;

        $res = $this->api->reset()->method('POST')
            ->url("/company/exams/{$examId}/questions")
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
}
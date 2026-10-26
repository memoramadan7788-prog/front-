<?php

namespace App\Frontend\Exam;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

use App\Models\ExamAPI;
use App\Support\Frontend\ApiListPage;

class ExamController extends Controller
{
    private $api;

    public function __construct(ExamAPI $api)
    {
        $this->api = $api;
    }

    function getCandidateExams(Request $request)
    {
        // ExamAPI::getCandidateExams documents that Backend has no candidate exam-list
        // endpoint yet; it returns an authentic empty 200 (not a hidden failure).
        return ApiListPage::render(
            $this->api->getCandidateExams(),
            'frontend.exam.exams',
            'exams',
            ['assessmentsListStubbed' => true],
        );
    }

    function startExam(Request $request, $examId)
    {
        if ($request->isMethod('post')) {

            $fields = $request->validate([
                'camera_consent' => 'required|accepted',
            ]);

            // the backend starts an exam from an application: /candidate/exams/{exam}/applications/{application}/start
            $request->validate(['application_id' => 'required|integer']);

            $data = $request->only(['camera_consent', 'application_id']);

            $response = $this->api->startExam($examId, $data);

            if ($response['statusCode'] === 200) {
                // Exam start successful, show the questions of the new attempt (submit/result use the attempt id)
                return view('frontend.exam.take', [
                    'exam' => $response['data'],
                    'examId' => $examId,
                    'attemptId' => data_get($response['data'], 'attempt.id'),
                ]);
            } else {
                // Exam start failed, return the error message
                return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']])->withInput();
            }
        }

        return view('frontend.exam.intro', get_defined_vars());
    }

    function submitExam(Request $request, $attemptId)
    {
        if ($request->isMethod('post')) {

            $fields = $request->validate([
                'answers' => 'required|array',
            ]);

            $data = $request->only(['answers']);

            $response = $this->api->submitExam($attemptId, $data);

            if ($response['statusCode'] === 200) {
                // Exam submission successful, you can perform additional actions here if needed
                return redirect()->route('frontend.candidate.exams.result', ['attemptId' => $attemptId])->with('success_key', 'flash.exam.submitted');
            } else {
                // Exam submission failed, return the error message
                return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']])->withInput();
            }
        }

        return view('frontend.exam.take', get_defined_vars());
    }

    function getExamResult(Request $request, $attemptId)
    {
        $response = $this->api->getExamResult($attemptId);

        if ($response['statusCode'] === 200) {
            // Exam result retrieval successful, you can perform additional actions here if needed
            return view('frontend.exam.result', ['result' => $response['data']]);
        } else {
            // Exam result retrieval failed, return the error message
            return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']]);
        }
    }

    function createExam(Request $request)
    {
        if ($request->isMethod('post')) {

            $fields = $request->validate([
                'title' => 'required|string|max:255',
                'time_limit_min' => 'required|integer|min:1',
                'attempt_limit' => 'required|integer|min:1|max:3',
                'passing_score' => 'required|numeric|min:0|max:100',
            ]);

            $data = $request->only(['title', 'time_limit_min', 'attempt_limit', 'passing_score']);

            $response = $this->api->createExam($data);

            if ($response['statusCode'] === 200) {
                // Exam creation successful, you can perform additional actions here if needed
                return redirect()->back()->with('success_key', 'flash.exam.created');
            } else {
                // Exam creation failed, return the error message
                return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']])->withInput();
            }
        }

        return view('frontend.exam.create', get_defined_vars());
    }

    function addQuestion(Request $request, $examId)
    {
        if ($request->isMethod('post')) {

            $fields = $request->validate([
                'question_type' => 'required|string|in:mcq,written,coding',
                'content' => 'required|string',
                'points' => 'required|numeric|min:0',
                'options' => 'nullable|array',
            ]);

            $data = $request->only(['question_type', 'content', 'points', 'options']);

            $response = $this->api->addQuestion($examId, $data);

            if ($response['statusCode'] === 200) {
                // Question creation successful, you can perform additional actions here if needed
                return redirect()->back()->with('success_key', 'flash.exam.question_added');
            } else {
                // Question creation failed, return the error message
                return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']])->withInput();
            }
        }

        return view('frontend.exam.add-question', get_defined_vars());
    }
}

<?php

namespace App\Frontend\Interview;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

use App\Models\InterviewAPI;
use App\Support\Frontend\ApiListPage;

class InterviewController extends Controller
{
    private $api;

    public function __construct(InterviewAPI $api)
    {
        $this->api = $api;
    }

    function getInterviews(Request $request)
    {
        return ApiListPage::render(
            $this->api->getInterviews(),
            'frontend.interview.interviews',
            'interviews',
        );
    }

    function getAvailableSlots(Request $request)
    {
        $filters = $request->query();

        $response = $this->api->getAvailableSlots($filters);

        if ($response['statusCode'] === 200) {
            // Available slots retrieval successful, you can perform additional actions here if needed
            return view('frontend.interview.slots', ['slots' => $response['data']]);
        } else {
            // Available slots retrieval failed, return the error message
            return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']]);
        }
    }

    function bookInterview(Request $request)
    {
        if ($request->isMethod('post')) {

            $fields = $request->validate([
                'application_id' => 'required|integer',
                'availability_id' => 'required|integer',
            ]);

            $data = $request->only(['application_id', 'availability_id']);

            $response = $this->api->bookInterview($data);

            if ($response['statusCode'] === 200) {
                // Interview booking successful, you can perform additional actions here if needed
                return redirect()->back()->with('success_key', 'flash.interview.booked');
            } else {
                // Interview booking failed, return the error message
                return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']])->withInput();
            }
        }

        return view('frontend.interview.book', get_defined_vars());
    }

    function rescheduleInterview(Request $request, $interviewId)
    {
        if ($request->isMethod('post')) {

            $fields = $request->validate([
                'availability_id' => 'required|integer',
            ]);

            $data = $request->only(['availability_id']);

            $response = $this->api->rescheduleInterview($interviewId, $data);

            if ($response['statusCode'] === 200) {
                // Interview rescheduling successful, you can perform additional actions here if needed
                return redirect()->back()->with('success_key', 'flash.interview.rescheduled');
            } else {
                // Interview rescheduling failed, return the error message
                return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']])->withInput();
            }
        }

        return view('frontend.interview.reschedule', get_defined_vars());
    }

    function cancelInterview(Request $request, $interviewId)
    {
        $response = $this->api->cancelInterview($interviewId);

        if ($response['statusCode'] === 200) {
            // Interview cancellation successful, you can perform additional actions here if needed
            return redirect()->back()->with('success_key', 'flash.interview.cancelled');
        } else {
            // Interview cancellation failed, return the error message
            return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']]);
        }
    }

    function submitEvaluation(Request $request, $interviewId)
    {
        if ($request->isMethod('post')) {

            $fields = $request->validate([
                'numeric_score' => 'required|numeric|min:0|max:100',
                'internal_notes' => 'required|string|min:3',
                'candidate_feedback' => 'nullable|string',
            ]);

            $data = $request->only(['numeric_score', 'internal_notes', 'candidate_feedback']);

            $response = $this->api->submitEvaluation($interviewId, $data);

            if ($response['statusCode'] === 200) {
                // Evaluation submission successful, you can perform additional actions here if needed
                return redirect()->back()->with('success_key', 'flash.interview.evaluation_submitted');
            } else {
                // Evaluation submission failed, return the error message
                return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']])->withInput();
            }
        }

        return view('frontend.interview.evaluation', get_defined_vars());
    }
}

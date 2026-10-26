<?php

namespace App\Frontend\Interviewer;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

use App\Models\InterviewerAPI;

class InterviewerController extends Controller
{
    private $api = null;

    public function __construct()
    {
        $this->api = new InterviewerAPI();
    }

    function getInterviews(Request $request)
    {
        $response = $this->api->getInterviews();

        if ($response['statusCode'] === 200) {
            // Interviews retrieval successful, you can perform additional actions here if needed
            return view('frontend.interviewer.interviews', ['interviews' => $response['data']]);
        } else {
            // Interviews retrieval failed, return the error message
            return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']]);
        }
    }

    function getInterview(Request $request, $interviewId)
    {
        $response = $this->api->getInterview($interviewId);

        if ($response['statusCode'] === 200) {
            // Interview retrieval successful, you can perform additional actions here if needed
            return view('frontend.interviewer.interview-details', ['interview' => $response['data'], 'interviewId' => $interviewId]);
        } else {
            // Interview retrieval failed, return the error message
            return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']]);
        }
    }

    function addAvailability(Request $request)
    {
        if ($request->isMethod('post')) {

            $fields = $request->validate([
                'start_at' => 'required|date|after:now',
                'end_at' => 'required|date|after:start_at',
            ]);

            $data = [
                'start_at' => Carbon::parse($fields['start_at'])->format('Y-m-d H:i:s'),
                'end_at' => Carbon::parse($fields['end_at'])->format('Y-m-d H:i:s'),
            ];

            $response = $this->api->addAvailability($data);

            if ($response['statusCode'] === 200) {
                // Availability added successfully, you can perform additional actions here if needed
                return redirect()->back()->with('success_key', 'flash.interviewer.slot_added');
            } else {
                // Adding availability failed, return the error message
                return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']])->withInput();
            }
        }

        return view('frontend.interviewer.availability', get_defined_vars());
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
                return redirect()->route('frontend.interviewer.interviews.show', ['interviewId' => $interviewId])->with('success_key', 'flash.interviewer.evaluation_submitted');
            } else {
                // Evaluation submission failed, return the error message
                return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']])->withInput();
            }
        }

        return view('frontend.interviewer.evaluation', get_defined_vars());
    }
}

<?php

namespace App\Frontend\Interviewer;

use App\Frontend\Auth\ApiSession;
use App\Models\InterviewerAPI;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class InterviewerController extends Controller
{
    public function __construct(private readonly InterviewerAPI $api) {}

    public function getInterviews(Request $request)
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

    public function getInterview(Request $request, $interviewId)
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

    public function addAvailability(Request $request)
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

    public function submitEvaluation(Request $request, $interviewId)
    {
        $interviewResponse = $this->api->getInterview($interviewId);
        $interviewStatus = (int) ($interviewResponse['statusCode'] ?? 500);

        if ($interviewStatus === 404) {
            abort(404);
        }

        if ($interviewStatus === 401) {
            ApiSession::forget();

            return redirect()->route('frontend.account.login')
                ->withErrors(['error' => __('Your session has expired. Please sign in again.')]);
        }

        if ($interviewStatus !== 200) {
            return redirect()->route('frontend.interviewer.interviews.index')
                ->withErrors(['error' => __('Could not verify this interview or your access to it.')]);
        }

        $interview = $interviewResponse['data'] ?? null;

        if (! is_array($interview)) {
            return redirect()->route('frontend.interviewer.interviews.index')
                ->withErrors(['error' => __('Could not verify this interview or your access to it.')]);
        }

        if ($request->isMethod('post')) {
            $competencies = ['technical', 'communication', 'problem_solving', 'collaboration', 'role_fit'];
            $rules = [
                'strengths' => ['required', 'string', 'min:3', 'max:5000'],
                'concerns' => ['required', 'string', 'min:3', 'max:5000'],
                'recommendation' => ['required', 'in:strong_yes,yes,maybe,no'],
                'candidate_feedback' => ['nullable', 'string', 'max:5000'],
            ];

            foreach ($competencies as $competency) {
                $rules["ratings.{$competency}"] = ['required', 'in:1,2,3,4,5,no'];
            }

            $validator = Validator::make($request->all(), $rules);
            $validator->after(function ($validator) use ($request, $competencies): void {
                $ratings = $request->input('ratings', []);
                $hasNumericRating = is_array($ratings)
                    && collect($competencies)->contains(fn ($key) => is_numeric($ratings[$key] ?? null));

                if (! $hasNumericRating) {
                    $validator->errors()->add('ratings', __('Rate at least one competency from 1 to 5.'));
                }
            });
            $fields = $validator->validate();

            $ratingLines = collect($competencies)->map(function ($competency) use ($fields): string {
                return str($competency)->replace('_', ' ')->headline().': '.$fields['ratings'][$competency];
            });
            $internalNotes = implode("\n", [
                __('Competency ratings'),
                ...$ratingLines->all(),
                '',
                __('Strengths').': '.$fields['strengths'],
                __('Concerns').': '.$fields['concerns'],
                __('Overall recommendation').': '.str($fields['recommendation'])->replace('_', ' ')->headline(),
            ]);
            $numericRatings = collect($fields['ratings'])->filter(fn ($rating) => is_numeric($rating));
            $data = [
                'numeric_score' => (int) round($numericRatings->avg() * 20),
                'internal_notes' => $internalNotes,
                'candidate_feedback' => $fields['candidate_feedback'] ?? null,
            ];

            $response = $this->api->submitEvaluation($interviewId, $data);

            if ((int) ($response['statusCode'] ?? 500) === 200) {
                return redirect()->route('frontend.interviewer.interviews.evaluation', ['interviewId' => $interviewId])
                    ->with('success_key', 'flash.interviewer.evaluation_submitted')
                    ->with('clear_scorecard_draft', true);
            } else {
                return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']])->withInput();
            }
        }

        return view('frontend.interviewer.evaluation', compact('interviewId', 'interview'));
    }
}

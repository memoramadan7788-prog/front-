<?php

namespace App\Frontend\Interviewer;

use App\Frontend\Auth\ApiSession;
use App\Models\InterviewerAPI;
use App\Support\Frontend\SafeApiMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class InterviewerController extends Controller
{
    public function __construct(private readonly InterviewerAPI $api) {}

    public function getInterviews(Request $request)
    {
        $tab = $request->query('tab', 'upcoming');
        $view = $request->query('view', 'list');

        $response = $this->api->getInterviews();

        $interviews = [];
        if (($response['statusCode'] ?? 500) === 200) {
            $allInterviews = \App\Support\Rows::from($response['data'] ?? []);

            $interviews = array_values(array_filter($allInterviews, function ($item) use ($tab) {
                $scheduledAt = data_get($item, 'scheduled_at');
                if (!$scheduledAt) {
                    return true;
                }

                $isPast = Carbon::parse($scheduledAt)->isPast();
                return $tab === 'past' ? $isPast : !$isPast;
            }));
        } else {
            return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']]);
        }

        return view('frontend.interviewer.interviews', compact('interviews', 'tab', 'view'));
    }

    public function getInterview(Request $request, int $interviewId): View|RedirectResponse
    {
        $response = $this->api->getInterview($interviewId);
        $status = (int) ($response['statusCode'] ?? 500);

        if ($status === 401) {
            ApiSession::forget();
            return redirect()->route('frontend.account.login')
                ->withErrors(['error' => __('interviewer.details.session_expired')]);
        }

        if ($status === 404) {
            abort(404);
        }

        if ($status === 403) {
            return view('frontend.interviewer.interview-details', [
                'state' => 'forbidden',
                'interviewId' => $interviewId,
                'interview' => [],
                'bookings' => [],
                'prompts' => $this->genericPrompts(),
                'message' => __('interviewer.details.forbidden'),
            ]);
        }

        if ($status !== 200 || !is_array($response['data'] ?? null)) {
            return view('frontend.interviewer.interview-details', [
                'state' => 'error',
                'interviewId' => $interviewId,
                'interview' => [],
                'bookings' => [],
                'prompts' => $this->genericPrompts(),
                'message' => SafeApiMessage::forUser(
                    is_string($response['message'] ?? null) ? $response['message'] : null,
                    __('interviewer.details.load_error'),
                    ['action' => 'interviewer.interviews.show', 'status' => $status]
                ),
            ]);
        }

        return view('frontend.interviewer.interview-details', [
            'state' => 'loaded',
            'details' => $this->interviewDetails($response['data'], $interviewId),
            'interviewId' => $interviewId,
            'prompts' => $this->genericPrompts(),
            'message' => null,
        ]);
    }

    private function interviewDetails(array $interview, int $interviewId): array
    {
        $bookings = data_get($interview, 'bookings', []);
        $bookings = is_array($bookings) ? $bookings : [];
        $bookings = array_values(array_filter($bookings, static fn ($booking): bool => is_array($booking)
            && strtolower((string) data_get($booking, 'booking_status', '')) !== 'cancelled'));
        
        $bookings = array_map(fn (array $booking): array => [
            'interviewer_name' => $this->stringOrNull(data_get($booking, 'interviewer.name')),
            'start_at' => $this->stringOrNull(data_get($booking, 'start_at')),
            'end_at' => $this->stringOrNull(data_get($booking, 'end_at')),
        ], $bookings);

        $meetingLink = $this->stringOrNull(data_get($interview, 'meeting_link') ?? data_get($interview, 'meeting_url'));
        if ($meetingLink !== null && !preg_match('#^https?://#i', $meetingLink)) {
            $meetingLink = null;
        }

        return [
            'id' => data_get($interview, 'id', $interviewId),
            'job_title' => $this->stringOrNull(data_get($interview, 'application.job.title') ?? data_get($interview, 'job.title') ?? data_get($interview, 'job_title')),
            'status' => $this->stringOrNull(data_get($interview, 'status')),
            'round_no' => is_scalar(data_get($interview, 'round_no')) ? (string) data_get($interview, 'round_no') : null,
            'interview_type' => $this->stringOrNull(data_get($interview, 'interview_type')),
            'location' => $this->stringOrNull(data_get($interview, 'location')),
            'instructions' => $this->stringOrNull(data_get($interview, 'instructions')),
            'meeting_link' => $meetingLink,
            'candidate_name' => $this->stringOrNull(data_get($interview, 'candidate.name') ?? data_get($interview, 'application.candidate.name') ?? data_get($interview, 'candidate_name')),
            'candidate_headline' => $this->stringOrNull(data_get($interview, 'candidate.headline')),
            'bookings' => $bookings,
        ];
    }

    private function stringOrNull(mixed $value): ?string
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }
        return trim($value);
    }

    private function genericPrompts(): array
    {
        return [
            __('interviewer.live.q_problem'),
            __('interviewer.live.q_systems'),
            __('interviewer.live.q_collaboration'),
        ];
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
                return redirect()->back()->with('success_key', 'flash.interviewer.slot_added');
            } else {
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
        if (!is_array($interview)) {
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

                if (!$hasNumericRating) {
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
<?php

namespace App\Frontend\Interviewer;

use App\Frontend\Auth\ApiSession;
use App\Models\InterviewerAPI;
use App\Support\Frontend\SafeApiMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * Mission 09 — Interviewer Live Interview Workspace (Task #42).
 *
 * Server contracts used: GET /interviewer/interviews/{id} (assignment via Gate::viewInterviewer).
 * No Backend session timer, agenda, question-bank, or notes-draft APIs — those panels are
 * explicitly local-only / blocked. Final evaluation stays on the existing scorecard route
 * (POST /interviews/{id}/evaluations) and is never auto-submitted from this page.
 */
class InterviewerLiveWorkspaceController extends Controller
{
    public function __construct(private readonly InterviewerAPI $api) {}

    public function show(Request $request, int $interviewId): View|RedirectResponse
    {
        $response = $this->api->getInterview($interviewId);
        $code = (int) ($response['statusCode'] ?? 500);

        if ($code === 401) {
            ApiSession::forget();

            return redirect()->route('frontend.account.login')
                ->withErrors(['error' => __('interviewer.live.session_expired')]);
        }

        if ($code === 404) {
            return view('frontend.interviewer.live-workspace', $this->emptyState(
                interviewId: $interviewId,
                mode: 'not_found',
                message: __('interviewer.live.not_found')
            ));
        }

        if ($code === 403) {
            return view('frontend.interviewer.live-workspace', $this->emptyState(
                interviewId: $interviewId,
                mode: 'forbidden',
                message: __('interviewer.live.forbidden')
            ));
        }

        if ($code < 200 || $code >= 300 || ! is_array($response['data'] ?? null)) {
            return view('frontend.interviewer.live-workspace', $this->emptyState(
                interviewId: $interviewId,
                mode: 'error',
                message: SafeApiMessage::forUser(
                    is_string($response['message'] ?? null) ? $response['message'] : null,
                    __('interviewer.live.load_error'),
                    ['action' => 'interviewer.interviews.show', 'status' => $code]
                )
            ));
        }

        $interview = $response['data'];
        $context = $this->buildContext($interview, $interviewId);

        return view('frontend.interviewer.live-workspace', [
            'mode' => 'workspace',
            'interviewId' => $interviewId,
            'context' => $context,
            'emptyMessage' => null,
            'agendaItems' => $this->localAgenda(),
            'questionPrompts' => $this->localQuestionPrompts(),
            'draftSaveSupported' => false,
            'timerIsLocal' => true,
            'agendaIsLocal' => true,
            'questionsAreLocal' => true,
            'notesAreLocal' => true,
        ]);
    }

    /**
     * @param  array<string, mixed>  $interview
     * @return array<string, mixed>
     */
    private function buildContext(array $interview, int $interviewId): array
    {
        $bookings = data_get($interview, 'bookings');
        if (! is_array($bookings)) {
            $bookings = [];
        }

        $primary = null;
        foreach ($bookings as $booking) {
            if (! is_array($booking)) {
                continue;
            }
            $status = strtolower((string) data_get($booking, 'booking_status', ''));
            if ($status === 'cancelled') {
                continue;
            }
            $primary = $booking;
            break;
        }
        if ($primary === null && isset($bookings[0]) && is_array($bookings[0])) {
            $primary = $bookings[0];
        }

        $startAt = data_get($primary, 'start_at');
        $endAt = data_get($primary, 'end_at');

        // Job title is not returned by InterviewerInterviewResource — do not invent.
        $jobTitle = data_get($interview, 'job.title')
            ?? data_get($interview, 'application.job.title')
            ?? data_get($interview, 'job_title');
        $jobTitle = is_string($jobTitle) && trim($jobTitle) !== '' ? trim($jobTitle) : null;

        $candidateName = data_get($interview, 'candidate.name')
            ?? data_get($interview, 'application.candidate.name')
            ?? data_get($interview, 'candidate_name');
        $candidateName = is_string($candidateName) && trim($candidateName) !== '' ? trim($candidateName) : null;

        $headline = data_get($interview, 'candidate.headline');
        $headline = is_string($headline) && trim($headline) !== '' ? trim($headline) : null;

        $interviewerName = data_get($primary, 'interviewer.name');
        $interviewerName = is_string($interviewerName) && trim($interviewerName) !== '' ? trim($interviewerName) : null;

        return [
            'id' => (int) data_get($interview, 'id', $interviewId),
            'status' => (string) (data_get($interview, 'status') ?: '—'),
            'interview_type' => (string) (data_get($interview, 'interview_type') ?: '—'),
            'round_no' => data_get($interview, 'round_no'),
            'candidate_name' => $candidateName,
            'candidate_headline' => $headline,
            // Intentionally omit email/phone from live header (PII minimization).
            'job_title' => $jobTitle,
            'job_title_available' => $jobTitle !== null,
            'interviewer_name' => $interviewerName,
            'start_at' => is_string($startAt) ? $startAt : null,
            'end_at' => is_string($endAt) ? $endAt : null,
            'when_label' => $this->formatWindow($startAt, $endAt),
            'timezone_note' => __('interviewer.live.timezone_unavailable'),
            'meeting_link' => $this->safeMeetingLink(data_get($interview, 'meeting_link') ?? data_get($interview, 'meeting_url')),
            'location' => filled(data_get($interview, 'location')) ? (string) data_get($interview, 'location') : null,
            'instructions' => filled(data_get($interview, 'instructions')) ? (string) data_get($interview, 'instructions') : null,
        ];
    }

    private function formatWindow(mixed $startAt, mixed $endAt): string
    {
        try {
            if (! is_string($startAt) || $startAt === '') {
                return '—';
            }
            $start = Carbon::parse($startAt);
            $label = $start->format('Y-m-d H:i');
            if (is_string($endAt) && $endAt !== '') {
                $end = Carbon::parse($endAt);
                $label .= ' – '.$end->format('H:i');
            }

            return $label.' (UTC offset as returned)';
        } catch (\Throwable) {
            return is_string($startAt) ? $startAt : '—';
        }
    }

    private function safeMeetingLink(mixed $link): ?string
    {
        if (! is_string($link) || trim($link) === '') {
            return null;
        }
        $link = trim($link);
        if (! preg_match('#^https?://#i', $link)) {
            return null;
        }

        return $link;
    }

    /**
     * Generic non-candidate-specific workflow guide (local-only).
     *
     * @return list<array{key: string, label: string}>
     */
    private function localAgenda(): array
    {
        return [
            ['key' => 'intro', 'label' => __('interviewer.live.agenda_intro')],
            ['key' => 'technical', 'label' => __('interviewer.live.agenda_technical')],
            ['key' => 'competency', 'label' => __('interviewer.live.agenda_competency')],
            ['key' => 'candidate_qs', 'label' => __('interviewer.live.agenda_candidate')],
            ['key' => 'wrap', 'label' => __('interviewer.live.agenda_wrap')],
        ];
    }

    /**
     * Generic interviewer prompts — not a Backend question bank.
     *
     * @return list<array{key: string, label: string}>
     */
    private function localQuestionPrompts(): array
    {
        return [
            ['key' => 'problem', 'label' => __('interviewer.live.q_problem')],
            ['key' => 'systems', 'label' => __('interviewer.live.q_systems')],
            ['key' => 'collaboration', 'label' => __('interviewer.live.q_collaboration')],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyState(int $interviewId, string $mode, string $message): array
    {
        return [
            'mode' => $mode,
            'interviewId' => $interviewId,
            'context' => null,
            'emptyMessage' => $message,
            'agendaItems' => [],
            'questionPrompts' => [],
            'draftSaveSupported' => false,
            'timerIsLocal' => true,
            'agendaIsLocal' => true,
            'questionsAreLocal' => true,
            'notesAreLocal' => true,
        ];
    }
}

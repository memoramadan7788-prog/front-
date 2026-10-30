<?php

namespace App\Frontend\Company;

use App\Frontend\Auth\ApiSession;
use App\Models\CompanyAPI;
use App\Models\EmployeeAPI;
use App\Support\Frontend\SafeApiMessage;
use App\Support\Rows;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Mission 07 — HR Schedule Interview.
 *
 * Scheduling is backed by POST /api/interviews + interviewer availability slots.
 * Duration/timezone are derived from selected open slots (display timezone is UI-only).
 * Invitation email delivery is NOT claimed — InterviewSchedulingService does not send mail.
 */
class CompanyScheduleInterviewController extends Controller
{
    public const SESSION_KEY = 'company_schedule_interview';

    /** Backend InterviewType enum (+ onsite alias normalized server-side to on_site). */
    public const INTERVIEW_TYPES = ['online', 'on_site', 'exam'];

    /** Display timezones only — not persisted by schedule API. */
    public const DISPLAY_TIMEZONES = [
        'Africa/Cairo',
        'Asia/Riyadh',
        'Asia/Dubai',
        'Europe/London',
        'UTC',
    ];

    public function __construct(
        private readonly CompanyAPI $companyApi,
        private readonly EmployeeAPI $employeeApi,
    ) {}

    public function show(Request $request): View|RedirectResponse
    {
        $applicationId = (int) $request->query('application_id', Session::get(self::SESSION_KEY.'.application_id', 0));
        if ($applicationId < 1) {
            return view('frontend.company.schedule-interview', $this->emptyStatePayload(
                __('company.interviews.schedule_need_application')
            ));
        }

        $applicationRes = $this->companyApi->getApplication($applicationId);
        $code = (int) ($applicationRes['statusCode'] ?? 500);

        if ($code === 401) {
            ApiSession::forget();

            return redirect()->route('frontend.account.login')
                ->withErrors(['error' => __('company.interviews.session_expired')]);
        }

        if ($code === 403) {
            return view('frontend.company.schedule-interview', $this->emptyStatePayload(
                __('company.interviews.forbidden_application'),
                unauthorized: true
            ));
        }

        if ($code === 404 || $code < 200 || $code >= 300) {
            return view('frontend.company.schedule-interview', $this->emptyStatePayload(
                SafeApiMessage::forUser(
                    is_string($applicationRes['message'] ?? null) ? $applicationRes['message'] : null,
                    __('company.interviews.application_unavailable'),
                    ['action' => 'company.applications.show', 'status' => $code]
                )
            ));
        }

        $application = is_array($applicationRes['data'] ?? null) ? $applicationRes['data'] : [];
        Session::put(self::SESSION_KEY.'.application_id', $applicationId);

        $fields = array_merge($this->defaultFields(), Session::get(self::SESSION_KEY.'.fields', []), old() ?: []);
        $interviewers = $this->loadInterviewers();
        $slotsPayload = $this->loadSlots($applicationId, $fields);
        $selectedSlot = $this->findSlot($slotsPayload['slots'], $fields['availability_id'] ?? null);
        $conflict = $this->conflictState($fields, $selectedSlot);

        return view('frontend.company.schedule-interview', [
            'mode' => 'form',
            'application' => $application,
            'applicationId' => $applicationId,
            'fields' => $fields,
            'interviewTypes' => self::INTERVIEW_TYPES,
            'displayTimezones' => self::DISPLAY_TIMEZONES,
            'interviewers' => $interviewers['items'],
            'interviewersAvailable' => $interviewers['available'],
            'interviewersMessage' => $interviewers['message'],
            'slots' => $slotsPayload['slots'],
            'slotsAvailable' => $slotsPayload['available'],
            'slotsMessage' => $slotsPayload['message'],
            'selectedSlot' => $selectedSlot,
            'conflict' => $conflict,
            'review' => (bool) ($fields['show_review'] ?? false),
            'unauthorized' => false,
            'emptyMessage' => null,
        ]);
    }

    public function handle(Request $request): View|RedirectResponse
    {
        $action = (string) $request->input('schedule_action', 'refresh');
        $applicationId = (int) $request->input('application_id', 0);
        if ($applicationId < 1) {
            return redirect()
                ->route('frontend.company.interviews.schedule')
                ->withErrors(['error' => __('company.interviews.schedule_need_application')]);
        }

        $validated = $request->validate([
            'application_id' => ['required', 'integer', 'min:1'],
            'interview_type' => ['required', Rule::in(self::INTERVIEW_TYPES)],
            'interviewer_id' => ['nullable', 'integer', 'min:1'],
            'availability_id' => ['nullable', 'integer', 'min:1'],
            'slot_date' => ['nullable', 'date'],
            'display_timezone' => ['required', Rule::in(self::DISPLAY_TIMEZONES)],
            'round_no' => ['nullable', 'integer', 'min:1'],
            'location' => ['nullable', 'string', 'max:255'],
            'meeting_link' => ['nullable', 'url', 'max:500'],
            'instructions' => ['nullable', 'string', 'max:5000'],
            'schedule_action' => ['nullable', 'string'],
        ]);

        $fields = [
            'interview_type' => $validated['interview_type'],
            'interviewer_id' => $validated['interviewer_id'] ?? null,
            'availability_id' => $validated['availability_id'] ?? null,
            'slot_date' => $validated['slot_date'] ?? null,
            'display_timezone' => $validated['display_timezone'],
            'round_no' => $validated['round_no'] ?? 1,
            'location' => $validated['location'] ?? null,
            'meeting_link' => $validated['meeting_link'] ?? null,
            'instructions' => $validated['instructions'] ?? null,
            'show_review' => false,
        ];

        Session::put(self::SESSION_KEY.'.application_id', $applicationId);
        Session::put(self::SESSION_KEY.'.fields', $fields);

        if ($action === 'refresh' || $action === 'check') {
            if ($action === 'check') {
                $fields['show_review'] = false;
                Session::put(self::SESSION_KEY.'.fields', $fields);
            }

            return redirect()->route('frontend.company.interviews.schedule', [
                'application_id' => $applicationId,
            ]);
        }

        if ($action === 'review') {
            $errors = $this->localScheduleGaps($fields);
            if ($errors !== []) {
                return redirect()
                    ->route('frontend.company.interviews.schedule', ['application_id' => $applicationId])
                    ->withErrors($errors)
                    ->withInput($fields);
            }
            $fields['show_review'] = true;
            Session::put(self::SESSION_KEY.'.fields', $fields);

            return redirect()->route('frontend.company.interviews.schedule', [
                'application_id' => $applicationId,
            ]);
        }

        if ($action === 'confirm') {
            return $this->confirm($applicationId, $fields);
        }

        return redirect()->route('frontend.company.interviews.schedule', [
            'application_id' => $applicationId,
        ]);
    }

    /**
     * @param  array<string, mixed>  $fields
     */
    private function confirm(int $applicationId, array $fields): RedirectResponse
    {
        $errors = $this->localScheduleGaps($fields);
        if ($errors !== []) {
            return redirect()
                ->route('frontend.company.interviews.schedule', ['application_id' => $applicationId])
                ->withErrors($errors)
                ->withInput($fields);
        }

        $slotsPayload = $this->loadSlots($applicationId, $fields);
        $slot = $this->findSlot($slotsPayload['slots'], $fields['availability_id'] ?? null);
        if ($slot === null) {
            return redirect()
                ->route('frontend.company.interviews.schedule', ['application_id' => $applicationId])
                ->withErrors(['availability_id' => __('company.interviews.slot_missing')])
                ->withInput($fields);
        }

        // Authoritative-ish precheck (Backend still re-validates slot open/lock).
        $conflict = $this->conflictState($fields, $slot);
        if (($conflict['state'] ?? '') === 'conflict') {
            return redirect()
                ->route('frontend.company.interviews.schedule', ['application_id' => $applicationId])
                ->withErrors(['error' => __('company.interviews.conflict_blocked')])
                ->withInput($fields);
        }

        $interviewers = $this->loadInterviewers();
        $interviewerId = (int) $fields['interviewer_id'];
        if (! $this->interviewerAllowed($interviewers['items'], $interviewerId)) {
            return redirect()
                ->route('frontend.company.interviews.schedule', ['application_id' => $applicationId])
                ->withErrors(['interviewer_id' => __('company.interviews.interviewer_not_allowed')])
                ->withInput($fields);
        }

        $payload = [
            'application_id' => $applicationId,
            'round_no' => (int) ($fields['round_no'] ?? 1),
            'interview_type' => $fields['interview_type'],
            'instructions' => $fields['instructions'] ?: null,
            'location' => $fields['location'] ?: null,
            'meeting_link' => $fields['meeting_link'] ?: null,
            'bookings' => [[
                'interviewer_id' => $interviewerId,
                'availability_id' => (int) $fields['availability_id'],
            ]],
        ];

        $response = $this->companyApi->scheduleInterview($payload);
        $code = (int) ($response['statusCode'] ?? 500);

        if ($code === 401) {
            ApiSession::forget();
            Session::forget(self::SESSION_KEY);

            return redirect()->route('frontend.account.login')
                ->withErrors(['error' => __('company.interviews.session_expired')]);
        }

        if ($code >= 200 && $code < 300) {
            Session::forget(self::SESSION_KEY);

            return redirect()
                ->route('frontend.company.interview-calendar')
                ->with('success_key', 'company.interviews.flash_scheduled');
        }

        $fallback = match (true) {
            $code === 403 => __('company.interviews.forbidden_schedule'),
            $code === 409 => __('company.interviews.conflict_or_taken'),
            $code === 422 => __('company.interviews.validation_error'),
            $code >= 500 => __('company.interviews.server_error'),
            default => __('company.interviews.save_error'),
        };

        $errors = is_array($response['errors'] ?? null) && ($response['errors'] ?? []) !== []
            ? $response['errors']
            : ['error' => SafeApiMessage::forUser(
                is_string($response['message'] ?? null) ? $response['message'] : null,
                $fallback,
                ['action' => 'interviews.schedule', 'status' => $code]
            )];

        return redirect()
            ->route('frontend.company.interviews.schedule', ['application_id' => $applicationId])
            ->withErrors($errors)
            ->withInput($fields);
    }

    /**
     * @param  array<string, mixed>  $fields
     * @return array<string, string>
     */
    private function localScheduleGaps(array $fields): array
    {
        $errors = [];
        if (! filled($fields['interview_type'] ?? null)) {
            $errors['interview_type'] = __('validation.required', ['attribute' => 'interview type']);
        }
        if (! filled($fields['interviewer_id'] ?? null)) {
            $errors['interviewer_id'] = __('company.interviews.interviewer_required');
        }
        if (! filled($fields['availability_id'] ?? null)) {
            $errors['availability_id'] = __('company.interviews.slot_required');
        }

        return $errors;
    }

    /**
     * @return array{items: list<array<string, mixed>>, available: bool, message: ?string}
     */
    private function loadInterviewers(): array
    {
        $res = $this->employeeApi->getEmployees(['status' => 'approved']);
        $code = (int) ($res['statusCode'] ?? 500);
        if ($code < 200 || $code >= 300) {
            return [
                'items' => [],
                'available' => false,
                'message' => SafeApiMessage::forUser(
                    is_string($res['message'] ?? null) ? $res['message'] : null,
                    __('company.interviews.interviewers_unavailable'),
                    ['action' => 'company.employees', 'status' => $code]
                ),
            ];
        }

        $items = [];
        foreach (Rows::from($res['data'] ?? []) as $row) {
            if (! is_array($row)) {
                continue;
            }
            $status = strtolower((string) data_get($row, 'status', ''));
            if ($status !== '' && ! in_array($status, ['approved', 'active'], true)) {
                continue;
            }
            $userId = (int) data_get($row, 'user.id', data_get($row, 'user_id', 0));
            $name = (string) (data_get($row, 'user.name') ?? data_get($row, 'name') ?? '');
            if ($userId < 1 || $name === '') {
                continue;
            }
            $role = strtolower((string) (data_get($row, 'role.name') ?? ''));
            $items[] = [
                'user_id' => $userId,
                'name' => $name,
                'email' => (string) (data_get($row, 'user.email') ?? ''),
                'role' => $role,
            ];
        }

        return ['items' => $items, 'available' => true, 'message' => null];
    }

    /**
     * @param  array<string, mixed>  $fields
     * @return array{slots: list<array<string, mixed>>, available: bool, message: ?string}
     */
    private function loadSlots(int $applicationId, array $fields): array
    {
        if (! filled($fields['interviewer_id'] ?? null)) {
            return [
                'slots' => [],
                'available' => true,
                'message' => __('company.interviews.pick_interviewer_first'),
            ];
        }

        $filters = [
            'interviewer_id' => (int) $fields['interviewer_id'],
        ];
        if (filled($fields['slot_date'] ?? null)) {
            $filters['date'] = $fields['slot_date'];
        }

        $res = $this->companyApi->getApplicationAvailableSlots($applicationId, $filters);
        $code = (int) ($res['statusCode'] ?? 500);
        if ($code < 200 || $code >= 300) {
            return [
                'slots' => [],
                'available' => false,
                'message' => SafeApiMessage::forUser(
                    is_string($res['message'] ?? null) ? $res['message'] : null,
                    __('company.interviews.slots_unavailable'),
                    ['action' => 'company.available-slots', 'status' => $code]
                ),
            ];
        }

        $slots = [];
        foreach (Rows::from($res['data'] ?? []) as $row) {
            if (! is_array($row)) {
                continue;
            }
            if (strtolower((string) data_get($row, 'availability_status', 'open')) !== 'open') {
                continue;
            }
            $slots[] = $row;
        }

        return [
            'slots' => $slots,
            'available' => true,
            'message' => $slots === [] ? __('company.interviews.no_open_slots') : null,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $slots
     * @return array<string, mixed>|null
     */
    private function findSlot(array $slots, mixed $availabilityId): ?array
    {
        if (! filled($availabilityId)) {
            return null;
        }
        foreach ($slots as $slot) {
            if ((string) data_get($slot, 'id') === (string) $availabilityId) {
                return $slot;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $fields
     * @param  array<string, mixed>|null  $slot
     * @return array{state: string, label: string, has_conflict: ?bool}
     */
    private function conflictState(array $fields, ?array $slot): array
    {
        if ($slot === null || ! filled($fields['interviewer_id'] ?? null)) {
            return [
                'state' => 'unverified',
                'label' => __('company.interviews.availability_unverified'),
                'has_conflict' => null,
            ];
        }

        $start = data_get($slot, 'start_at');
        $end = data_get($slot, 'end_at');
        if (! is_string($start) || ! is_string($end) || $start === '' || $end === '') {
            return [
                'state' => 'unverified',
                'label' => __('company.interviews.availability_unverified'),
                'has_conflict' => null,
            ];
        }

        $res = $this->companyApi->checkInterviewConflicts([
            'interviewer_id' => (int) $fields['interviewer_id'],
            'start_at' => $start,
            'end_at' => $end,
        ]);
        $code = (int) ($res['statusCode'] ?? 500);
        if ($code < 200 || $code >= 300) {
            return [
                'state' => 'unverified',
                'label' => __('company.interviews.conflict_check_failed'),
                'has_conflict' => null,
            ];
        }

        $hasConflict = (bool) data_get($res['data'], 'has_conflict', false);
        if ($hasConflict) {
            return [
                'state' => 'conflict',
                'label' => __('company.interviews.conflict_detected'),
                'has_conflict' => true,
            ];
        }

        return [
            'state' => 'clear',
            'label' => __('company.interviews.conflict_clear'),
            'has_conflict' => false,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $interviewers
     */
    private function interviewerAllowed(array $interviewers, int $userId): bool
    {
        foreach ($interviewers as $row) {
            if ((int) ($row['user_id'] ?? 0) === $userId) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultFields(): array
    {
        return [
            'interview_type' => 'online',
            'interviewer_id' => null,
            'availability_id' => null,
            'slot_date' => null,
            'display_timezone' => 'Africa/Cairo',
            'round_no' => 1,
            'location' => null,
            'meeting_link' => null,
            'instructions' => null,
            'show_review' => false,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyStatePayload(string $message, bool $unauthorized = false): array
    {
        return [
            'mode' => 'empty',
            'application' => [],
            'applicationId' => null,
            'fields' => $this->defaultFields(),
            'interviewTypes' => self::INTERVIEW_TYPES,
            'displayTimezones' => self::DISPLAY_TIMEZONES,
            'interviewers' => [],
            'interviewersAvailable' => false,
            'interviewersMessage' => null,
            'slots' => [],
            'slotsAvailable' => false,
            'slotsMessage' => null,
            'selectedSlot' => null,
            'conflict' => ['state' => 'unverified', 'label' => '', 'has_conflict' => null],
            'review' => false,
            'unauthorized' => $unauthorized,
            'emptyMessage' => $message,
        ];
    }

    public static function formatSlot(?array $slot, string $timezone): string
    {
        if ($slot === null) {
            return '—';
        }
        try {
            $start = Carbon::parse((string) data_get($slot, 'start_at'))->timezone($timezone);
            $end = Carbon::parse((string) data_get($slot, 'end_at'))->timezone($timezone);
            $mins = (int) (data_get($slot, 'duration_minutes') ?? $start->diffInMinutes($end));

            return $start->format('Y-m-d H:i').' – '.$end->format('H:i')." ({$mins} min, {$timezone})";
        } catch (\Throwable) {
            return (string) (data_get($slot, 'start_at') ?? '—');
        }
    }
}

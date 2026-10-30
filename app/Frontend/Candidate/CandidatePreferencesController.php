<?php

namespace App\Frontend\Candidate;

use App\Frontend\Auth\ApiSession;
use App\Support\Frontend\CandidateSettings\CandidateSettingsGateway;
use App\Support\Frontend\SafeApiMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;

/**
 * Candidate Preferences & Alerts — wired to verified Backend settings APIs only.
 */
class CandidatePreferencesController extends Controller
{
    /** @var list<string> */
    public const EMPLOYMENT_TYPES = [
        'full_time',
        'part_time',
        'contract',
        'internship',
        'temporary',
    ];

    /** @var list<string> */
    public const WORK_MODELS = ['onsite', 'remote', 'hybrid'];

    /** @var list<string> */
    public const PRIVACY_VISIBILITY = ['public', 'employers_only', 'private'];

    /** Filter keys aligned with public/candidate job discovery (Mission 01). */
    public const SAVED_SEARCH_FILTER_KEYS = ['q', 'location', 'employment_type', 'work_mode'];

    public function __construct(
        private readonly CandidateSettingsGateway $gateway,
    ) {}

    public function show(Request $request): View|RedirectResponse
    {
        $prefs = $this->gateway->getPreferences();
        $notifs = $this->gateway->getNotificationSettings();
        $privacy = $this->gateway->getPrivacy();
        $searches = $this->gateway->listSavedSearches();

        foreach ([$prefs, $notifs, $privacy, $searches] as $res) {
            if ((int) ($res['statusCode'] ?? 500) === 401) {
                ApiSession::forget();

                return redirect()->route('frontend.account.login')
                    ->withErrors(['error' => __('candidate.pipeline.session_expired')]);
            }
        }

        $preferencesError = $this->loadError($prefs);
        $notificationsError = $this->loadError($notifs);
        $privacyError = $this->loadError($privacy);
        $savedSearchesError = $this->loadError($searches);

        // Only normalize Backend payloads after a confirmed successful load.
        // Failed sections stay empty so defaults are never presented as persisted.
        return view('frontend.candidate.preferences', [
            'preferencesLoaded' => $preferencesError === null,
            'preferences' => $preferencesError === null
                ? $this->normalizePreferences($prefs['data'] ?? null)
                : [],
            'preferencesError' => $preferencesError,
            'notificationsLoaded' => $notificationsError === null,
            'notifications' => $notificationsError === null
                ? $this->normalizeNotifications($notifs['data'] ?? null)
                : [],
            'notificationsError' => $notificationsError,
            'privacyLoaded' => $privacyError === null,
            'privacy' => $privacyError === null
                ? $this->normalizePrivacy($privacy['data'] ?? null)
                : [],
            'privacyError' => $privacyError,
            'savedSearchesLoaded' => $savedSearchesError === null,
            'savedSearches' => $savedSearchesError === null
                ? $this->normalizeSearches($searches['data'] ?? null)
                : [],
            'savedSearchesError' => $savedSearchesError,
            'employmentTypes' => self::EMPLOYMENT_TYPES,
            'workModels' => self::WORK_MODELS,
            'visibilityOptions' => self::PRIVACY_VISIBILITY,
            'alertsDeliveryAvailable' => false,
        ]);
    }

    public function updatePreferences(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'desired_titles' => ['nullable', 'string', 'max:1000'],
            'location' => ['nullable', 'string', 'max:255'],
            'employment_type' => ['nullable', 'string', 'max:50', Rule::in(array_merge([''], self::EMPLOYMENT_TYPES))],
            'min_salary' => ['nullable', 'numeric', 'min:0'],
            'work_model' => ['nullable', 'string', Rule::in(array_merge([''], self::WORK_MODELS))],
            'open_to_relocation' => ['sometimes', 'boolean'],
            'include_agency' => ['sometimes', 'boolean'],
        ]);

        $titles = collect(preg_split('/[,;\n]+/', (string) ($validated['desired_titles'] ?? '')) ?: [])
            ->map(fn ($t) => trim((string) $t))
            ->filter()
            ->take(20)
            ->values()
            ->all();

        $payload = [
            'desired_titles' => $titles,
            'location' => $validated['location'] ?? null,
            'employment_type' => ($validated['employment_type'] ?? '') !== '' ? $validated['employment_type'] : null,
            'min_salary' => array_key_exists('min_salary', $validated) && $validated['min_salary'] !== null && $validated['min_salary'] !== ''
                ? $validated['min_salary']
                : null,
            'work_model' => ($validated['work_model'] ?? '') !== '' ? $validated['work_model'] : null,
            'open_to_relocation' => $request->boolean('open_to_relocation'),
            'include_agency' => $request->boolean('include_agency'),
        ];

        return $this->mutate(
            $this->gateway->updatePreferences($payload),
            'candidate.preferences.flash_preferences_saved',
            'preferences'
        );
    }

    public function updateNotifications(Request $request): RedirectResponse
    {
        $request->validate([
            'weekly_digest' => ['sometimes', 'boolean'],
            'status_updates' => ['sometimes', 'boolean'],
            'interview_reminders' => ['sometimes', 'boolean'],
            'messages' => ['sometimes', 'boolean'],
            'marketing' => ['sometimes', 'boolean'],
        ]);

        $payload = [
            'weekly_digest' => $request->boolean('weekly_digest'),
            'status_updates' => $request->boolean('status_updates'),
            'interview_reminders' => $request->boolean('interview_reminders'),
            'messages' => $request->boolean('messages'),
            'marketing' => $request->boolean('marketing'),
        ];

        return $this->mutate(
            $this->gateway->updateNotificationSettings($payload),
            'candidate.preferences.flash_notifications_saved',
            'notifications'
        );
    }

    public function updatePrivacy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'visibility' => ['required', Rule::in(self::PRIVACY_VISIBILITY)],
            'allow_agency_recruiters' => ['sometimes', 'boolean'],
            'show_in_public_search' => ['sometimes', 'boolean'],
        ]);

        $payload = [
            'visibility' => $validated['visibility'],
            'allow_agency_recruiters' => $request->boolean('allow_agency_recruiters'),
            'show_in_public_search' => $request->boolean('show_in_public_search'),
        ];

        return $this->mutate(
            $this->gateway->updatePrivacy($payload),
            'candidate.preferences.flash_privacy_saved',
            'privacy'
        );
    }

    public function storeSavedSearch(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'search_q' => ['nullable', 'string', 'max:120'],
            'search_location' => ['nullable', 'string', 'max:255'],
            'search_employment_type' => ['nullable', 'string', Rule::in(array_merge([''], self::EMPLOYMENT_TYPES))],
            'search_work_mode' => ['nullable', 'string', Rule::in(array_merge([''], self::WORK_MODELS))],
            'weekly_alerts' => ['sometimes', 'boolean'],
        ]);

        $rawFilters = [
            'q' => trim((string) ($validated['search_q'] ?? '')),
            'location' => trim((string) ($validated['search_location'] ?? '')),
            'employment_type' => trim((string) ($validated['search_employment_type'] ?? '')),
            'work_mode' => trim((string) ($validated['search_work_mode'] ?? '')),
        ];

        $filters = [];
        foreach ($rawFilters as $key => $value) {
            if ($value === '') {
                continue;
            }
            $filters[$key] = in_array($key, ['employment_type', 'work_mode'], true)
                ? [$value]
                : $value;
        }

        if ($filters === []) {
            return redirect()
                ->route('frontend.candidate.preferences')
                ->withFragment('saved-searches')
                ->withErrors(['filters' => __('candidate.preferences.saved_search_filters_required')])
                ->withInput();
        }

        $payload = [
            'name' => $validated['name'],
            'filters' => $filters,
            'weekly_alerts' => $request->boolean('weekly_alerts'),
            'is_paused' => false,
        ];

        return $this->mutate(
            $this->gateway->createSavedSearch($payload),
            'candidate.preferences.flash_saved_search_created',
            'saved-searches',
            acceptCreated: true
        );
    }

    public function updateSavedSearch(Request $request, int $searchId): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'weekly_alerts' => ['sometimes', 'boolean'],
            'is_paused' => ['sometimes', 'boolean'],
        ]);

        $payload = [];
        if (array_key_exists('name', $validated) && is_string($validated['name'])) {
            $payload['name'] = $validated['name'];
        }
        if ($request->has('weekly_alerts')) {
            $payload['weekly_alerts'] = $request->boolean('weekly_alerts');
        }
        if ($request->has('is_paused')) {
            $payload['is_paused'] = $request->boolean('is_paused');
        }

        if ($payload === []) {
            return redirect()
                ->route('frontend.candidate.preferences')
                ->withFragment('saved-searches')
                ->withErrors(['error' => __('candidate.preferences.saved_search_nothing_to_update')]);
        }

        return $this->mutate(
            $this->gateway->updateSavedSearch($searchId, $payload),
            'candidate.preferences.flash_saved_search_updated',
            'saved-searches'
        );
    }

    public function destroySavedSearch(Request $request, int $searchId): RedirectResponse
    {
        return $this->mutate(
            $this->gateway->deleteSavedSearch($searchId),
            'candidate.preferences.flash_saved_search_deleted',
            'saved-searches'
        );
    }

    /**
     * @param  array{statusCode?: mixed, message?: mixed, errors?: mixed}  $response
     */
    private function mutate(
        array $response,
        string $successKey,
        string $anchor,
        bool $acceptCreated = false,
    ): RedirectResponse {
        $status = (int) ($response['statusCode'] ?? 500);

        if ($status === 401) {
            ApiSession::forget();

            return redirect()->route('frontend.account.login')
                ->withErrors(['error' => __('candidate.pipeline.session_expired')]);
        }

        $ok = $status === 200 || ($acceptCreated && $status === 201);
        if ($ok) {
            return redirect()
                ->route('frontend.candidate.preferences')
                ->withFragment($anchor)
                ->with('success_key', $successKey);
        }

        $fallback = match (true) {
            $status === 403 => __('candidate.preferences.forbidden'),
            $status === 404 => __('candidate.preferences.not_found'),
            $status === 422 => __('candidate.preferences.validation_error'),
            $status >= 500 => __('candidate.preferences.server_error'),
            default => __('candidate.preferences.save_error'),
        };

        $errors = is_array($response['errors'] ?? null) && ($response['errors'] ?? []) !== []
            ? $response['errors']
            : ['error' => SafeApiMessage::forUser(
                is_string($response['message'] ?? null) ? $response['message'] : null,
                $fallback,
                ['action' => 'candidate.preferences.'.$anchor, 'status' => $status]
            )];

        return redirect()
            ->route('frontend.candidate.preferences')
            ->withFragment($anchor)
            ->withErrors($errors)
            ->withInput();
    }

    /**
     * @param  array{statusCode?: mixed, message?: mixed, errors?: mixed}  $response
     */
    private function loadError(array $response): ?string
    {
        $status = (int) ($response['statusCode'] ?? 500);
        if ($status === 200 || $status === 201) {
            return null;
        }

        $fallback = match (true) {
            $status === 403 => __('candidate.preferences.forbidden'),
            $status >= 500 => __('candidate.preferences.server_error'),
            default => __('candidate.preferences.load_error'),
        };

        return SafeApiMessage::forUser(
            is_string($response['message'] ?? null) ? $response['message'] : null,
            $fallback,
            ['action' => 'candidate.preferences.load', 'status' => $status]
        );
    }

    /** @return array<string, mixed> */
    private function normalizePreferences(mixed $data): array
    {
        $row = is_array($data) ? $data : [];
        $titles = $row['desired_titles'] ?? [];
        if (! is_array($titles)) {
            $titles = [];
        }

        return [
            'desired_titles' => array_values(array_filter(array_map('strval', $titles))),
            'desired_titles_text' => implode(', ', array_values(array_filter(array_map('strval', $titles)))),
            'location' => is_string($row['location'] ?? null) ? $row['location'] : '',
            'employment_type' => is_string($row['employment_type'] ?? null) ? $row['employment_type'] : '',
            'min_salary' => $row['min_salary'] ?? '',
            'work_model' => is_string($row['work_model'] ?? null) ? $row['work_model'] : '',
            'open_to_relocation' => (bool) ($row['open_to_relocation'] ?? false),
            'include_agency' => (bool) ($row['include_agency'] ?? false),
        ];
    }

    /** @return array<string, bool> */
    private function normalizeNotifications(mixed $data): array
    {
        $row = is_array($data) ? $data : [];

        return [
            'weekly_digest' => (bool) ($row['weekly_digest'] ?? false),
            'status_updates' => (bool) ($row['status_updates'] ?? true),
            'interview_reminders' => (bool) ($row['interview_reminders'] ?? true),
            'messages' => (bool) ($row['messages'] ?? true),
            'marketing' => (bool) ($row['marketing'] ?? false),
        ];
    }

    /** @return array<string, mixed> */
    private function normalizePrivacy(mixed $data): array
    {
        $row = is_array($data) ? $data : [];
        $visibility = is_string($row['visibility'] ?? null) ? $row['visibility'] : 'employers_only';
        if (! in_array($visibility, self::PRIVACY_VISIBILITY, true)) {
            $visibility = 'employers_only';
        }

        return [
            'visibility' => $visibility,
            'allow_agency_recruiters' => (bool) ($row['allow_agency_recruiters'] ?? false),
            'show_in_public_search' => (bool) ($row['show_in_public_search'] ?? false),
        ];
    }

    /** @return list<array<string, mixed>> */
    private function normalizeSearches(mixed $data): array
    {
        if (! is_array($data)) {
            return [];
        }
        $rows = array_is_list($data) ? $data : [];
        $out = [];
        foreach ($rows as $row) {
            if (! is_array($row) || ! isset($row['id'])) {
                continue;
            }
            $out[] = [
                'id' => (int) $row['id'],
                'name' => (string) ($row['name'] ?? ''),
                'filters' => is_array($row['filters'] ?? null) ? $row['filters'] : [],
                'weekly_alerts' => (bool) ($row['weekly_alerts'] ?? false),
                'is_paused' => (bool) ($row['is_paused'] ?? false),
                'last_alerted_at' => $row['last_alerted_at'] ?? null,
                'updated_at' => $row['updated_at'] ?? null,
            ];
        }

        return $out;
    }
}

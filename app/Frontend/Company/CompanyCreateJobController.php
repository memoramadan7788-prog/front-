<?php

namespace App\Frontend\Company;

use App\Frontend\Auth\ApiSession;
use App\Models\CompanyAPI;
use App\Models\LookupAPI;
use App\Support\Frontend\SafeApiMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Mission 06 — HR Create a Job four-step wizard.
 *
 * Persists only Backend/DB-supported job columns on final Save Draft / Publish.
 * Owner, hiring manager, publish_to_search, allow_agency_submissions, structured
 * requirements/benefits columns require pending Backend migration 000009 and are
 * not posted as persisted fields.
 */
class CompanyCreateJobController extends Controller
{
    public const SESSION_KEY = 'company_job_wizard';

    public const STEPS = ['role', 'description', 'hiring', 'review'];

    /** @var list<string> */
    public const EMPLOYMENT_TYPES = ['full_time', 'part_time', 'contract', 'internship', 'temporary'];

    /** @var list<string> */
    public const WORK_MODES = ['onsite', 'remote', 'hybrid'];

    /** @var list<string> */
    public const HIRING_MODELS = ['direct', 'agency'];

    /** @var list<string> */
    public const CURRENCIES = ['EGP', 'USD', 'EUR', 'SAR', 'AED'];

    /** @var list<string> */
    public const SALARY_PERIODS = ['hour', 'day', 'week', 'month', 'year'];

    /**
     * Columns accepted by StoreJobRequest that exist on live Audit `jobs` today.
     *
     * @var list<string>
     */
    public const PERSISTABLE_FIELDS = [
        'title',
        'description',
        'category_id',
        'location',
        'work_mode',
        'employment_type',
        'salary_min',
        'salary_max',
        'currency',
        'salary_period',
        'experience_years',
        'department',
        'openings_count',
        'deadline',
        'visibility',
        'private_apply_mode',
        'hiring_model',
        'status',
    ];

    public function __construct(
        private readonly CompanyAPI $companyApi,
        private readonly LookupAPI $lookupApi,
    ) {}

    public function show(Request $request): View|RedirectResponse
    {
        $state = $this->state();
        $step = $this->normalizeStep($request->query('step', $state['step'] ?? 1));
        $state['step'] = $step;
        $this->putState($state);

        return $this->render($step, $state['fields']);
    }

    public function handle(Request $request): View|RedirectResponse
    {
        $action = (string) $request->input('wizard_action', 'next');
        $step = $this->normalizeStep($request->input('step', 1));
        $state = $this->state();
        $fields = array_merge($state['fields'], $this->extractInput($request));

        // Back / goto keep draft input without blocking on incomplete step rules.
        if (in_array($action, ['back', 'goto'], true)) {
            $fields = array_merge($fields, $this->extractInput($request));
            $state['fields'] = $fields;
            $this->putState($state);

            return $action === 'back'
                ? $this->goToStep(max(1, $step - 1), $fields)
                : $this->goToStep($this->normalizeStep($request->input('goto_step', $step)), $fields);
        }

        if (in_array($action, ['next', 'preview', 'save_draft', 'publish'], true)) {
            $validated = $this->validateStep($request, $step, $action);
            $fields = array_merge($fields, $validated);
        }

        $state['fields'] = $fields;
        $state['step'] = $step;
        $this->putState($state);

        return match ($action) {
            'preview' => redirect()->route('frontend.company.vacancies.create.preview'),
            'save_draft' => $this->submit($fields, 'draft'),
            'publish' => $this->submit($fields, 'published'),
            'next' => $step >= 4
                ? $this->goToStep(4, $fields)
                : $this->goToStep($step + 1, $fields),
            default => $this->goToStep($step, $fields),
        };
    }

    public function preview(Request $request): View|RedirectResponse
    {
        $state = $this->state();
        $fields = $state['fields'];
        if ($fields === []) {
            return redirect()
                ->route('frontend.company.vacancies.create', ['step' => 1])
                ->withErrors(['error' => __('company.jobs.wizard_empty_preview')]);
        }

        $categories = $this->categories();
        $composed = $this->composeDescription($fields);

        return view('frontend.company.create-job-preview', [
            'fields' => $fields,
            'composedDescription' => $composed,
            'categories' => $categories,
            'categoryName' => $this->categoryName($categories, $fields['category_id'] ?? null),
            'persistable' => true,
        ]);
    }

    /**
     * @param  array<string, mixed>  $fields
     */
    private function submit(array $fields, string $status): RedirectResponse
    {
        $missing = $this->publicationGaps($fields);
        if ($status === 'published' && $missing !== []) {
            return redirect()
                ->route('frontend.company.vacancies.create', ['step' => 4])
                ->withErrors(['error' => __('company.jobs.publish_checklist_incomplete')])
                ->withInput($fields);
        }

        foreach (['title', 'description', 'work_mode', 'employment_type'] as $required) {
            // description may be composed from editor sections
            if ($required === 'description') {
                continue;
            }
            if (! filled($fields[$required] ?? null)) {
                return redirect()
                    ->route('frontend.company.vacancies.create', ['step' => 1])
                    ->withErrors([$required => __('validation.required', ['attribute' => $required])])
                    ->withInput($fields);
            }
        }

        $payload = $this->buildPersistablePayload($fields, $status);
        if (mb_strlen((string) ($payload['description'] ?? '')) < 20) {
            return redirect()
                ->route('frontend.company.vacancies.create', ['step' => 2])
                ->withErrors(['description' => __('company.jobs.description_min')])
                ->withInput($fields);
        }

        $response = $this->companyApi->createVacancy($payload);
        $code = (int) ($response['statusCode'] ?? 500);

        if ($code === 401) {
            ApiSession::forget();
            $this->clearState();

            return redirect()->route('frontend.account.login')
                ->withErrors(['error' => __('company.jobs.session_expired')]);
        }

        if ($code >= 200 && $code < 300) {
            $this->clearState();
            $flash = $status === 'draft'
                ? 'company.jobs.flash_draft_saved'
                : 'company.jobs.flash_published';

            return redirect()
                ->route('frontend.company.vacancies.index')
                ->with('success_key', $flash);
        }

        $fallback = match (true) {
            $code === 403 => __('company.jobs.forbidden_create'),
            $code === 422 => __('company.jobs.validation_error'),
            $code >= 500 => __('company.jobs.server_error'),
            default => __('company.jobs.save_error'),
        };

        $errors = is_array($response['errors'] ?? null) && ($response['errors'] ?? []) !== []
            ? $response['errors']
            : ['error' => SafeApiMessage::forUser(
                is_string($response['message'] ?? null) ? $response['message'] : null,
                $fallback,
                ['action' => 'company.jobs.create', 'status' => $code]
            )];

        return redirect()
            ->route('frontend.company.vacancies.create', ['step' => 4])
            ->withErrors($errors)
            ->withInput($fields);
    }

    /**
     * @param  array<string, mixed>  $fields
     * @return array<string, mixed>
     */
    private function buildPersistablePayload(array $fields, string $status): array
    {
        $payload = [
            'title' => trim((string) ($fields['title'] ?? '')),
            'description' => $this->composeDescription($fields),
            'category_id' => filled($fields['category_id'] ?? null) ? (int) $fields['category_id'] : null,
            'location' => filled($fields['location'] ?? null) ? trim((string) $fields['location']) : null,
            'work_mode' => $fields['work_mode'] ?? null,
            'employment_type' => $fields['employment_type'] ?? null,
            'salary_min' => filled($fields['salary_min'] ?? null) ? $fields['salary_min'] : null,
            'salary_max' => filled($fields['salary_max'] ?? null) ? $fields['salary_max'] : null,
            'currency' => filled($fields['currency'] ?? null) ? $fields['currency'] : null,
            'salary_period' => filled($fields['salary_period'] ?? null) ? $fields['salary_period'] : null,
            'experience_years' => filled($fields['experience_years'] ?? null) ? (int) $fields['experience_years'] : null,
            'department' => filled($fields['department'] ?? null) ? trim((string) $fields['department']) : null,
            'openings_count' => filled($fields['openings_count'] ?? null) ? (int) $fields['openings_count'] : 1,
            'deadline' => filled($fields['deadline'] ?? null) ? $fields['deadline'] : null,
            'visibility' => $fields['visibility'] ?? 'public',
            'private_apply_mode' => ($fields['visibility'] ?? 'public') === 'private'
                ? ($fields['private_apply_mode'] ?? 'all_employees')
                : null,
            'hiring_model' => $fields['hiring_model'] ?? 'direct',
            'status' => $status,
        ];

        // Never send DB-missing contract fields (migration 000009 pending on Audit).
        unset(
            $payload['requisition_owner_id'],
            $payload['hiring_manager_id'],
            $payload['allow_agency_submissions'],
            $payload['publish_to_search'],
            $payload['requirements'],
            $payload['benefits'],
            $payload['company_id'],
        );

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $fields
     */
    private function composeDescription(array $fields): string
    {
        $parts = [];
        $overview = trim((string) ($fields['description'] ?? ''));
        if ($overview !== '') {
            $parts[] = $overview;
        }
        $requirements = trim((string) ($fields['requirements_text'] ?? ''));
        if ($requirements !== '') {
            $parts[] = __('company.jobs.requirements').":\n".$requirements;
        }
        $benefits = trim((string) ($fields['benefits_text'] ?? ''));
        if ($benefits !== '') {
            $parts[] = __('company.jobs.benefits').":\n".$benefits;
        }

        return trim(implode("\n\n", $parts));
    }

    /**
     * @return array<string, mixed>
     */
    private function validateStep(Request $request, int $step, string $action): array
    {
        // Final mutations validate the full persistable set via publication gaps + payload build.
        if (in_array($action, ['save_draft', 'publish', 'preview'], true)) {
            return $this->extractInput($request);
        }

        $rules = match ($step) {
            1 => [
                'title' => ['required', 'string', 'max:255'],
                'category_id' => ['nullable', 'integer'],
                'department' => ['nullable', 'string', 'max:100'],
                'employment_type' => ['required', Rule::in(self::EMPLOYMENT_TYPES)],
                'work_mode' => ['required', Rule::in(self::WORK_MODES)],
                'location' => ['nullable', 'string', 'max:255'],
                'openings_count' => ['nullable', 'integer', 'min:1'],
                'salary_min' => ['nullable', 'numeric', 'min:0'],
                'salary_max' => ['nullable', 'numeric', 'gte:salary_min'],
                'currency' => ['nullable', Rule::in(array_merge([''], self::CURRENCIES))],
                'salary_period' => ['nullable', Rule::in(array_merge([''], self::SALARY_PERIODS))],
                'experience_years' => ['nullable', 'integer', 'min:0', 'max:50'],
                'deadline' => ['nullable', 'date', 'after:today'],
                'visibility' => ['required', Rule::in(['public', 'private'])],
                'private_apply_mode' => ['nullable', Rule::in(['all_employees', 'nominated_only'])],
            ],
            2 => [
                'description' => ['required', 'string', 'min:20', 'max:20000'],
                'requirements_text' => ['nullable', 'string', 'max:10000'],
                'benefits_text' => ['nullable', 'string', 'max:5000'],
            ],
            3 => [
                'hiring_model' => ['required', Rule::in(self::HIRING_MODELS)],
            ],
            default => [],
        };

        return $request->validate($rules);
    }

    /**
     * @return array<string, mixed>
     */
    private function extractInput(Request $request): array
    {
        return $request->only([
            'title', 'category_id', 'department', 'employment_type', 'work_mode', 'location',
            'openings_count', 'salary_min', 'salary_max', 'currency', 'salary_period',
            'experience_years', 'deadline', 'visibility', 'private_apply_mode',
            'description', 'requirements_text', 'benefits_text', 'hiring_model',
        ]);
    }

    /**
     * @param  array<string, mixed>  $fields
     * @return list<string>
     */
    private function publicationGaps(array $fields): array
    {
        $gaps = [];
        if (! filled($fields['title'] ?? null)) {
            $gaps[] = 'title';
        }
        if (mb_strlen($this->composeDescription($fields)) < 20) {
            $gaps[] = 'description';
        }
        if (! filled($fields['category_id'] ?? null)) {
            $gaps[] = 'category';
        }
        if (! filled($fields['work_mode'] ?? null)) {
            $gaps[] = 'work_mode';
        }
        if (! filled($fields['employment_type'] ?? null)) {
            $gaps[] = 'employment_type';
        }

        return $gaps;
    }

    /**
     * @param  array<string, mixed>  $fields
     */
    private function goToStep(int $step, array $fields): RedirectResponse
    {
        $state = $this->state();
        $state['step'] = $step;
        $state['fields'] = $fields;
        $this->putState($state);

        return redirect()->route('frontend.company.vacancies.create', ['step' => $step]);
    }

    /**
     * @param  array<string, mixed>  $fields
     */
    private function render(int $step, array $fields): View
    {
        $categories = $this->categories();
        $gaps = $this->publicationGaps($fields);

        return view('frontend.company.create-vacancy', [
            'step' => $step,
            'steps' => self::STEPS,
            'fields' => $fields,
            'categories' => $categories,
            'employmentTypes' => self::EMPLOYMENT_TYPES,
            'workModes' => self::WORK_MODES,
            'hiringModels' => self::HIRING_MODELS,
            'currencies' => self::CURRENCIES,
            'salaryPeriods' => self::SALARY_PERIODS,
            'publicationGaps' => $gaps,
            'canPublish' => $gaps === [],
            'categoryName' => $this->categoryName($categories, $fields['category_id'] ?? null),
            'schemaBlocked' => [
                'requisition_owner_id',
                'hiring_manager_id',
                'allow_agency_submissions',
                'publish_to_search',
                'requirements_column',
                'benefits_column',
                'publish_endpoint',
            ],
            'composedDescription' => $this->composeDescription($fields),
        ]);
    }

    /** @return list<array<string, mixed>> */
    private function categories(): array
    {
        $res = $this->lookupApi->getCategories();
        // Prefer API `data` list; Rows::from also tolerates plain lists / nested envelopes (HEAD create form).
        $rows = \App\Support\Rows::from($res['data'] ?? $res);

        return array_values(array_filter(
            $rows,
            fn ($row) => is_array($row) && isset($row['id'])
        ));
    }

    /**
     * @param  list<array<string, mixed>>  $categories
     */
    private function categoryName(array $categories, mixed $id): string
    {
        if ($id === null || $id === '') {
            return '';
        }
        foreach ($categories as $cat) {
            if ((string) ($cat['id'] ?? '') === (string) $id) {
                return (string) ($cat['name'] ?? $cat['title'] ?? '');
            }
        }

        return '';
    }

    private function normalizeStep(mixed $step): int
    {
        $n = (int) $step;

        return max(1, min(4, $n));
    }

    /**
     * @return array{step: int, fields: array<string, mixed>}
     */
    private function state(): array
    {
        $raw = Session::get(self::SESSION_KEY, []);
        if (! is_array($raw)) {
            $raw = [];
        }

        return [
            'step' => $this->normalizeStep($raw['step'] ?? 1),
            'fields' => is_array($raw['fields'] ?? null) ? $raw['fields'] : [],
        ];
    }

    /**
     * @param  array{step: int, fields: array<string, mixed>}  $state
     */
    private function putState(array $state): void
    {
        Session::put(self::SESSION_KEY, [
            'step' => $this->normalizeStep($state['step'] ?? 1),
            'fields' => is_array($state['fields'] ?? null) ? $state['fields'] : [],
        ]);
    }

    private function clearState(): void
    {
        Session::forget(self::SESSION_KEY);
    }
}

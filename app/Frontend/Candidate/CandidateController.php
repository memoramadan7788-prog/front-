<?php

namespace App\Frontend\Candidate;

use App\Frontend\Auth\ApiSession;
use App\Models\CandidateAPI;
use App\Models\JobAPI;
use App\Models\LookupAPI;
use App\Support\Frontend\CandidateDashboard\CandidateDashboardKpiGateway;
use App\Support\Frontend\CandidateReturnPath;
use App\Support\Rows;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CandidateController extends Controller
{
    private $api;

    public function __construct(
        CandidateAPI $api,
        private readonly LookupAPI $lookups,
    ) {
        $this->api = $api;
    }

    public function dashboard(CandidateDashboardAdapter $adapter)
    {
        $payload = $adapter->load();

        if (! empty($payload['unauthorized'])) {
            ApiSession::forget();

            return redirect()->route('frontend.account.login')
                ->withErrors(['error' => __('candidate.pipeline.session_expired')]);
        }

        return view('frontend.candidate.dashboard', [
            'kpis' => $payload['kpis'] ?? [],
            'analytics' => $payload['analytics'] ?? [],
            'overview' => $payload['overview'] ?? [],
            'loadError' => $payload['loadError'] ?? null,
        ]);
    }

    public function getProfile(Request $request)
    {
        $response = $this->api->getProfile();

        if ($response['statusCode'] === 200) {
            // Profile retrieval successful, you can perform additional actions here if needed
            return view('frontend.candidate.profile', ['profile' => $response['data']]);
        } else {
            // Profile retrieval failed, return the error message
            return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']]);
        }
    }

    public function updateProfile(Request $request)
    {
        if ($request->isMethod('post')) {

            $fields = $request->validate([
                'phone' => 'nullable|string|max:30',
                'location' => 'nullable|string|max:255',
                'headline' => 'nullable|string|max:255',
                'bio' => 'nullable|string',
                'has_no_experience' => 'nullable|boolean',
                'years_experience' => 'nullable|integer|min:0',
            ]);

            $data = $request->only(['phone', 'location', 'headline', 'bio', 'has_no_experience', 'years_experience']);

            $response = $this->api->updateProfile($data);

            if ($response['statusCode'] === 200) {
                // Profile update successful, you can perform additional actions here if needed
                return redirect()->route('frontend.candidate.profile')->with('success_key', 'flash.candidate.profile_updated');
            } else {
                // Profile update failed, return the error message
                return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']])->withInput();
            }
        }

        // pre-fill the form with the current profile
        $response = $this->api->getProfile();
        $profile = $response['statusCode'] === 200 ? $response['data'] : [];

        return view('frontend.candidate.update-profile', get_defined_vars());
    }

public function uploadResume(Request $request)
    {
        // Backend UploadCvRequest: mimes pdf,doc,docx · max 10240 KB (10 MB).
        $maxKilobytes = 10240;
        $allowedMimes = 'pdf,doc,docx';

        $returnTo = CandidateReturnPath::applyPathFromRequest($request);
        $vacancyId = CandidateReturnPath::vacancyIdFromSafePath($returnTo);

        if ($request->isMethod('post')) {
            $request->validate([
                'resume' => "required|file|mimes:{$allowedMimes}|max:{$maxKilobytes}",
                'vacancy_id' => 'nullable|integer|min:1',
                'return_to' => 'nullable|string|max:255',
            ]);

            // Re-resolve after validation so forged absolute return_to cannot win.
            $returnTo = CandidateReturnPath::applyPathFromRequest($request);
            $vacancyId = CandidateReturnPath::vacancyIdFromSafePath($returnTo);

            $uploadedResume = $request->file('resume');

            // Send the real client filename + mime: the temp path is like "phpAbC123" (no extension),
            // and the backend ATS needs the extension to know how to read the CV.
            $response = $this->api->uploadResume(
                $uploadedResume->getRealPath(),
                $uploadedResume->getClientMimeType(),
                $uploadedResume->getClientOriginalName()
            );

            if ($response['statusCode'] === 200) {
                if ($returnTo !== null) {
                    return redirect($returnTo)
                        ->with('success_key', 'candidate.resume_upload.flash_success');
                }

                return redirect()->route('frontend.candidate.profile')
                    ->with('success_key', 'candidate.resume_upload.flash_success');
            }

            return redirect()->back()
                ->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message'] ?? __('candidate.resume_upload.flash_error')])
                ->withInput($request->except('resume'));
        }

        return view('frontend.candidate.upload-resume', [
            'returnTo' => $returnTo,
            'vacancyId' => $vacancyId,
            'maxKilobytes' => $maxKilobytes,
            'allowedExtensions' => ['pdf', 'doc', 'docx'],
            'maxBytes' => $maxKilobytes * 1024,
        ]);
    }
    public function downloadResume(Request $request, $resumeId)
    {
        $response = $this->api->downloadResume($resumeId);

        if ($response['statusCode'] === 200) {
            // Resume download successful, you can perform additional actions here if needed
            return response()->streamDownload(function () use ($response) {
                echo $response['data'];
            }, 'resume.pdf', [
                'Content-Type' => 'application/pdf',
            ]);
        } else {
            // Resume download failed, return the error message
            return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']]);
        }
    }

    public function deleteResume(Request $request, $resumeId)
    {
        $response = $this->api->deleteResume($resumeId);

        if ($response['statusCode'] === 200) {
            // Resume deletion successful, you can perform additional actions here if needed
            return redirect()->back()->with('success_key', 'flash.candidate.resume_deleted');
        } else {
            // Resume deletion failed, return the error message
            return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']]);
        }
    }

    public function getVacancies(Request $request)
    {
        $response = $this->api->getVacancies();

        if ($response['statusCode'] === 200) {
            $discoverContext = $this->discoverContext();

            // Vacancies retrieval successful, you can perform additional actions here if needed
            return view('frontend.candidate.vacancies', [
                'vacancies' => $response['data'],
                ...$discoverContext,
            ]);
        } else {
            // Vacancies retrieval failed, return the error message
            return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']]);
        }
    }

    /**
     * Load existing profile, skills, and application APIs to enrich discovery without inventing preferences.
     *
     * @return array{profile: array, coreSkills: array, appliedJobIds: array, contextErrors: array, skillsSource: string}
     */
    private function discoverContext(): array
    {
        $profileResponse = $this->api->getProfile();
        $skillsResponse = $this->api->getSkills();
        $applicationsResponse = $this->api->getApplications();
        $profile = $profileResponse['statusCode'] === 200 && is_array($profileResponse['data'] ?? null)
            ? $profileResponse['data']
            : [];
        $profileSkills = Rows::from(
            data_get($profile, 'skills', data_get($profile, 'candidate_skills', []))
        );
        $skillsAvailable = (int) ($skillsResponse['statusCode'] ?? 500) === 200;
        $skills = $skillsAvailable
            ? Rows::from($skillsResponse['data'] ?? [])
            : $profileSkills;
        $skillsSource = $skillsAvailable ? 'candidate_skills_api' : ($profileSkills ? 'profile' : 'unavailable');
        $applications = $applicationsResponse['statusCode'] === 200
            ? Rows::from($applicationsResponse['data'] ?? [])
            : [];
        $appliedJobIds = [];

        foreach ($applications as $application) {
            $jobId = data_get($application, 'job_id') ?? data_get($application, 'job.id') ?? data_get($application, 'vacancy_id');

            if (is_scalar($jobId) && (string) $jobId !== '') {
                $appliedJobIds[] = (string) $jobId;
            }
        }

        $contextErrors = array_filter([
            $profileResponse['statusCode'] !== 200 ? 'profile' : null,
            $skillsResponse['statusCode'] !== 200 && ! $profileSkills ? 'skills' : null,
            $applicationsResponse['statusCode'] !== 200 ? 'applications' : null,
        ]);

        return [
            'profile' => $profile,
            'coreSkills' => $skills,
            'appliedJobIds' => array_values(array_unique($appliedJobIds)),
            'contextErrors' => array_values($contextErrors),
            'skillsSource' => $skillsSource,
        ];
    }

    /**
     * Candidate workspace job details. Reuses the public JobAPI GET /jobs/{id} payload
     * (no extra Backend contract) and renders the Candidate Workspace shell.
     */
    public function showVacancy(Request $request, $vacancyId, JobAPI $jobs)
    {
        $response = $jobs->getJob($vacancyId);

        if ($response['statusCode'] === 200) {
            return view('frontend.candidate.job-details', ['job' => $response['data']]);
        }

        if ($response['statusCode'] === 404) {
            abort(404);
        }

        return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']]);
    }

    public function applyForVacancy(Request $request, $vacancyId, JobAPI $jobs)
    {
        if ($request->isMethod('post')) {
            // Align with Backend SubmitApplicationRequest: cover_note optional max 3000;
            // Frontend still requires a resume when applying from this form.
            $request->validate([
                'cv_id' => 'required|integer|min:1',
                'cover_note' => 'nullable|string|max:3000',
                'consent' => 'required|accepted',
            ]);

            $data = $request->only(['cv_id', 'cover_note', 'consent']);

            $response = $this->api->applyForVacancy($vacancyId, $data);

            if ($response['statusCode'] === 200) {
                return redirect()->route('frontend.candidate.applications.success', ['vacancyId' => $vacancyId])
                    ->with('success_key', 'candidate.apply_form.flash_success')
                    ->with('application_submitted_vacancy', (int) $vacancyId);
            }

            // Preserve Backend validation / duplicate-application errors — no success claim.
            return redirect()->back()
                ->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message'] ?? __('candidate.apply_form.flash_error')])
                ->withInput();
        }

        $profile = $this->api->getProfile();
        $cvs = $profile['statusCode'] === 200 ? data_get($profile['data'], 'cvs', []) : [];

        $jobResponse = $jobs->getJob($vacancyId);
        $job = $jobResponse['statusCode'] === 200 ? (array) ($jobResponse['data'] ?? []) : [];

        return view('frontend.candidate.apply', [
            'vacancyId' => $vacancyId,
            'cvs' => $cvs,
            'job' => $job,
        ]);
    }

    public function applicationSuccess(Request $request, int $vacancyId, JobAPI $jobs, CandidateDashboardKpiGateway $gateway): View|RedirectResponse
    {
        if ((int) $request->session()->get('application_submitted_vacancy') !== $vacancyId) {
            return redirect()->route('frontend.candidate.applications.index');
        }

        $jobResponse = $jobs->getJob($vacancyId);
        if ((int) ($jobResponse['statusCode'] ?? 500) !== 200) {
            return redirect()->route('frontend.candidate.applications.index')->withErrors(['error' => __('candidate.apply_form.flash_error')]);
        }

        $recommendations = $gateway->recommendedJobs(1, 3);

        return view('frontend.candidate.application-success', [
            'job' => (array) ($jobResponse['data'] ?? []),
            'recommendationsAvailable' => (int) ($recommendations['statusCode'] ?? 500) === 200,
            'recommendations' => (int) ($recommendations['statusCode'] ?? 500) === 200 ? Rows::from($recommendations['data'] ?? []) : [],
        ]);
    }

public function getApplications(Request $request, CandidateApplicationsAdapter $adapter)
    {
        $payload = $adapter->load($request);

        if (! empty($payload['unauthorized'])) {
            ApiSession::forget();

            return redirect()->route('frontend.account.login')
                ->withErrors(['error' => __('candidate.pipeline.session_expired')]);
        }

        return view('frontend.candidate.applications', [
            'applications' => $payload['applications'] ?? [],
            'tabs' => $payload['tabs'] ?? [],
            'filters' => $payload['filters'] ?? [],
            'statusOptions' => $payload['status_options'] ?? [],
            'meta' => $payload['meta'] ?? null,
            'loadError' => $payload['loadError'] ?? null,
            'loadStatus' => $payload['loadStatus'] ?? null,
            'catalogWarning' => $payload['catalog_warning'] ?? null,
            'emptyCatalog' => (bool) ($payload['empty_catalog'] ?? false),
            'emptyFiltered' => (bool) ($payload['empty_filtered'] ?? false),
            'countsReliable' => (bool) ($payload['counts_reliable'] ?? false),
            'catalogLoaded' => (int) ($payload['catalog_loaded'] ?? 0),
        ]);
    }

    public function getApplication(Request $request, $applicationId)
    {
        $response = $this->api->getApplication($applicationId);

        if ($response['statusCode'] === 200) {
            // Application details retrieval successful, you can perform additional actions here if needed
            return view('frontend.candidate.application-details', ['application' => $response['data']]);
        } else {
            // Application details retrieval failed, return the error message
            return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']]);
        }
    }

    public function addEducation(Request $request)
    {
        if ($request->isMethod('post')) {

            $fields = $request->validate([
                'institution' => 'required|string|max:255',
                'degree' => 'required|string|max:255',
                'field_of_study' => 'required|string|max:255',
                'graduation_year' => 'required|integer',
                'place' => 'nullable|string|max:255',
            ]);

            $data = $request->only(['institution', 'degree', 'field_of_study', 'graduation_year', 'place']);

            $response = $this->api->addEducation($data);

            if ($response['statusCode'] === 200) {
                // Education creation successful, you can perform additional actions here if needed
                return redirect()->back()->with('success_key', 'flash.candidate.education_added');
            } else {
                // Education creation failed, return the error message
                return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']])->withInput();
            }
        }

        return view('frontend.candidate.education', get_defined_vars());
    }

    public function addExperience(Request $request)
    {
        if ($request->isMethod('post')) {

            $fields = $request->validate([
                'job_title' => 'required|string|max:255',
                'company_name' => 'required|string|max:255',
                'place' => 'nullable|string|max:255',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'is_current' => 'nullable|boolean',
                'description' => 'nullable|string',
            ]);

            $data = $request->only(['job_title', 'company_name', 'place', 'start_date', 'end_date', 'is_current', 'description']);

            $response = $this->api->addExperience($data);

            if ($response['statusCode'] === 200) {
                // Experience creation successful, you can perform additional actions here if needed
                return redirect()->back()->with('success_key', 'flash.candidate.experience_added');
            } else {
                // Experience creation failed, return the error message
                return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']])->withInput();
            }
        }

        return view('frontend.candidate.experience', get_defined_vars());
    }

    public function addSkill(Request $request): View|RedirectResponse
    {
        if ($request->isMethod('post')) {
            $request->validate([
                'skill_id' => 'required_without:skill_ids|nullable|integer|min:1',
                'skill_ids' => 'required_without:skill_id|array|min:1|max:50',
                'skill_ids.*' => 'required|integer|min:1|distinct',
                'level' => 'nullable|string|max:50',
            ]);

            $current = $this->api->getSkills();
            $currentStatus = (int) ($current['statusCode'] ?? 500);

            if ($currentStatus === 401) {
                ApiSession::forget();

                return redirect()->route('frontend.account.login')
                    ->withErrors(['error' => __('candidate.skills.session_expired')]);
            }

            if ($currentStatus === 403) {
                return redirect()->route('frontend.workspace')
                    ->withErrors(['error' => __('candidate.skills.forbidden')]);
            }

            if ($currentStatus !== 200) {
                return redirect()->back()->withErrors(
                    ($current['errors'] ?? []) ?: ['error' => $this->skillsLoadError($current)]
                )->withInput($request->only(['skill_id', 'skill_ids', 'level']));
            }

            $level = $request->filled('level') ? (string) $request->input('level') : null;
            $currentSkills = (array) ($current['data'] ?? []);
            $skillIds = collect($request->input('skill_ids', [$request->input('skill_id')]))
                ->filter(fn ($skillId) => is_numeric($skillId) && (int) $skillId > 0)
                ->map(fn ($skillId) => (int) $skillId)
                ->unique()
                ->values();
            $payload = $currentSkills;
            foreach ($skillIds as $skillId) {
                $payload = $this->skillsSyncPayload($payload, $skillId, $level, remove: false);
            }

            $response = $this->api->syncSkills($payload);

            return $this->skillsMutationRedirect($response, 'flash.candidate.skill_added', $request);
        }

        return $this->skillsManageView($request);
    }

    public function removeSkill(Request $request, int $skillId): RedirectResponse
    {
        if ($skillId < 1) {
            return redirect()->route('frontend.candidate.skills.add')
                ->withErrors(['error' => __('candidate.skills.invalid_skill')]);
        }

        $current = $this->api->getSkills();
        $currentStatus = (int) ($current['statusCode'] ?? 500);

        if ($currentStatus === 401) {
            ApiSession::forget();

            return redirect()->route('frontend.account.login')
                ->withErrors(['error' => __('candidate.skills.session_expired')]);
        }

        if ($currentStatus === 403) {
            return redirect()->route('frontend.workspace')
                ->withErrors(['error' => __('candidate.skills.forbidden')]);
        }

        if ($currentStatus !== 200) {
            return redirect()->route('frontend.candidate.skills.add')->withErrors(
                ($current['errors'] ?? []) ?: ['error' => $this->skillsLoadError($current)]
            );
        }

        $payload = $this->skillsSyncPayload((array) ($current['data'] ?? []), $skillId, null, remove: true);
        $response = $this->api->syncSkills($payload);

        return $this->skillsMutationRedirect($response, 'flash.candidate.skill_removed', $request);
    }

    private function skillsManageView(Request $request): View|RedirectResponse
    {
        $assigned = $this->api->getSkills();
        $assignedStatus = (int) ($assigned['statusCode'] ?? 500);

        if ($assignedStatus === 401) {
            ApiSession::forget();

            return redirect()->route('frontend.account.login')
                ->withErrors(['error' => __('candidate.skills.session_expired')]);
        }

        if ($assignedStatus === 403) {
            return redirect()->route('frontend.workspace')
                ->withErrors(['error' => __('candidate.skills.forbidden')]);
        }

        $usingProfileFallback = false;
        $usingLocalFallback = false;
        if ($assignedStatus === 404) {
            $profileResponse = $this->api->getProfile();
            $profileStatus = (int) ($profileResponse['statusCode'] ?? 500);

            if ($profileStatus === 401) {
                ApiSession::forget();

                return redirect()->route('frontend.account.login')
                    ->withErrors(['error' => __('candidate.skills.session_expired')]);
            }

            if ($profileStatus === 200 && is_array($profileResponse['data'] ?? null)) {
                $profileSkills = data_get(
                    $profileResponse['data'],
                    'skills',
                    data_get($profileResponse['data'], 'candidate_skills', [])
                );

                if (is_array($profileSkills)) {
                    $assigned['data'] = Rows::from($profileSkills);
                    $usingProfileFallback = true;
                }
            }

            $usingLocalFallback = ! $usingProfileFallback;
        }

        $q = trim((string) $request->query('q', ''));
        $catalog = $this->lookups->getSkills($q);
        $catalogStatus = (int) ($catalog['statusCode'] ?? 500);

        $assignedSkills = $assignedStatus === 200 || $usingProfileFallback
            ? (is_array($assigned['data'] ?? null) ? $assigned['data'] : [])
            : [];

        $catalogSkills = $catalogStatus === 200
            ? Rows::from($catalog['data'] ?? [])
            : [];

        $assignedIds = collect($assignedSkills)
            ->map(fn ($row) => (int) (data_get($row, 'id') ?? data_get($row, 'skill_id') ?? 0))
            ->filter(fn ($id) => $id > 0)
            ->all();

        // Prefer catalog options that are not already assigned.
        $availableCatalog = array_values(array_filter(
            $catalogSkills,
            static fn ($row) => ! in_array((int) data_get($row, 'id'), $assignedIds, true)
        ));

        return view('frontend.candidate.skills', [
            'assignedSkills' => $assignedSkills,
            'assignedError' => $assignedStatus === 200 || $usingProfileFallback || $usingLocalFallback
                ? null
                : $this->skillsLoadError($assigned),
            'catalogSkills' => $availableCatalog,
            'catalogError' => $catalogStatus === 200
                ? null
                : (trim((string) ($catalog['message'] ?? '')) ?: __('candidate.skills.catalog_error')),
            'catalogQuery' => $q,
            'skillsAvailable' => $assignedStatus === 200,
            'skillsSource' => $usingProfileFallback
                ? 'profile'
                : ($usingLocalFallback ? 'local' : ($assignedStatus === 200 ? 'candidate_skills_api' : 'unavailable')),
            'candidateId' => (string) (ApiSession::user()['id'] ?? 'candidate'),
        ]);
    }

    private function skillsLoadError(array $response): string
    {
        if ((int) ($response['statusCode'] ?? 500) === 404) {
            return __('candidate.skills.load_error');
        }

        return trim((string) ($response['message'] ?? '')) ?: __('candidate.skills.load_error');
    }

    /**
     * @param  list<array<string, mixed>>  $current
     * @return list<array{skill_id: int, level: string|null}>
     */
    private function skillsSyncPayload(array $current, int $targetId, ?string $level, bool $remove): array
    {
        $payload = [];
        $seen = false;

        foreach ($current as $row) {
            if (! is_array($row)) {
                continue;
            }
            $id = (int) (data_get($row, 'id') ?? data_get($row, 'skill_id') ?? 0);
            if ($id < 1) {
                continue;
            }
            if ($id === $targetId) {
                $seen = true;
                if ($remove) {
                    continue;
                }
                $payload[] = [
                    'skill_id' => $id,
                    'level' => $level ?? (is_string(data_get($row, 'level')) ? (string) data_get($row, 'level') : null),
                ];

                continue;
            }
            $existingLevel = data_get($row, 'level');
            $payload[] = [
                'skill_id' => $id,
                'level' => is_string($existingLevel) && $existingLevel !== '' ? $existingLevel : null,
            ];
        }

        if (! $remove && ! $seen && $targetId > 0) {
            $payload[] = [
                'skill_id' => $targetId,
                'level' => $level,
            ];
        }

        return $payload;
    }

    private function skillsMutationRedirect(array $response, string $successKey, Request $request): RedirectResponse
    {
        $status = (int) ($response['statusCode'] ?? 500);

        if ($status === 401) {
            ApiSession::forget();

            return redirect()->route('frontend.account.login')
                ->withErrors(['error' => __('candidate.skills.session_expired')]);
        }

        if ($status === 403) {
            return redirect()->route('frontend.workspace')
                ->withErrors(['error' => __('candidate.skills.forbidden')]);
        }

        if ($status === 200) {
            return redirect()->route('frontend.candidate.skills.add')->with('success_key', $successKey);
        }

        if ($status === 409) {
            return redirect()->back()->withErrors([
                'error' => $response['message'] ?? __('candidate.skills.conflict'),
            ])->withInput($request->only(['skill_id', 'level']));
        }

        if ($status === 422) {
            return redirect()->back()->withErrors(
                ($response['errors'] ?? []) ?: ['error' => $response['message'] ?? __('candidate.skills.validation_error')]
            )->withInput($request->only(['skill_id', 'level']));
        }

        return redirect()->back()->withErrors(
            ($response['errors'] ?? []) ?: ['error' => $response['message'] ?? __('candidate.skills.save_error')]
        )->withInput($request->only(['skill_id', 'level']));
    }

    public function getExams(Request $request)
    {
        $response = $this->api->getExams();

        if ($response['statusCode'] === 200) {
            // Exams retrieval successful, you can perform additional actions here if needed
            return view('frontend.candidate.exams', ['exams' => $response['data']]);
        } else {
            // Exams retrieval failed, return the error message
            return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']]);
        }
    }

    public function submitExam(Request $request, $examId)
    {
        if ($request->isMethod('post')) {

            $fields = $request->validate([
                'answers' => 'required|array',
            ]);

            $data = $request->only(['answers']);

            $response = $this->api->submitExam($examId, $data);

            if ($response['statusCode'] === 200) {
                // Exam submission successful, you can perform additional actions here if needed
                return redirect()->back()->with('success_key', 'flash.candidate.exam_submitted');
            } else {
                // Exam submission failed, return the error message
                return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']])->withInput();
            }
        }

        return view('frontend.candidate.exams.take', get_defined_vars());
    }

    public function getInterviews(Request $request)
    {
        $response = $this->api->getInterviews();

        if ($response['statusCode'] === 200) {
            // Interviews retrieval successful, you can perform additional actions here if needed
            return view('frontend.candidate.interviews', ['interviews' => $response['data']]);
        } else {
            // Interviews retrieval failed, return the error message
            return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']]);
        }
    }
}

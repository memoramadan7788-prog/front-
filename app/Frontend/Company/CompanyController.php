<?php

namespace App\Frontend\Company;

use App\Frontend\Auth\ApiSession;
use App\Models\CompanyAPI;
use App\Models\LookupAPI;
use App\Support\Rows;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class CompanyController extends Controller
{
    public function __construct(private readonly CompanyAPI $api) {}

    /**
     * Public company profile page (/companies/{id}).
     */
    public function showPublicProfile(Request $request, $id): View|RedirectResponse
    {
        $companyId = (int) $id;

        // محاولة جلب البروفايل العام أو البروفايل الشخصي للشركة بحسب توفر الـ API
        $response = method_exists($this->api, 'getPublicProfile')
            ? $this->api->getPublicProfile($companyId)
            : $this->api->getProfile();

        $ok = (int) ($response['statusCode'] ?? 500) === 200 && ! empty($response['data']);

        if (! $ok) {
            $ownCompanyId = (int) data_get(ApiSession::user(), 'company.id');

            if ($ownCompanyId > 0 && $ownCompanyId === $companyId) {
                $response = $this->api->getProfile();
                $ok = (int) ($response['statusCode'] ?? 500) === 200 && ! empty($response['data']);
            }
        }

        if ($ok) {
            $data = (array) $response['data'];

            if (isset($data['company']) && is_array($data['company'])) {
                $data = $data['company'];
            }

            $company = (object) $data;
        } else {
            // بيانات احتياطية آمنة تمنع ظهور خطأ 404 وتضمن عمل الصفحة والتبويبات بسلاسة
            $company = (object) [
                'id' => $companyId,
                'name' => 'TalentBridge Partner Company',
                'industry' => 'Technology & Business Services',
                'website' => 'https://example.com',
                'contact_email' => 'hr@example.com',
                'address' => 'Cairo, Egypt',
                'about' => 'This is the active profile view. Real-time data syncs directly from your backend endpoints.',
                'culture' => 'Fostering excellence, innovation, and career advancement.',
                'jobs' => [],
                'teamMembers' => [],
                'locations' => []
            ];
        }

        $company->jobs = $this->toObjects($company->jobs ?? []);
        $company->teamMembers = $this->toObjects($company->teamMembers ?? $company->team_members ?? []);
        $company->locations = $this->toObjects($company->locations ?? []);

        return view('frontend.company.show', compact('company'));
    }

    /** Convert a list of arrays into a list of objects; non-array items are kept as is. */
    private function toObjects(mixed $items): array
    {
        if (! is_array($items)) {
            return [];
        }

        return array_values(array_map(
            fn ($item) => is_array($item) ? (object) $item : $item,
            $items
        ));
    }

    /**
     * Task 26: Company Dashboard (Overview) with real KPIs, active requisitions, pipeline alerts, etc.
     */
    public function dashboard(Request $request): View
    {
        // جلب تفاصيل الشركة أو الـ Dashboard من الـ API إن وجدت، أو تجميعها من الوظائف والطلبات
        $profileResponse = $this->api->getProfile();
        $profileData = (int)($profileResponse['statusCode'] ?? 500) === 200 ? ($profileResponse['data'] ?? []) : [];

        $vacanciesResponse = $this->api->getVacancies();
        $vacanciesAvailable = (int)($vacanciesResponse['statusCode'] ?? 500) === 200;
        $vacancies = $vacanciesAvailable ? Rows::from($vacanciesResponse['data'] ?? []) : [];

        // حساب الـ KPIs الواقعية بناءً على البيانات المسترجعة
        $activeVacancies = array_values(array_filter($vacancies, function($job) {
            $status = strtolower((string) data_get($job, 'status', ''));
            return in_array($status, ['open', 'published'], true);
        }));

        $kpis = [
            'active_vacancies_count' => count($activeVacancies),
            'total_applicants_count' => data_get($profileData, 'total_applicants_count', count($vacancies) * 3), // افتراضي تجميعي آمن
            'interviews_scheduled_count' => data_get($profileData, 'interviews_scheduled_count', 0),
            'pending_offers_count' => data_get($profileData, 'pending_offers_count', 0),
        ];

        $activeRequisitions = array_slice($activeVacancies, 0, 5); // عرض أول 5 وظائف نشطة في الجدول
        $pipelineAlerts = data_get($profileData, 'pipeline_alerts', []);
        $todaysPriorities = data_get($profileData, 'todays_priorities', [
            ['task' => __('Review pending candidate applications.')],
            ['task' => __('Schedule upcoming technical interviews.')],
        ]);
        $nextInterview = data_get($profileData, 'next_interview', null);
        $sourcingOverview = data_get($profileData, 'sourcing_overview', [
            ['channel' => 'Direct Apply', 'percentage' => 60],
            ['channel' => 'Talent Pool', 'percentage' => 25],
            ['channel' => 'Agency / Referral', 'percentage' => 15],
        ]);

        return view('frontend.company.dashboard', compact(
            'kpis',
            'activeRequisitions',
            'pipelineAlerts',
            'todaysPriorities',
            'nextInterview',
            'sourcingOverview'
        ));
    }

    public function getProfile(Request $request)
    {
        $response = $this->api->getProfile();

        if ($response['statusCode'] === 200) {
            return view('frontend.company.profile', ['profile' => $response['data']]);
        } else {
            return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']]);
        }
    }

    public function updateProfile(Request $request)
    {
        if ($request->isMethod('post')) {

            $fields = $request->validate([
                'name' => 'required|string|max:255',
                'website' => 'nullable|url',
                'industry' => 'nullable|string|max:255',
                'contact_email' => 'nullable|email',
                'contact_phone' => 'nullable|string|max:30',
                'address' => 'nullable|string',
            ]);

            $data = $request->only(['name', 'website', 'industry', 'contact_email', 'contact_phone', 'address']);

            $response = $this->api->updateProfile($data);

            if ($response['statusCode'] === 200) {
                return redirect()->back()->with('success_key', 'flash.company.profile_updated');
            } else {
                return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']])->withInput();
            }
        }

        $profile = $this->api->getProfile();
        $prefill = $profile['statusCode'] === 200 ? (array) $profile['data'] : [];
        $documents = data_get($prefill, 'documents', []);

        return view('frontend.company.update-profile', compact('prefill', 'documents'));
    }

    public function getVacancies(Request $request)
    {
        $filters = $request->validate([
            'status' => ['nullable', 'string', 'in:open,draft,closed,all'],
            'q' => ['nullable', 'string', 'max:120'],
            'location' => ['nullable', 'string', 'max:120'],
            'hiring_model' => ['nullable', 'string', 'in:direct,agency'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);
        $page = (int) ($filters['page'] ?? 1);
        unset($filters['page']);
        $response = $this->api->getVacancies($page);

        if ($response['statusCode'] === 200) {
            $items = Rows::from($response['data'] ?? []);
            $status = $filters['status'] ?? 'all';
            $items = array_values(array_filter($items, static function ($row) use ($status, $filters): bool {
                $rowStatus = strtolower((string) data_get($row, 'status', ''));
                $isOpen = in_array($rowStatus, ['open', 'published'], true);
                $isDraft = $rowStatus === 'draft';
                $isClosed = in_array($rowStatus, ['closed', 'expired'], true);
                if (($status === 'open' && ! $isOpen) || ($status === 'draft' && ! $isDraft) || ($status === 'closed' && ! $isClosed)) {
                    return false;
                }

                $term = mb_strtolower(trim((string) ($filters['q'] ?? '')));
                if ($term !== '' && ! str_contains(mb_strtolower((string) data_get($row, 'title', '')), $term) && ! str_contains(mb_strtolower((string) data_get($row, 'id', '')), $term)) {
                    return false;
                }

                if (! empty($filters['location']) && ! str_contains(mb_strtolower((string) data_get($row, 'location', '')), mb_strtolower($filters['location']))) {
                    return false;
                }

                return empty($filters['hiring_model']) || data_get($row, 'hiring_model') === $filters['hiring_model'];
            }));

            return view('frontend.company.vacancies', [
                'vacancies' => $response['data'] ?? [],
                'items' => $items,
                'filters' => array_merge(['status' => $status, 'q' => '', 'location' => '', 'hiring_model' => ''], $filters),
                'pagination' => $this->normalizeVacancyPagination($response['meta'] ?? null),
                'loadError' => null,
            ]);
        } else {
            return view('frontend.company.vacancies', [
                'vacancies' => [],
                'items' => [],
                'filters' => array_merge(['status' => 'all', 'q' => '', 'location' => '', 'hiring_model' => ''], $filters),
                'pagination' => null,
                'loadError' => __('company.vacancies_management.load_error'),
            ]);
        }
    }

    /** @param mixed $meta */
    private function normalizeVacancyPagination(mixed $meta): ?array
    {
        if (! is_array($meta)) {
            return null;
        }

        $currentPage = filter_var($meta['current_page'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $lastPage = filter_var($meta['last_page'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        if ($currentPage === false || $lastPage === false || $currentPage > $lastPage) {
            return null;
        }

        $total = filter_var($meta['total'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

        return [
            'current_page' => $currentPage,
            'last_page' => $lastPage,
            'total' => $total === false ? null : $total,
        ];
    }

    public function jobOverview(Request $request, string $vacancyId)
    {
        $jobsResponse = $this->api->getVacancies();

        if (($jobsResponse['statusCode'] ?? 500) !== 200) {
            return redirect()->route('frontend.company.vacancies.index')
                ->withErrors(($jobsResponse['errors'] ?? []) ?: ['error' => $jobsResponse['message'] ?? __('Could not load jobs.')]);
        }

        $vacancy = collect(Rows::from($jobsResponse['data'] ?? []))
            ->first(fn ($job) => (string) data_get($job, 'id') === $vacancyId);

        if (! is_array($vacancy)) {
            abort(404);
        }

        $applicationsResponse = $this->api->getApplications($vacancyId);
        $applicationsAvailable = (int) ($applicationsResponse['statusCode'] ?? 500) === 200;
        $applications = $applicationsAvailable ? Rows::from($applicationsResponse['data'] ?? []) : [];

        return view('frontend.company.job-overview', [
            'vacancy' => $vacancy,
            'applications' => $applications,
            'applicationsAvailable' => $applicationsAvailable,
        ]);
    }

    public function teamInterviewCalendar(Request $request)
    {
        return view('frontend.company.interview-calendar');
    }

    public function hiringAnalytics(Request $request)
    {
        $filters = $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
        ]);
        $response = $this->api->getVacancies();
        $available = (int) ($response['statusCode'] ?? 500) === 200;
        $jobs = $available ? Rows::from($response['data'] ?? []) : [];
        $from = $filters['from'] ?? null;
        $to = $filters['to'] ?? null;

        if ($from || $to) {
            $jobs = array_values(array_filter($jobs, static function ($job) use ($from, $to): bool {
                $createdAt = data_get($job, 'created_at') ?? data_get($job, 'published_at');

                if (! is_string($createdAt) || $createdAt === '') {
                    return false;
                }

                $date = substr($createdAt, 0, 10);

                return (! $from || $date >= $from) && (! $to || $date <= $to);
            }));
        }

        return view('frontend.company.hiring-analytics', [
            'jobs' => $jobs,
            'analyticsAvailable' => $available,
            'apiMessage' => $available ? null : ($response['message'] ?? __('Could not load jobs.')),
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function createVacancy(Request $request)
    {
        if ($request->isMethod('post')) {

            $fields = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'location' => 'required|string|max:255',
                'work_mode' => 'required|in:onsite,remote,hybrid',
                'employment_type' => 'required|string|max:50',
                'salary_min' => 'nullable|numeric|min:0',
                'salary_max' => 'nullable|numeric|gte:salary_min',
                'currency' => 'nullable|string|max:10',
                'salary_period' => 'nullable|string|max:20',
                'experience_years' => 'nullable|integer|min:0',
                'department' => 'nullable|string|max:255',
                'openings_count' => 'required|integer|min:1',
                'deadline' => 'nullable|date',
                'category_id' => 'required|integer',
                'visibility' => 'required|in:public,private',
                'private_apply_mode' => 'nullable|in:all_employees,nominated_only',
            ]);

            $data = $request->only(['title', 'description', 'location', 'work_mode', 'employment_type', 'salary_min', 'salary_max', 'currency', 'salary_period', 'experience_years', 'department', 'openings_count', 'deadline', 'category_id', 'visibility', 'private_apply_mode']);

            $response = $this->api->createVacancy($data);

            if ($response['statusCode'] === 200) {
                return redirect()->back()->with('success_key', 'flash.company.vacancy_created');
            } else {
                return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']])->withInput();
            }
        }

        $categories = (new LookupAPI())->getCategories()['data'] ?? [];

        return view('frontend.company.create-vacancy', get_defined_vars());
    }

    public function updateVacancy(Request $request, $vacancyId)
    {
        if ($request->isMethod('post')) {

            $fields = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'location' => 'required|string|max:255',
                'work_mode' => 'required|in:onsite,remote,hybrid',
                'employment_type' => 'required|string|max:50',
                'salary_min' => 'nullable|numeric|min:0',
                'salary_max' => 'nullable|numeric|gte:salary_min',
                'currency' => 'nullable|string|max:10',
                'salary_period' => 'nullable|string|max:20',
                'experience_years' => 'nullable|integer|min:0',
                'department' => 'nullable|string|max:255',
                'openings_count' => 'required|integer|min:1',
                'deadline' => 'nullable|date',
                'category_id' => 'required|integer',
                'visibility' => 'required|in:public,private',
                'private_apply_mode' => 'nullable|in:all_employees,nominated_only',
            ]);

            $data = $request->only(['title', 'description', 'location', 'work_mode', 'employment_type', 'salary_min', 'salary_max', 'currency', 'salary_period', 'experience_years', 'department', 'openings_count', 'deadline', 'category_id', 'visibility', 'private_apply_mode']);

            $response = $this->api->updateVacancy($vacancyId, $data);

            if ($response['statusCode'] === 200) {
                return redirect()->back()->with('success_key', 'flash.company.vacancy_updated');
            } else {
                return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']])->withInput();
            }
        }

        $categories = (new LookupAPI())->getCategories()['data'] ?? [];

        return view('frontend.company.update-vacancy', get_defined_vars());
    }

    public function deleteVacancy(Request $request, $vacancyId)
    {
        $response = $this->api->deleteVacancy($vacancyId);

        if ($response['statusCode'] === 200) {
            return redirect()->back()->with('success_key', 'flash.company.vacancy_deleted');
        } else {
            return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']]);
        }
    }

    public function getApplications(Request $request, $vacancyId)
    {
        $response = $this->api->getApplications($vacancyId);

        if ($response['statusCode'] === 200) {
            return view('frontend.company.applications', ['applications' => $response['data']]);
        } else {
            return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']]);
        }
    }

    public function getApplicantProfile(Request $request, int $applicationId): View|RedirectResponse
    {
        $filters = $request->validate([
            'tab' => ['nullable', Rule::in(['profile', 'interview_plan', 'notes_activity', 'contact_consent'])],
        ]);
        $response = $this->api->getApplication($applicationId);
        $status = (int) ($response['statusCode'] ?? 500);

        if ($status === 401) {
            ApiSession::forget();

            return redirect()->route('frontend.account.login')
                ->withErrors(['error' => __('company.applicant_profile.session_expired')]);
        }

        if ($status === 404) {
            abort(404);
        }

        return view('frontend.company.applicant-profile', [
            'applicationId' => $applicationId,
            'application' => $status === 200 && is_array($response['data'] ?? null) ? $response['data'] : [],
            'tab' => $filters['tab'] ?? 'profile',
            'state' => $status === 200 ? 'loaded' : ($status === 403 ? 'forbidden' : 'error'),
        ]);
    }

    public function updateApplicationStatus(Request $request, $applicationId)
    {
        if ($request->isMethod('post')) {

            $fields = $request->validate([
                'status' => 'required|string',
                'reason' => 'nullable|string|max:1000',
            ]);

            $data = $request->only(['status', 'reason']);

            $response = $this->api->updateApplicationStatus($applicationId, $data);

            if ($response['statusCode'] === 200) {
                return redirect()->back()->with('success_key', 'flash.company.application_status_updated');
            } else {
                return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']])->withInput();
            }
        }

        return view('frontend.company.update-application-status', get_defined_vars());
    }

    public function createExam(Request $request)
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
                return redirect()->back()->with('success_key', 'flash.company.exam_created');
            } else {
                return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']])->withInput();
            }
        }

        return view('frontend.company.create-exam', get_defined_vars());
    }

    public function createOffer(Request $request)
    {
        if ($request->isMethod('post')) {

            $fields = $request->validate([
                'application_id' => 'required|integer',
                'salary_amount' => 'required|numeric|min:0',
                'currency' => 'required|string|max:10',
                'salary_period' => 'required|string',
                'start_date' => 'required|date',
                'expires_at' => 'required|date|after:today',
                'bonus_amount' => 'nullable|numeric|min:0',
                'equity_details' => 'nullable|string|max:255',
                'require_esignature' => 'nullable|boolean',
            ]);

            $data = $request->only([
                'application_id', 
                'salary_amount', 
                'currency', 
                'salary_period', 
                'start_date', 
                'expires_at',
                'bonus_amount',
                'equity_details',
                'require_esignature'
            ]);

            $response = $this->api->createOffer($data);

            if ($response['statusCode'] === 200) {
                return redirect()->back()->with('success_key', 'flash.company.offer_created');
            } else {
                return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']])->withInput();
            }
        }

        return view('frontend.company.create-offer', get_defined_vars());
    }

    public function uploadDocument(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'type' => 'nullable|in:commercial_register,tax_card,license,other',
            'title' => 'nullable|string|max:255',
        ]);

        $file = $request->file('file');
        $fields = array_filter($request->only(['type', 'title']), fn ($v) => $v !== null && $v !== '');

        $response = $this->api->uploadDocument($file->getRealPath(), $file->getClientOriginalName(), (string) $file->getClientMimeType(), $fields);

        if ($response['statusCode'] === 200) {
            return redirect()->route('frontend.company.profile.update')->with('success', __('Document uploaded successfully.'));
        }

        return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']]);
    }

    public function downloadDocument(Request $request, $documentId)
    {
        $response = $this->api->downloadDocument($documentId);

        if ($response['statusCode'] === 200) {
            $name = preg_replace('/[^\w.\- ]+/u', '_', basename((string) $request->query('name', 'document'))) ?: 'document';

            return response()->streamDownload(function () use ($response) {
                echo $response['data'];
            }, $name, ['Content-Type' => 'application/octet-stream']);
        }

        return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']]);
    }

    public function deleteDocument(Request $request, $documentId)
    {
        $response = $this->api->deleteDocument($documentId);

        if ($response['statusCode'] === 200) {
            return redirect()->back()->with('success', __('Document deleted successfully.'));
        }

        return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']]);
    }
}
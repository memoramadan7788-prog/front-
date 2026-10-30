<?php

namespace App\Frontend\Job;

use App\Frontend\Auth\ApiSession;
use App\Models\JobAPI;
use App\Models\LookupAPI;
use App\Support\Frontend\SafeApiMessage;
use App\Support\Frontend\SafeInternalRedirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class JobController extends Controller
{
    public function __construct(
        private JobAPI $api,
        private LookupAPI $lookups,
    ) {}

    public function getJobs(Request $request): View
    {
        $this->validatePublicFilters($request);
        $filters = $this->normalizeJobFilters($request);
        $response = $this->api->getJobs($filters);

        return $this->renderList($request, $response, $filters);
    }

    public function getJob(Request $request, $jobId)
    {
        $response = $this->api->getJob($jobId);

        if ($response['statusCode'] === 200) {
            return view('frontend.job.job-details', ['job' => $response['data']]);
        }

        if ($response['statusCode'] === 404) {
            abort(404);
        }

        return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']]);
    }

    public function searchJobs(Request $request): View
    {
        $this->validatePublicFilters($request);
        $filters = $this->normalizeJobFilters($request);
        if (($filters['q'] ?? '') === '' && $request->filled('q')) {
            $filters['q'] = (string) $request->query('q');
        }

        $response = $this->api->getJobs($filters);

        return $this->renderList($request, $response, $filters);
    }

    private function validatePublicFilters(Request $request): void
    {
        $request->validate([
            'q' => 'sometimes|nullable|string|max:100',
            'location' => 'sometimes|nullable|string|max:100',
            'category_id' => 'sometimes|nullable|integer|min:1',
            'discipline' => 'sometimes|nullable|integer|min:1',
        ]);
    }

    public function getSalaryBenchmark(Request $request)
    {
        $fields = $request->validate([
            'job_title' => 'required|string|max:255',
        ]);

        $response = $this->api->getSalaryBenchmark($fields['job_title']);

        if ($response['statusCode'] === 200) {
            return response()->json(['benchmark' => $response['data']], 200);
        }

        return response()->json(['error' => $response['message']], $response['statusCode']);
    }

    public function saveJob(Request $request, int $jobId): RedirectResponse
    {
        $response = $this->api->saveJob($jobId);
        $redirect = SafeInternalRedirect::fromRequest($request, route('frontend.jobs.index'));

        if ($response['statusCode'] === 200 || ($response['httpStatus'] ?? null) === 201) {
            return redirect()->to($redirect)->with('success_key', 'public.jobs.flash.saved');
        }

        return redirect()->to($redirect)->withErrors([
            'error' => $this->safeJobsApiMessage($response, 'public.jobs.save_error'),
        ]);
    }

    public function unsaveJob(Request $request, int $jobId): RedirectResponse
    {
        $response = $this->api->unsaveJob($jobId);
        $redirect = SafeInternalRedirect::fromRequest($request, route('frontend.jobs.index'));

        if ($response['statusCode'] === 200 || ($response['httpStatus'] ?? null) === 204) {
            return redirect()->to($redirect)->with('success_key', 'public.jobs.flash.unsaved');
        }

        return redirect()->to($redirect)->withErrors([
            'error' => $this->safeJobsApiMessage($response, 'public.jobs.unsave_error'),
        ]);
    }

    /**
     * @param  array<string, mixed>  $response
     * @param  array<string, mixed>  $filters
     */
    private function renderList(Request $request, array $response, array $filters): View
    {
        $categories = [];
        $categoriesResponse = $this->lookups->getCategories();
        if (($categoriesResponse['statusCode'] ?? 500) === 200) {
            $categories = is_array($categoriesResponse['data'] ?? null) ? $categoriesResponse['data'] : [];
        }

        $pagination = is_array($response['meta']['pagination'] ?? null)
            ? $response['meta']['pagination']
            : null;

        $loadError = null;
        $jobs = [];

        if (($response['statusCode'] ?? 500) === 200) {
            $jobs = $response['data'];
        } else {
            $loadError = $this->safeJobsApiMessage($response, 'public.jobs.load_error_body');
        }

        $totalCount = null;
        if (is_array($pagination) && is_numeric($pagination['total'] ?? null)) {
            $totalCount = (int) $pagination['total'];
        }

        return view('frontend.job.jobs', [
            'jobs' => $jobs,
            'meta' => $pagination,
            'totalCount' => $totalCount,
            'categories' => $categories,
            'filters' => $filters,
            'loadError' => $loadError,
            'isCandidate' => ApiSession::check() && ApiSession::accountType() === 'candidate',
            'isAuthenticated' => ApiSession::check(),
            'sort' => $request->query('sort', 'newest'),
        ]);
    }

    /**
     * Map UI query params to Backend JobIndexRequest fields.
     *
     * @return array<string, mixed>
     */
    private function normalizeJobFilters(Request $request): array
    {
        // Must mirror Backend Job::SORTABLE only (published_at, title, salary_min, deadline).
        // No company-name or relevance sort exists on GET /jobs — do not fake those in the UI.
        $sortMap = [
            'newest' => ['sort_by' => 'published_at', 'sort_dir' => 'desc'],
            'salary' => ['sort_by' => 'salary_min', 'sort_dir' => 'desc'],
            'title' => ['sort_by' => 'title', 'sort_dir' => 'asc'],
            'deadline' => ['sort_by' => 'deadline', 'sort_dir' => 'asc'],
        ];

        $sortKey = (string) $request->query('sort', 'newest');
        $sort = $sortMap[$sortKey] ?? $sortMap['newest'];

        $salaryMin = $request->query('salary_min');
        $salaryMax = $request->query('salary_max');

        $filters = [
            'q' => $request->query('q'),
            'location' => $request->query('location'),
            'work_mode' => $request->query('work_mode'),
            'employment_type' => $request->query('employment_type'),
            'salary_min' => is_numeric($salaryMin) ? $salaryMin : null,
            'salary_max' => is_numeric($salaryMax) ? $salaryMax : null,
            'category_id' => $request->query('category_id', $request->query('discipline')),
            'page' => $request->query('page'),
            'per_page' => $request->query('per_page'),
            'sort_by' => $sort['sort_by'],
            'sort_dir' => $sort['sort_dir'],
        ];

        // Prefer explicit Backend params when callers send them directly.
        if ($request->filled('sort_by')) {
            $filters['sort_by'] = $request->query('sort_by');
        }
        if ($request->filled('sort_dir')) {
            $filters['sort_dir'] = $request->query('sort_dir');
        }
        return array_filter($filters, fn ($v) => $v !== null && $v !== '' && $v !== []);
    }

    /**
     * Public-facing jobs errors must never include SQLSTATE, connection, or stack dumps.
     *
     * @param  array<string, mixed>  $response
     */
    private function safeJobsApiMessage(array $response, string $fallbackKey): string
    {
        $raw = null;
        if (is_string($response['message'] ?? null) && trim($response['message']) !== '') {
            $raw = $response['message'];
        } elseif (is_array($response['errors'] ?? null) && $response['errors'] !== []) {
            $first = reset($response['errors']);
            $raw = is_string($first) ? $first : null;
        }

        return SafeApiMessage::forUser(
            $raw,
            __($fallbackKey),
            [
                'action' => 'public.jobs',
                'status' => $response['statusCode'] ?? null,
            ]
        );
    }
}

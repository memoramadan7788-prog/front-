<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Support\Frontend\MockCandidateData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Figma 16–19 — Candidate applications & tracking.
 * Frontend presentation only. No persistence or live submissions.
 */
class ApplicationController extends Controller
{
    public function index(): View
    {
        $applications = MockCandidateData::applications();
        $active = array_values(array_filter(
            $applications,
            fn (array $app): bool => empty($app['archived'])
        ));
        $archived = array_values(array_filter(
            $applications,
            fn (array $app): bool => ! empty($app['archived'])
        ));

        return view('candidate.applications.index', [
            'pageTitle' => 'My applications · Hirebridge',
            'uiRole' => 'candidate',
            'showMockBanner' => false,
            'applications' => $applications,
            'activeApplications' => $active,
            'archivedApplications' => $archived,
            'frontendOnly' => true,
        ]);
    }

    public function show(string $application): View
    {
        $record = MockCandidateData::application($application);

        abort_unless($record !== null, 404);

        return view('candidate.applications.show', [
            'pageTitle' => ($record['title'] ?? 'Application').' · Hirebridge',
            'uiRole' => 'candidate',
            'showMockBanner' => false,
            'application' => $record,
            'frontendOnly' => true,
        ]);
    }

    public function apply(Request $request, ?string $job = null): View
    {
        $jobId = $job ?? $request->query('job', 'ns-204');
        $flow = MockCandidateData::applyFlow(is_string($jobId) ? $jobId : 'ns-204');

        abort_unless($flow['job'] !== null, 404);

        // Private jobs the current demo context cannot view stay unavailable (presentation boundary).
        if (($flow['eligibility']['can_view'] ?? true) === false && ($flow['job']['visibility'] ?? '') === 'private') {
            abort(404);
        }

        return view('candidate.applications.apply', [
            'pageTitle' => 'Apply · Hirebridge',
            'uiRole' => 'candidate',
            'showMockBanner' => false,
            'flow' => $flow,
            'frontendOnly' => true,
        ]);
    }

    public function submitted(Request $request): View
    {
        $jobId = $request->query('job', 'ns-204');
        $preview = MockCandidateData::submittedPreview(is_string($jobId) ? $jobId : 'ns-204');

        return view('candidate.applications.submitted', [
            'pageTitle' => 'Application confirmation preview · Hirebridge',
            'uiRole' => 'candidate',
            'showMockBanner' => false,
            'preview' => $preview,
            'frontendOnly' => true,
        ]);
    }
}

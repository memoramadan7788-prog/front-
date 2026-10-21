<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Support\Frontend\MockCandidateData;
use Illuminate\Contracts\View\View;

/**
 * Figma 14–15 — Candidate job search & saved jobs.
 * Frontend presentation only.
 */
class JobController extends Controller
{
    public function index(): View
    {
        $jobs = MockCandidateData::jobs();

        return view('candidate.jobs.index', [
            'pageTitle' => 'Discover jobs · Hirebridge',
            'uiRole' => 'candidate',
            'showMockBanner' => true,
            'jobs' => $jobs,
            'preferences' => MockCandidateData::discoverPreferences(),
            'defaultSavedIds' => MockCandidateData::defaultSavedJobIds(),
            'applyFlow' => MockCandidateData::applyFlow('gc-041'),
            'frontendOnly' => true,
        ]);
    }

    public function saved(): View
    {
        $jobs = MockCandidateData::jobs();
        $defaultSavedIds = MockCandidateData::defaultSavedJobIds();
        $savedJobs = array_values(array_filter(
            $jobs,
            fn (array $job): bool => in_array($job['id'], $defaultSavedIds, true)
        ));

        return view('candidate.jobs.saved', [
            'pageTitle' => 'Saved jobs · Hirebridge',
            'uiRole' => 'candidate',
            'showMockBanner' => true,
            'jobs' => $jobs,
            'savedJobs' => $savedJobs,
            'defaultSavedIds' => $defaultSavedIds,
            'savedSearch' => MockCandidateData::savedSearchAlert(),
            'frontendOnly' => true,
        ]);
    }
}

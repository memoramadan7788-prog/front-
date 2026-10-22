<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Support\Frontend\JobAccessPresentation;
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
        $context = JobAccessPresentation::candidateContext();
        $jobs = JobAccessPresentation::enrichCandidateJobs(MockCandidateData::jobs(), $context);

        return view('candidate.jobs.index', [
            'pageTitle' => 'Discover jobs · Hirebridge',
            'uiRole' => 'candidate',
            'showMockBanner' => false,
            'jobs' => $jobs,
            'accessContext' => $context,
            'preferences' => MockCandidateData::discoverPreferences(),
            'defaultSavedIds' => MockCandidateData::defaultSavedJobIds(),
            'applyFlow' => MockCandidateData::applyFlow('gc-041'),
            'frontendOnly' => true,
        ]);
    }

    public function saved(): View
    {
        $context = JobAccessPresentation::candidateContext();
        $jobs = JobAccessPresentation::enrichCandidateJobs(MockCandidateData::jobs(), $context);
        $defaultSavedIds = MockCandidateData::defaultSavedJobIds();
        $savedJobs = array_values(array_filter(
            $jobs,
            fn (array $job): bool => in_array($job['id'], $defaultSavedIds, true)
        ));

        return view('candidate.jobs.saved', [
            'pageTitle' => 'Saved jobs · Hirebridge',
            'uiRole' => 'candidate',
            'showMockBanner' => false,
            'jobs' => $jobs,
            'savedJobs' => $savedJobs,
            'accessContext' => $context,
            'defaultSavedIds' => $defaultSavedIds,
            'savedSearch' => MockCandidateData::savedSearchAlert(),
            'frontendOnly' => true,
        ]);
    }
}

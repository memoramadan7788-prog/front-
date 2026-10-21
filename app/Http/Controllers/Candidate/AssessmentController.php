<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Support\Frontend\MockCandidateData;
use Illuminate\Contracts\View\View;

/**
 * Candidate assessments / exams — workflow & ERD stubs (no dedicated Figma frame).
 * Frontend presentation only. No grading, proctoring, or persistence.
 */
class AssessmentController extends Controller
{
    public function index(): View
    {
        return view('candidate.assessments.index', [
            'pageTitle' => 'Assessments · Hirebridge',
            'uiRole' => 'candidate',
            'showMockBanner' => true,
            'assessments' => MockCandidateData::assessments(),
            'frontendOnly' => true,
        ]);
    }

    public function show(string $assessment): View
    {
        $record = MockCandidateData::assessment($assessment);

        abort_unless($record !== null, 404);

        return view('candidate.assessments.intro', [
            'pageTitle' => ($record['title'] ?? 'Assessment').' · Hirebridge',
            'uiRole' => 'candidate',
            'showMockBanner' => true,
            'assessment' => $record,
            'frontendOnly' => true,
        ]);
    }

    public function take(string $assessment): View
    {
        $record = MockCandidateData::assessment($assessment);

        abort_unless($record !== null && ($record['status_key'] ?? '') === 'ready', 404);

        return view('candidate.assessments.take', [
            'pageTitle' => 'Taking '.$record['title'].' · Hirebridge',
            'uiRole' => 'candidate',
            'showMockBanner' => true,
            'assessment' => $record,
            'frontendOnly' => true,
        ]);
    }

    public function result(string $assessment): View
    {
        $record = MockCandidateData::assessment($assessment);

        abort_unless($record !== null, 404);

        return view('candidate.assessments.result', [
            'pageTitle' => 'Results preview · Hirebridge',
            'uiRole' => 'candidate',
            'showMockBanner' => true,
            'assessment' => $record,
            'frontendOnly' => true,
        ]);
    }
}

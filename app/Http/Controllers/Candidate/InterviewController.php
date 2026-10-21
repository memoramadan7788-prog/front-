<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Support\Frontend\MockCandidateData;
use Illuminate\Contracts\View\View;

/**
 * Figma 20 — Candidate interviews.
 * Frontend presentation only. No booking or calendar sync.
 */
class InterviewController extends Controller
{
    public function index(): View
    {
        $interviews = MockCandidateData::interviews();

        return view('candidate.interviews.index', [
            'pageTitle' => 'Your interviews · Hirebridge',
            'uiRole' => 'candidate',
            'showMockBanner' => false,
            'interviews' => $interviews,
            'selected' => MockCandidateData::interview($interviews['selected_id']) ?? $interviews['upcoming'][0] ?? null,
            'frontendOnly' => true,
        ]);
    }

    public function show(string $interview): View
    {
        $record = MockCandidateData::interview($interview);

        abort_unless($record !== null, 404);

        $catalog = MockCandidateData::interviews();

        return view('candidate.interviews.show', [
            'pageTitle' => ($record['title'] ?? 'Interview').' · Hirebridge',
            'uiRole' => 'candidate',
            'showMockBanner' => false,
            'interview' => $record,
            'interviews' => $catalog,
            'frontendOnly' => true,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Support\Frontend\MockCandidateData;
use Illuminate\Contracts\View\View;

/**
 * Figma 23 — Candidate preferences & alerts.
 * Frontend presentation only. No account persistence.
 */
class PreferenceController extends Controller
{
    public function edit(): View
    {
        return view('candidate.preferences', [
            'pageTitle' => 'Job preferences & alerts · Hirebridge',
            'uiRole' => 'candidate',
            'showMockBanner' => true,
            'preferences' => MockCandidateData::preferences(),
            'frontendOnly' => true,
        ]);
    }
}

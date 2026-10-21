<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Support\Frontend\MockCandidateData;
use Illuminate\Contracts\View\View;

/**
 * Figma 13 — Good morning, Maya (candidate dashboard).
 * Frontend presentation only.
 */
class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('candidate.dashboard', [
            'pageTitle' => 'Overview · Hirebridge',
            'uiRole' => 'candidate',
            'showMockBanner' => false,
            'dashboard' => MockCandidateData::dashboard(),
            'frontendOnly' => true,
        ]);
    }
}

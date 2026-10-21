<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Support\Frontend\MockCandidateData;
use Illuminate\Contracts\View\View;

/**
 * Figma 22 — Profile & resume.
 * Frontend presentation only.
 */
class ProfileController extends Controller
{
    public function index(): View
    {
        return view('candidate.profile.index', [
            'pageTitle' => 'Profile & resume · Hirebridge',
            'uiRole' => 'candidate',
            'showMockBanner' => true,
            'profile' => MockCandidateData::profile(),
            'frontendOnly' => true,
        ]);
    }
}

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
            'showMockBanner' => false,
            'profile' => MockCandidateData::profile(),
            'membership' => MockCandidateData::membership(),
            'frontendOnly' => true,
        ]);
    }

    /**
     * Optional company membership request (Workflow 2 / F9A) — presentation only.
     */
    public function membership(): View
    {
        return view('candidate.membership.index', [
            'pageTitle' => __('candidate.membership.page_title'),
            'uiRole' => 'candidate',
            'showMockBanner' => false,
            'membership' => MockCandidateData::membership(),
            'frontendOnly' => true,
        ]);
    }
}

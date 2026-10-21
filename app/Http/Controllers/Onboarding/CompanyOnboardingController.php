<?php

namespace App\Http\Controllers\Onboarding;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

/**
 * Figma 12 — Set up your hiring workspace (presentation only).
 * Uses layouts.app company shell to match the Figma frame.
 */
class CompanyOnboardingController extends Controller
{
    public function create(): View
    {
        return view('onboarding.company-register', [
            'pageTitle' => 'Set up your hiring workspace · Hirebridge',
            'uiRole' => 'company',
            'showMockBanner' => true,
            'frontendOnly' => true,
        ]);
    }

    /**
     * Supporting review-state preview (not the primary Frame 12 form).
     */
    public function status(): View
    {
        return view('onboarding.company-status', [
            'pageTitle' => 'Company review status · Hirebridge',
            'companyName' => 'Northstar Labs',
            'uiRole' => 'company',
            'showMockBanner' => true,
            'frontendOnly' => true,
        ]);
    }
}

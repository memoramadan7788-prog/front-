<?php

namespace App\Http\Controllers\Onboarding;

use App\Http\Controllers\Controller;
use App\Support\Frontend\MockCompanyData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

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
            'showMockBanner' => false,
            'frontendOnly' => true,
        ]);
    }

    /**
     * Supporting review-state preview (not the primary Frame 12 form).
     */
    public function status(Request $request): View
    {
        $status = $request->query('status');
        $status = is_string($status) ? $status : 'pending';
        $context = MockCompanyData::organizationContext($status);

        return view('onboarding.company-status', [
            'pageTitle' => 'Company review status · Hirebridge',
            'companyName' => $context['organization']['name'],
            'organizationContext' => $context,
            'reviewStatus' => $context['organization']['status'],
            'uiRole' => 'company',
            'showMockBanner' => false,
            'frontendOnly' => true,
        ]);
    }
}

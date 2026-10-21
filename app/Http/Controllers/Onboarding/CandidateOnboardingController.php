<?php

namespace App\Http\Controllers\Onboarding;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

/**
 * Figma 11 — Build a profile that feels like you (presentation only).
 * Uses layouts.app candidate shell to match the Figma frame.
 */
class CandidateOnboardingController extends Controller
{
    public function create(): View
    {
        return view('onboarding.candidate-profile', [
            'pageTitle' => 'Build a profile that feels like you · Hirebridge',
            'uiRole' => 'candidate',
            'showMockBanner' => false,
            'frontendOnly' => true,
        ]);
    }
}

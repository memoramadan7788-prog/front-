<?php

namespace App\Http\Controllers\Interviewer;

use App\Http\Controllers\Controller;
use App\Support\Frontend\MockInterviewerData;
use Illuminate\Contracts\View\View;

/**
 * Figma Frame 39 — Interviewer dashboard overview.
 * Frontend presentation only.
 */
class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('interviewer.dashboard', [
            'pageTitle' => __('interviewer.dashboard.page_title'),
            'uiRole' => 'interviewer',
            'showMockBanner' => false,
            'dashboard' => MockInterviewerData::dashboard(),
            'frontendOnly' => true,
        ]);
    }
}

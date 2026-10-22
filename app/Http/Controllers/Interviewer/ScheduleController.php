<?php

namespace App\Http\Controllers\Interviewer;

use App\Http\Controllers\Controller;
use App\Support\Frontend\MockInterviewerData;
use Illuminate\Contracts\View\View;

/**
 * Figma Frame 40 — Interviewer schedule.
 * Frontend presentation only.
 */
class ScheduleController extends Controller
{
    public function index(): View
    {
        return view('interviewer.slots.index', [
            'pageTitle' => __('interviewer.schedule.page_title'),
            'uiRole' => 'interviewer',
            'showMockBanner' => false,
            'schedule' => MockInterviewerData::schedule(),
            'frontendOnly' => true,
        ]);
    }
}

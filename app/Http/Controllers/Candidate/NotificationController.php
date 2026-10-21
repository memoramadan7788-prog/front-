<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Support\Frontend\MockCandidateData;
use Illuminate\Contracts\View\View;

/**
 * Candidate notifications list — Stage 2 dropdown extended (Batch 4E).
 * Frontend presentation only. No delivery or persistence.
 */
class NotificationController extends Controller
{
    public function index(): View
    {
        return view('candidate.notifications.index', [
            'pageTitle' => 'Notifications · Hirebridge',
            'uiRole' => 'candidate',
            'showMockBanner' => false,
            'notifications' => MockCandidateData::notifications(),
            'frontendOnly' => true,
        ]);
    }
}

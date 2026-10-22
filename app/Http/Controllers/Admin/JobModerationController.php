<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Frontend\MockAdminData;
use Illuminate\Contracts\View\View;

/**
 * Figma Frame 48 — Job moderation queue.
 * Frontend presentation only. No approve/reject/publish writes.
 */
class JobModerationController extends Controller
{
    public function index(): View
    {
        return view('admin.jobs.index', [
            'pageTitle' => __('admin.jobs.page_title'),
            'uiRole' => 'admin',
            'showMockBanner' => false,
            'jobsPage' => MockAdminData::jobsModerationIndex(),
            'frontendOnly' => true,
        ]);
    }
}

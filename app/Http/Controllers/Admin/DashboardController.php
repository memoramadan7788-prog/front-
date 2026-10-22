<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Frontend\MockAdminData;
use Illuminate\Contracts\View\View;

/**
 * Figma Frame 44 — Platform operations overview.
 * Frontend presentation only.
 */
class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'pageTitle' => __('admin.dashboard.page_title'),
            'uiRole' => 'admin',
            'showMockBanner' => false,
            'dashboard' => MockAdminData::dashboard(),
            'frontendOnly' => true,
        ]);
    }
}

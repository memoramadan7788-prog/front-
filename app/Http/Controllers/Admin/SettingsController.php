<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Frontend\MockAdminData;
use Illuminate\Contracts\View\View;

/**
 * Figma Frame 50 — Platform settings & audit.
 * Frontend presentation only. No security, permission, or integration writes.
 */
class SettingsController extends Controller
{
    public function index(): View
    {
        return view('admin.settings.index', [
            'pageTitle' => __('admin.settings.page_title'),
            'uiRole' => 'admin',
            'showMockBanner' => false,
            'settingsPage' => MockAdminData::settingsIndex(),
            'frontendOnly' => true,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Frontend\MockAdminData;
use Illuminate\Contracts\View\View;

/**
 * Figma Frame 49 — Agency operations.
 * Frontend presentation only. No engagement write APIs.
 */
class AgencyController extends Controller
{
    public function index(): View
    {
        return view('admin.agencies.index', [
            'pageTitle' => __('admin.agencies.page_title'),
            'uiRole' => 'admin',
            'showMockBanner' => false,
            'agenciesPage' => MockAdminData::agenciesIndex(),
            'frontendOnly' => true,
        ]);
    }
}

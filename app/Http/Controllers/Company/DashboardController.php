<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Support\Frontend\MockCompanyData;
use Illuminate\Contracts\View\View;

/**
 * Figma Frame 25 — Company / HR workspace overview.
 * Frontend presentation only.
 */
class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('company.dashboard', [
            'pageTitle' => 'Overview · Hirebridge',
            'uiRole' => 'company',
            'showMockBanner' => true,
            'dashboard' => MockCompanyData::dashboard(),
            'frontendOnly' => true,
        ]);
    }
}

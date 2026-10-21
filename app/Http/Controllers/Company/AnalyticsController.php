<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Support\Frontend\MockCompanyData;
use Illuminate\Contracts\View\View;

/**
 * Company hiring analytics UI (Batch 5G) — Frame 38.
 * Frontend presentation only. Metrics are a static mock snapshot.
 */
class AnalyticsController extends Controller
{
    public function index(): View
    {
        return view('company.analytics.index', [
            'pageTitle' => __('company.analytics.page_title'),
            'uiRole' => 'company',
            'showMockBanner' => false,
            'analytics' => MockCompanyData::analytics(),
            'frontendOnly' => true,
        ]);
    }
}

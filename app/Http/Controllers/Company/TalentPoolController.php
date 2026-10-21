<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Support\Frontend\MockCompanyData;
use Illuminate\Contracts\View\View;

/**
 * Company talent pool UI (Batch 5E) — Frame 36.
 * Frontend presentation only.
 */
class TalentPoolController extends Controller
{
    public function index(): View
    {
        return view('company.talent-pool.index', [
            'pageTitle' => __('company.talent_pool.page_title'),
            'uiRole' => 'company',
            'showMockBanner' => false,
            'pool' => MockCompanyData::talentPool(),
            'frontendOnly' => true,
        ]);
    }
}

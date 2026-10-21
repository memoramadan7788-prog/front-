<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Support\Frontend\MockCompanyData;
use Illuminate\Contracts\View\View;

/**
 * Company profile UI — workflow / onboarding-derived (Batch 5A).
 * No dedicated Figma profile frame verified. Frontend presentation only.
 */
class ProfileController extends Controller
{
    public function show(): View
    {
        return view('company.profile.show', [
            'pageTitle' => 'Company profile · Hirebridge',
            'uiRole' => 'company',
            'showMockBanner' => false,
            'profile' => MockCompanyData::profile(),
            'frontendOnly' => true,
        ]);
    }
}

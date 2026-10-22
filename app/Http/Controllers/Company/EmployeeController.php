<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Support\Frontend\MockCompanyData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

/**
 * Company team, employees, and join-request UI (Batch 5F) — Frame 37 + workflow-derived requests.
 * Frontend presentation only.
 */
class EmployeeController extends Controller
{
    public function index(): View
    {
        return view('company.employees.index', [
            'pageTitle' => __('company.employees.page_title'),
            'uiRole' => 'company',
            'showMockBanner' => false,
            'team' => MockCompanyData::team(),
            'organizationContext' => MockCompanyData::organizationContext(),
            'frontendOnly' => true,
        ]);
    }

    public function requests(): View
    {
        return view('company.employees.requests', [
            'pageTitle' => __('company.employees.requests_page_title'),
            'uiRole' => 'company',
            'showMockBanner' => false,
            'requestsPage' => MockCompanyData::joinRequests(),
            'organizationContext' => MockCompanyData::organizationContext(),
            'frontendOnly' => true,
        ]);
    }

    public function show(string $employee): View|Response
    {
        $member = MockCompanyData::employee($employee);

        if ($member === null) {
            abort(404);
        }

        return view('company.employees.show', [
            'pageTitle' => __('company.employees.show_page_title', ['name' => $member['name']]),
            'uiRole' => 'company',
            'showMockBanner' => false,
            'member' => $member,
            'team' => MockCompanyData::team(),
            'frontendOnly' => true,
        ]);
    }
}

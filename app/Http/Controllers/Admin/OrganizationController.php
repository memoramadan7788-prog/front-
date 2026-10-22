<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Frontend\MockAdminData;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Figma Frames 45–46 — Organizations directory and organization details.
 * Frontend presentation only.
 */
class OrganizationController extends Controller
{
    public function index(): View
    {
        return view('admin.organizations.index', [
            'pageTitle' => __('admin.organizations.page_title'),
            'uiRole' => 'admin',
            'showMockBanner' => false,
            'orgsPage' => MockAdminData::organizationsIndex(),
            'frontendOnly' => true,
        ]);
    }

    public function show(string $organization): View
    {
        $org = MockAdminData::organizationShow($organization);
        if ($org === null) {
            throw new NotFoundHttpException;
        }

        return view('admin.organizations.show', [
            'pageTitle' => __('admin.organizations.show_page_title', ['name' => $org['name']]),
            'uiRole' => 'admin',
            'showMockBanner' => false,
            'organization' => $org,
            'frontendOnly' => true,
        ]);
    }
}

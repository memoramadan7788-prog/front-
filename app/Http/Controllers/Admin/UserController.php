<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Frontend\MockAdminData;
use Illuminate\Contracts\View\View;

/**
 * Figma Frame 47 — Users & access directory.
 * Frontend presentation only. No impersonation or write APIs.
 */
class UserController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index', [
            'pageTitle' => __('admin.users.page_title'),
            'uiRole' => 'admin',
            'showMockBanner' => false,
            'usersPage' => MockAdminData::usersIndex(),
            'frontendOnly' => true,
        ]);
    }
}

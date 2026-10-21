<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Lightweight Stage 1 layout previews.
 * Not product screens — used to verify shells, tokens, and asset loading.
 */
class LayoutPreviewController extends Controller
{
    /**
     * @var list<string>
     */
    private const UI_ROLES = ['candidate', 'company', 'interviewer', 'admin'];

    public function auth(): View
    {
        return view('frontend.layouts.auth-preview');
    }

    public function app(Request $request): View
    {
        $uiRole = $request->query('role', 'candidate');

        if (! in_array($uiRole, self::UI_ROLES, true)) {
            $uiRole = 'candidate';
        }

        return view('frontend.layouts.app-preview', [
            'uiRole' => $uiRole,
            'showMockBanner' => true,
        ]);
    }
}

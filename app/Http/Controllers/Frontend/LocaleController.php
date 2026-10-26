<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Middleware\SetFrontendLocale;
use App\Support\Frontend\SafeInternalRedirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

/**
 * Sets the frontend presentation locale cookie and returns the user to a safe in-app URL.
 * Does not alter authentication, roles, permissions, or theme preference.
 */
class LocaleController extends Controller
{
    public function __invoke(Request $request, string $locale): RedirectResponse
    {
        if (! in_array($locale, SetFrontendLocale::SUPPORTED, true)) {
            $locale = config('app.fallback_locale', 'en');
        }

        $redirectTo = SafeInternalRedirect::fromRequest($request);

        return redirect()
            ->to($redirectTo)
            ->withCookie(
                Cookie::make(
                    SetFrontendLocale::COOKIE,
                    $locale,
                    60 * 24 * 365,
                    '/',
                    null,
                    $request->isSecure(),
                    false,
                    false,
                    'lax',
                )
            );
    }
}

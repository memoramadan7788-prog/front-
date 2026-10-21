<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Presentation-only locale preference from cookie (frontend scope).
 * Does not touch auth profiles or database user locale.
 */
class SetFrontendLocale
{
    public const COOKIE = 'hb_locale';

    /** @var list<string> */
    public const SUPPORTED = ['en', 'ar'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->cookie(self::COOKIE, config('app.locale', 'en'));

        if (! in_array($locale, self::SUPPORTED, true)) {
            $locale = config('app.fallback_locale', 'en');
        }

        App::setLocale($locale);

        /** @var Response $response */
        $response = $next($request);

        return $response;
    }
}

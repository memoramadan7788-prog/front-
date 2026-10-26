<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts a route group to one account type (candidate | company).
 * Usage: ->middleware(['auth', 'account:candidate'])
 */
class EnsureAccountType
{
    public function handle(Request $request, Closure $next, string $type): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('frontend.account.login');
        }

        if (($user->account_type ?? null) !== $type) {
            // wrong workspace: send the user to where they belong (never loops: workspace aborts 403 for unknown types)
            return redirect()->route('frontend.workspace');
        }

        return $next($request);
    }
}

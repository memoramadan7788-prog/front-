<?php

use App\Http\Middleware\EnsureAccountType;
use App\Http\Middleware\EnsureAdminAccess;
use App\Http\Middleware\SetFrontendLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: [
            SetFrontendLocale::COOKIE,
        ]);

        $middleware->web(append: [
            SetFrontendLocale::class,
        ]);

        $middleware->redirectGuestsTo(fn () => route('frontend.account.login'));
        $middleware->redirectUsersTo(fn () => route('frontend.workspace'));

        $middleware->alias([
            'account' => EnsureAccountType::class,
            'admin.session' => EnsureAdminAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
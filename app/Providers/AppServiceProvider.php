<?php

namespace App\Providers;

use App\Frontend\Auth\ApiSession;
use App\Support\Frontend\CompanyMembership\CompanyMembershipGateway;
use App\Support\Frontend\CompanyMembership\HttpCompanyMembershipGateway;
use App\Support\Frontend\Notifications\HttpNotificationsGateway;
use App\Support\Frontend\Notifications\NotificationsGateway;
use Illuminate\Auth\GenericUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CompanyMembershipGateway::class, HttpCompanyMembershipGateway::class);
        $this->app->bind(NotificationsGateway::class, HttpNotificationsGateway::class);
    }

    public function boot(): void
    {
        // "Who is logged in?" comes from the token + user stored in the session by the API login,
        // NOT from the database. This keeps auth()->user(), @auth, @guest and the `auth` middleware working.
        Auth::viaRequest('api-session', function (Request $request) {
            if (! ApiSession::check()) {
                return null;
            }

            return new GenericUser(ApiSession::user());
        });
    }
}

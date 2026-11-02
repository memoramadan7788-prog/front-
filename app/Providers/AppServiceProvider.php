<?php

namespace App\Providers;

use App\Frontend\Auth\ApiSession;
use App\Support\Frontend\CandidateDashboard\CandidateDashboardKpiGateway;
use App\Support\Frontend\CandidateDashboard\HttpCandidateDashboardKpiGateway;
use App\Support\Frontend\CandidateSettings\CandidateSettingsGateway;
use App\Support\Frontend\CandidateSettings\HttpCandidateSettingsGateway;
use App\Support\Frontend\CompanyMembership\CompanyMembershipGateway;
use App\Support\Frontend\CompanyMembership\HttpCompanyMembershipGateway;
use App\Support\Frontend\Notifications\HttpNotificationsGateway;
use App\Support\Frontend\Notifications\NotificationsGateway;
use Illuminate\Auth\GenericUser;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            CompanyMembershipGateway::class,
            HttpCompanyMembershipGateway::class
        );

        $this->app->bind(
            NotificationsGateway::class,
            HttpNotificationsGateway::class
        );

        $this->app->bind(
            CandidateDashboardKpiGateway::class,
            HttpCandidateDashboardKpiGateway::class
        );

        $this->app->bind(
            CandidateSettingsGateway::class,
            HttpCandidateSettingsGateway::class
        );
    }

    public function boot(): void
    {
        ResetPassword::createUrlUsing(function ($notifiable, string $token) {
            $frontendUrl = rtrim(
                config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:8001')),
                '/'
            );

            return $frontendUrl . '/account/reset-password/' . $token
                . '?email=' . urlencode($notifiable->getEmailForPasswordReset());
        });

        Auth::viaRequest('api-session', function (Request $request) {
            if (!ApiSession::check()) {
                return null;
            }

            return new GenericUser(ApiSession::user());
        });
    }
}
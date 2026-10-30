<?php

namespace Tests\Feature;

use App\Frontend\Auth\ApiSession;
use App\Http\Middleware\SetFrontendLocale;
use App\Models\AccountAPI;
use App\Models\AdminAPI;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Mockery;
use Tests\TestCase;

class WelcomeBackLoginTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(PreventRequestForgery::class);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_login_page_get_succeeds_with_branding_and_workspace_messaging(): void
    {
        $html = $this->get(route('frontend.account.login'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(__('auth.login.title'), $html);
        $this->assertStringContainsString(__('auth.brand_headline'), $html);
        $this->assertStringContainsString(__('auth.login.continue_workspace'), $html);
        $this->assertStringContainsString('data-login-form', $html);
        $this->assertStringContainsString('data-toggle-password="password"', $html);
        $this->assertStringContainsString(route('frontend.account.register', ['type' => 'candidate'], false), $html);
        $this->assertStringContainsString(route('frontend.account.register', ['type' => 'company'], false), $html);
        $this->assertStringContainsString(route('frontend.account.admin-login', [], false), $html);
        // No Forgot Password route exists — do not invent a dead link.
        $this->assertStringNotContainsString(__('auth.login.forgot'), $html);
        $this->assertStringNotContainsString('SQLSTATE', $html);
    }

    public function test_unsupported_google_sso_and_remember_are_disabled_and_honest(): void
    {
        $html = $this->get(route('frontend.account.login'))->assertOk()->getContent();

        $this->assertStringContainsString('data-login-sso-unavailable', $html);
        $this->assertStringContainsString(__('auth.login.sso_google'), $html);
        $this->assertStringContainsString(__('auth.login.sso_company'), $html);
        $this->assertStringContainsString(__('auth.login.sso_unavailable'), $html);
        $this->assertStringContainsString('data-login-remember-unavailable', $html);
        $this->assertStringContainsString(__('auth.login.remember_unavailable'), $html);
        $this->assertStringNotContainsString('name="remember"', $html);
        $this->assertStringNotContainsString('accounts.google.com', $html);
        $this->assertStringNotContainsString('/oauth', $html);
        $this->assertStringNotContainsString('/sso', $html);
    }

    public function test_candidate_login_success_routes_to_candidate_dashboard(): void
    {
        $this->bindUserLoginMock([
            'id' => 11,
            'name' => 'Candidate',
            'email' => 'cand@example.com',
            'account_type' => 'candidate',
        ], 'cand-token');

        $this->post(route('frontend.account.login.submit'), [
            'email' => 'cand@example.com',
            'password' => 'SecretPass1',
        ])->assertRedirect(route('frontend.candidate.dashboard'));

        $this->assertTrue(ApiSession::check());
        $this->assertSame('candidate', ApiSession::accountType());
        $this->assertSame(ApiSession::REALM_USER, ApiSession::realm());
        $this->assertStringNotContainsString('SecretPass1', json_encode(session()->all()));
        $this->assertStringNotContainsString('cand-token', (string) request()->fullUrl());
    }

    public function test_company_login_success_routes_to_company_dashboard(): void
    {
        $this->bindUserLoginMock([
            'id' => 22,
            'name' => 'HR Lead',
            'email' => 'hr@example.com',
            'account_type' => 'company',
            'company' => ['id' => 5, 'name' => 'Acme', 'status' => 'approved'],
        ], 'co-token');

        $this->post(route('frontend.account.login.submit'), [
            'email' => 'hr@example.com',
            'password' => 'SecretPass1',
        ])->assertRedirect(route('frontend.company.dashboard'));

        $this->assertSame('company', ApiSession::accountType());
        $this->assertFalse(ApiSession::isPlatformAdmin());
    }

    public function test_invalid_credentials_are_flashed_without_creating_session(): void
    {
        $accounts = Mockery::mock(AccountAPI::class);
        $accounts->shouldReceive('login')->once()->andReturn([
            'message' => 'The given data was invalid.',
            'data' => null,
            'errors' => ['email' => ['The provided credentials do not match our records.']],
            'statusCode' => 422,
        ]);
        $this->app->instance(AccountAPI::class, $accounts);

        $this->from(route('frontend.account.login'))
            ->post(route('frontend.account.login.submit'), [
                'email' => 'nobody@example.com',
                'password' => 'wrong-password',
            ])
            ->assertRedirect(route('frontend.account.login'))
            ->assertSessionHasErrors('email');

        $this->assertFalse(ApiSession::check());
        $this->assertStringContainsString('credentials', (string) session('errors')->first('email'));
    }

    public function test_form_validation_errors_require_email_and_password(): void
    {
        $accounts = Mockery::mock(AccountAPI::class);
        $accounts->shouldNotReceive('login');
        $this->app->instance(AccountAPI::class, $accounts);

        $this->from(route('frontend.account.login'))
            ->post(route('frontend.account.login.submit'), [])
            ->assertRedirect(route('frontend.account.login'))
            ->assertSessionHasErrors(['email', 'password']);
    }

    public function test_login_form_exposes_duplicate_submit_guards(): void
    {
        $html = $this->get(route('frontend.account.login'))->assertOk()->getContent();

        $this->assertStringContainsString('data-login-form', $html);
        $this->assertStringContainsString('data-login-submit', $html);
        $this->assertStringContainsString('data-login-idle', $html);
        $this->assertStringContainsString('data-login-loading', $html);
    }

    public function test_safe_return_url_is_honored_after_login(): void
    {
        $this->bindUserLoginMock([
            'id' => 11,
            'name' => 'Candidate',
            'email' => 'cand@example.com',
            'account_type' => 'candidate',
        ], 'cand-token');

        $this->post(route('frontend.account.login.submit'), [
            'email' => 'cand@example.com',
            'password' => 'SecretPass1',
            'redirect' => '/jobs?q=php',
        ])->assertRedirect('/jobs?q=php');
    }

    public function test_external_and_auth_return_urls_are_rejected(): void
    {
        $this->bindUserLoginMock([
            'id' => 11,
            'name' => 'Candidate',
            'email' => 'cand@example.com',
            'account_type' => 'candidate',
        ], 'cand-token', times: 3);

        $this->post(route('frontend.account.login.submit'), [
            'email' => 'cand@example.com',
            'password' => 'SecretPass1',
            'redirect' => 'https://evil.example/phish',
        ])->assertRedirect(route('frontend.candidate.dashboard'));

        ApiSession::forget();

        $this->post(route('frontend.account.login.submit'), [
            'email' => 'cand@example.com',
            'password' => 'SecretPass1',
            'redirect' => '//evil.example/phish',
        ])->assertRedirect(route('frontend.candidate.dashboard'));

        ApiSession::forget();

        $this->post(route('frontend.account.login.submit'), [
            'email' => 'cand@example.com',
            'password' => 'SecretPass1',
            'redirect' => '/account/login',
        ])->assertRedirect(route('frontend.candidate.dashboard'));
    }

    public function test_login_get_ignores_unsafe_redirect_query(): void
    {
        $html = $this->get(route('frontend.account.login', [
            'redirect' => 'https://evil.example/phish',
        ]))->assertOk()->getContent();

        $this->assertStringNotContainsString('https://evil.example/phish', $html);
        $this->assertStringNotContainsString('name="redirect"', $html);
        $this->assertStringContainsString('data-login-continuation-default', $html);
    }

    public function test_login_get_shows_safe_continuation_target(): void
    {
        $html = $this->get(route('frontend.account.login', [
            'redirect' => '/jobs/42',
        ]))->assertOk()->getContent();

        $this->assertStringContainsString('data-login-continuation', $html);
        $this->assertStringContainsString('name="redirect"', $html);
        $this->assertStringContainsString('/jobs/42', $html);
    }

    public function test_standard_login_never_calls_platform_admin_api(): void
    {
        $admins = Mockery::mock(AdminAPI::class);
        $admins->shouldNotReceive('login');
        $admins->shouldNotReceive('me');
        $this->app->instance(AdminAPI::class, $admins);

        $this->bindUserLoginMock([
            'id' => 11,
            'name' => 'Candidate',
            'email' => 'cand@example.com',
            'account_type' => 'candidate',
        ], 'cand-token');

        $this->post(route('frontend.account.login.submit'), [
            'email' => 'cand@example.com',
            'password' => 'SecretPass1',
        ])->assertRedirect(route('frontend.candidate.dashboard'));

        $this->assertFalse(ApiSession::isPlatformAdmin());
    }

    public function test_en_ar_and_rtl_login_rendering(): void
    {
        $en = $this->withUnencryptedCookie(SetFrontendLocale::COOKIE, 'en')
            ->get(route('frontend.account.login'))
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString(__('auth.login.title', [], 'en'), $en);
        $this->assertMatchesRegularExpression('/<html[^>]*\sdir="ltr"/', $en);

        $ar = $this->withUnencryptedCookie(SetFrontendLocale::COOKIE, 'ar')
            ->get(route('frontend.account.login'))
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString(__('auth.login.title', [], 'ar'), $ar);
        $this->assertStringContainsString(__('auth.login.sso_unavailable', [], 'ar'), $ar);
        $this->assertMatchesRegularExpression('/<html[^>]*\sdir="rtl"/', $ar);
    }

    public function test_login_sql_debug_is_sanitized(): void
    {
        $leak = "SQLSTATE[HY000]: General error: 2002 Connection refused (Connection: mysql, Host: 127.0.0.1)";

        $accounts = Mockery::mock(AccountAPI::class);
        $accounts->shouldReceive('login')->once()->andReturn([
            'message' => $leak,
            'data' => null,
            'errors' => ['error' => $leak],
            'statusCode' => 500,
        ]);
        $this->app->instance(AccountAPI::class, $accounts);

        $this->from(route('frontend.account.login'))
            ->post(route('frontend.account.login.submit'), [
                'email' => 'cand@example.com',
                'password' => 'SecretPass1',
            ])
            ->assertRedirect(route('frontend.account.login'))
            ->assertSessionHasErrors('error');

        $message = (string) session('errors')->first('error');
        $this->assertSame(__('auth.login.unavailable'), $message);
        $this->assertStringNotContainsString('SQLSTATE', $message);
        $this->assertFalse(ApiSession::check());
    }

    public function test_logout_clears_user_realm_and_returns_to_login(): void
    {
        ApiSession::store('cand-token', [
            'id' => 11,
            'email' => 'cand@example.com',
            'name' => 'Candidate',
            'account_type' => 'candidate',
        ], ApiSession::REALM_USER);

        $accounts = Mockery::mock(AccountAPI::class);
        $accounts->shouldReceive('logout')->once()->andReturn([
            'message' => 'Logged out successfully.',
            'data' => null,
            'errors' => null,
            'statusCode' => 200,
        ]);
        $this->app->instance(AccountAPI::class, $accounts);

        $admins = Mockery::mock(AdminAPI::class);
        $admins->shouldNotReceive('logout');
        $this->app->instance(AdminAPI::class, $admins);

        $this->post(route('frontend.account.logout'))
            ->assertRedirect(route('frontend.account.login'));

        $this->assertFalse(ApiSession::check());
        $this->assertNull(ApiSession::token());
    }

    /**
     * @param  array<string, mixed>  $user
     */
    private function bindUserLoginMock(array $user, string $token, int $times = 1): void
    {
        $accounts = Mockery::mock(AccountAPI::class);
        $accounts->shouldReceive('login')
            ->times($times)
            ->with(Mockery::on(function (array $payload): bool {
                return array_keys($payload) === ['email', 'password']
                    && is_string($payload['email'] ?? null)
                    && is_string($payload['password'] ?? null)
                    && ! array_key_exists('remember', $payload);
            }))
            ->andReturn([
                'message' => 'Login successful.',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                    'access_token' => $token,
                ],
                'errors' => [],
                'statusCode' => 200,
            ]);
        $this->app->instance(AccountAPI::class, $accounts);
    }
}

<?php

namespace Tests\Feature;

use App\Frontend\Auth\ApiSession;
use App\Models\AccountAPI;
use App\Models\AdminAPI;
use Mockery;
use Tests\TestCase;

class PlatformAdminAuthTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_platform_admin_login_uses_admin_login_and_sets_platform_realm(): void
    {
        $admins = Mockery::mock(AdminAPI::class);
        $admins->shouldReceive('login')->once()->with(Mockery::on(function (array $payload): bool {
            return ($payload['email'] ?? null) === 'super@example.com'
                && ($payload['password'] ?? null) === 'SecretPass1'
                && array_keys($payload) === ['email', 'password'];
        }))->andReturn([
            'message' => 'Login successful.',
            'data' => [
                'admin' => [
                    'id' => 7,
                    'name' => 'Super Admin',
                    'email' => 'super@example.com',
                ],
                'token' => 'platform-token',
                'access_token' => 'platform-token',
                'token_type' => 'Bearer',
            ],
            'errors' => null,
            'statusCode' => 200,
        ]);
        $admins->shouldReceive('me')->once()->andReturn([
            'message' => 'OK',
            'data' => [
                'id' => 7,
                'name' => 'Super Admin',
                'email' => 'super@example.com',
                'roles' => ['super_admin'],
                'permissions' => ['reports.view'],
            ],
            'errors' => null,
            'statusCode' => 200,
        ]);
        $this->app->instance(AdminAPI::class, $admins);

        $accounts = Mockery::mock(AccountAPI::class);
        $accounts->shouldNotReceive('login');
        $this->app->instance(AccountAPI::class, $accounts);

        $this->from(route('frontend.account.admin-login'))
            ->post(route('frontend.account.admin-login.submit'), [
                'email' => 'super@example.com',
                'password' => 'SecretPass1',
            ])
            ->assertRedirect(route('frontend.dashboard'));

        $this->assertTrue(ApiSession::check());
        $this->assertTrue(ApiSession::isPlatformAdmin());
        $this->assertSame('platform-token', ApiSession::token());
        $this->assertSame('admin', ApiSession::accountType());
        $this->assertSame('super@example.com', ApiSession::user()['email'] ?? null);
        $this->assertSame(['super_admin'], ApiSession::user()['roles'] ?? null);
        $this->assertStringNotContainsString('SecretPass1', json_encode(session()->all()));
    }

    public function test_platform_admin_login_rejects_invalid_credentials(): void
    {
        $admins = Mockery::mock(AdminAPI::class);
        $admins->shouldReceive('login')->once()->andReturn([
            'message' => 'Invalid credentials.',
            'data' => null,
            'errors' => null,
            'statusCode' => 401,
        ]);
        $admins->shouldNotReceive('me');
        $this->app->instance(AdminAPI::class, $admins);

        $this->from(route('frontend.account.admin-login'))
            ->post(route('frontend.account.admin-login.submit'), [
                'email' => 'super@example.com',
                'password' => 'wrong-password',
            ])
            ->assertRedirect(route('frontend.account.admin-login'))
            ->assertSessionHasErrors('error');

        $this->assertFalse(ApiSession::check());
    }

    public function test_standard_login_does_not_call_admin_login_api(): void
    {
        $admins = Mockery::mock(AdminAPI::class);
        $admins->shouldNotReceive('login');
        $admins->shouldNotReceive('me');
        $this->app->instance(AdminAPI::class, $admins);

        $accounts = Mockery::mock(AccountAPI::class);
        $accounts->shouldReceive('login')->once()->andReturn([
            'message' => 'Login successful.',
            'data' => [
                'user' => [
                    'id' => 11,
                    'name' => 'Candidate',
                    'email' => 'cand@example.com',
                    'account_type' => 'candidate',
                ],
                'token' => 'cand-token',
            ],
            'errors' => [],
            'statusCode' => 200,
        ]);
        $this->app->instance(AccountAPI::class, $accounts);

        $this->post(route('frontend.account.login.submit'), [
            'email' => 'cand@example.com',
            'password' => 'SecretPass1',
        ])->assertRedirect(route('frontend.candidate.dashboard'));

        $this->assertTrue(ApiSession::check());
        $this->assertFalse(ApiSession::isPlatformAdmin());
        $this->assertSame(ApiSession::REALM_USER, ApiSession::realm());
        $this->assertSame('candidate', ApiSession::accountType());
    }

    public function test_company_login_preserves_user_realm(): void
    {
        $admins = Mockery::mock(AdminAPI::class);
        $admins->shouldNotReceive('login');
        $this->app->instance(AdminAPI::class, $admins);

        $accounts = Mockery::mock(AccountAPI::class);
        $accounts->shouldReceive('login')->once()->andReturn([
            'message' => 'Login successful.',
            'data' => [
                'user' => [
                    'id' => 22,
                    'name' => 'HR',
                    'email' => 'hr@example.com',
                    'account_type' => 'company',
                    'company' => ['id' => 5, 'name' => 'Acme', 'status' => 'approved'],
                ],
                'token' => 'co-token',
            ],
            'errors' => [],
            'statusCode' => 200,
        ]);
        $this->app->instance(AccountAPI::class, $accounts);

        $this->post(route('frontend.account.login.submit'), [
            'email' => 'hr@example.com',
            'password' => 'SecretPass1',
        ])->assertRedirect(route('frontend.company.dashboard'));

        $this->assertSame(ApiSession::REALM_USER, ApiSession::realm());
        $this->assertSame('company', ApiSession::accountType());
    }

    public function test_candidate_session_cannot_use_admin_login_while_authenticated(): void
    {
        ApiSession::store('cand-token', [
            'id' => 1,
            'email' => 'c@example.com',
            'name' => 'C',
            'account_type' => 'candidate',
        ], ApiSession::REALM_USER);

        $this->get(route('frontend.account.admin-login'))
            ->assertRedirect(route('frontend.workspace'));
    }

    public function test_platform_admin_logout_calls_admin_logout_and_clears_realm(): void
    {
        ApiSession::store('platform-token', [
            'id' => 7,
            'email' => 'super@example.com',
            'name' => 'Super',
            'account_type' => 'admin',
        ], ApiSession::REALM_PLATFORM_ADMIN);

        $admins = Mockery::mock(AdminAPI::class);
        $admins->shouldReceive('logout')->once()->andReturn([
            'message' => 'Logout successful.',
            'data' => null,
            'errors' => null,
            'statusCode' => 200,
        ]);
        $this->app->instance(AdminAPI::class, $admins);

        $accounts = Mockery::mock(AccountAPI::class);
        $accounts->shouldNotReceive('logout');
        $this->app->instance(AccountAPI::class, $accounts);

        $this->post(route('frontend.account.logout'))
            ->assertRedirect(route('frontend.account.admin-login'));

        $this->assertFalse(ApiSession::check());
        $this->assertSame(ApiSession::REALM_USER, ApiSession::realm());
    }

    public function test_user_logout_does_not_call_admin_logout(): void
    {
        ApiSession::store('cand-token', [
            'id' => 11,
            'email' => 'cand@example.com',
            'name' => 'Cand',
            'account_type' => 'candidate',
        ], ApiSession::REALM_USER);

        $admins = Mockery::mock(AdminAPI::class);
        $admins->shouldNotReceive('logout');
        $this->app->instance(AdminAPI::class, $admins);

        $accounts = Mockery::mock(AccountAPI::class);
        $accounts->shouldReceive('logout')->once()->andReturn([
            'message' => 'Logged out successfully.',
            'data' => null,
            'errors' => [],
            'statusCode' => 200,
        ]);
        $this->app->instance(AccountAPI::class, $accounts);

        $this->post(route('frontend.account.logout'))
            ->assertRedirect(route('frontend.account.login'));

        $this->assertFalse(ApiSession::check());
    }

    public function test_admin_login_page_renders_without_fabricating_access(): void
    {
        $html = $this->get(route('frontend.account.admin-login'))->assertOk()->getContent();

        $this->assertStringContainsString(__('auth.admin_login.heading'), $html);
        $this->assertStringContainsString(route('frontend.account.admin-login.submit', [], false), $html);
        $this->assertStringContainsString(route('frontend.account.login', [], false), $html);
    }
}

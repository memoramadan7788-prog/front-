<?php

namespace Tests\Feature;

use App\Frontend\Auth\ApiSession;
use App\Http\Middleware\EnsureAdminAccess;
use App\Models\AdminAPI;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

class AdminAuthorizationTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_candidate_is_denied_admin_users_page(): void
    {
        ApiSession::store('candidate-token', [
            'id' => 11,
            'email' => 'candidate@example.com',
            'name' => 'Candidate',
            'account_type' => 'candidate',
        ], ApiSession::REALM_USER);

        $this->get(route('frontend.admin.users.index'))
            ->assertRedirect(route('frontend.workspace'));
    }

    public function test_company_is_denied_admin_roles_page(): void
    {
        ApiSession::store('company-token', [
            'id' => 12,
            'email' => 'company@example.com',
            'name' => 'Company',
            'account_type' => 'company',
        ], ApiSession::REALM_USER);

        $this->get(route('frontend.admin.roles.index'))
            ->assertRedirect(route('frontend.workspace'));
    }

    public function test_legacy_user_admin_session_is_rejected_from_admin_pages(): void
    {
        ApiSession::store('admin-token', [
            'id' => 90,
            'email' => 'admin@example.com',
            'name' => 'Admin',
            'account_type' => 'admin',
        ], ApiSession::REALM_USER);

        $admins = Mockery::mock(AdminAPI::class);
        $admins->shouldNotReceive('me');
        $admins->shouldNotReceive('getDashboard');

        $middleware = new EnsureAdminAccess($admins);
        $request = Request::create('/admin/dashboard', 'GET');
        $request->setUserResolver(fn () => auth()->user());

        $response = $middleware->handle($request, fn () => response('should-not-run', 200));

        $this->assertTrue($response->isRedirect());
        $this->assertSame(route('frontend.account.admin-login'), $response->headers->get('Location'));
        $this->assertFalse(ApiSession::check());
        $this->assertNull(ApiSession::token());
    }

    public function test_legacy_user_admin_cannot_open_dashboard_via_http(): void
    {
        ApiSession::store('legacy-admin-token', [
            'id' => 90,
            'email' => 'admin@example.com',
            'name' => 'Super Admin',
            'account_type' => 'admin',
        ], ApiSession::REALM_USER);

        $admins = Mockery::mock(AdminAPI::class);
        $admins->shouldNotReceive('me');
        $admins->shouldNotReceive('getDashboard');
        $admins->shouldNotReceive('getAnalytics');
        $this->app->instance(AdminAPI::class, $admins);

        $this->get(route('frontend.dashboard'))
            ->assertRedirect(route('frontend.account.admin-login'));
        $this->assertFalse(ApiSession::check());
    }

    public function test_platform_admin_session_revalidates_via_admin_me_not_auth_me(): void
    {
        ApiSession::store('platform-admin-token', [
            'id' => 7,
            'email' => 'super@example.com',
            'name' => 'Super',
            'account_type' => 'admin',
        ], ApiSession::REALM_PLATFORM_ADMIN);

        $admins = Mockery::mock(AdminAPI::class);
        $admins->shouldReceive('me')->once()->andReturn([
            'message' => 'OK',
            'data' => [
                'id' => 7,
                'email' => 'super@example.com',
                'name' => 'Super',
                'roles' => ['super_admin'],
                'permissions' => ['reports.view', 'users.manage'],
            ],
            'errors' => null,
            'statusCode' => 200,
        ]);

        $middleware = new EnsureAdminAccess($admins);
        $request = Request::create('/admin/dashboard', 'GET');
        $request->setUserResolver(fn () => auth()->user());

        $response = $middleware->handle($request, fn () => response('platform-ok', 200));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('platform-ok', $response->getContent());
        $this->assertTrue(ApiSession::isPlatformAdmin());
        $this->assertSame('admin', ApiSession::accountType());
        $this->assertSame(['super_admin'], ApiSession::user()['roles'] ?? null);
        $this->assertSame('platform-admin-token', ApiSession::token());
    }

    public function test_platform_admin_401_redirects_to_admin_login(): void
    {
        ApiSession::store('stale-platform', [
            'id' => 8,
            'email' => 'super@example.com',
            'name' => 'Super',
            'account_type' => 'admin',
        ], ApiSession::REALM_PLATFORM_ADMIN);

        $admins = Mockery::mock(AdminAPI::class);
        $admins->shouldReceive('me')->once()->andReturn([
            'message' => 'Unauthenticated.',
            'data' => null,
            'errors' => null,
            'statusCode' => 401,
        ]);

        $middleware = new EnsureAdminAccess($admins);
        $response = $middleware->handle(Request::create('/admin/users', 'GET'), fn () => response('no'));

        $this->assertSame(route('frontend.account.admin-login'), $response->headers->get('Location'));
        $this->assertFalse(ApiSession::check());
    }

    public function test_platform_admin_403_from_me_keeps_session_and_redirects_to_workspace(): void
    {
        ApiSession::store('platform-admin-token', [
            'id' => 92,
            'email' => 'super@example.com',
            'name' => 'Super',
            'account_type' => 'admin',
        ], ApiSession::REALM_PLATFORM_ADMIN);

        $admins = Mockery::mock(AdminAPI::class);
        $admins->shouldReceive('me')->once()->andReturn([
            'message' => 'Forbidden.',
            'data' => null,
            'errors' => null,
            'statusCode' => 403,
        ]);

        $middleware = new EnsureAdminAccess($admins);
        $request = Request::create('/admin/users', 'GET');

        $response = $middleware->handle($request, fn () => response('should-not-run'));

        $this->assertTrue($response->isRedirect());
        $this->assertSame(route('frontend.workspace'), $response->headers->get('Location'));
        $this->assertTrue(ApiSession::check());
        $this->assertSame('platform-admin-token', ApiSession::token());
        $this->assertTrue(ApiSession::isPlatformAdmin());
    }

    public function test_wrong_realm_token_is_not_accepted_for_admin_pages(): void
    {
        // Candidate/Company style user-realm token with a forged admin account_type claim.
        ApiSession::store('user-realm-token', [
            'id' => 93,
            'email' => 'forged@example.com',
            'name' => 'Forged',
            'account_type' => 'admin',
        ], ApiSession::REALM_USER);

        $admins = Mockery::mock(AdminAPI::class);
        $admins->shouldNotReceive('me');
        $this->app->instance(AdminAPI::class, $admins);

        $this->get(route('frontend.admin.users.index'))
            ->assertRedirect(route('frontend.account.admin-login'));
        $this->assertFalse(ApiSession::check());
    }

    public function test_missing_admin_session_redirects_to_admin_login(): void
    {
        ApiSession::forget();

        $this->get(route('frontend.dashboard'))
            ->assertRedirect();
    }
}

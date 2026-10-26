<?php

namespace Tests\Feature;

use App\Frontend\Auth\ApiSession;
use App\Http\Middleware\EnsureAdminAccess;
use App\Models\AccountAPI;
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
        ]);

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
        ]);

        $this->get(route('frontend.admin.roles.index'))
            ->assertRedirect(route('frontend.workspace'));
    }

    public function test_admin_session_allows_request_when_backend_confirms_admin(): void
    {
        ApiSession::store('admin-token', [
            'id' => 90,
            'email' => 'admin@example.com',
            'name' => 'Admin',
            'account_type' => 'admin',
        ]);

        $accounts = Mockery::mock(AccountAPI::class);
        $accounts->shouldReceive('getProfile')->once()->andReturn([
            'message' => 'OK',
            'data' => [
                'id' => 90,
                'email' => 'admin@example.com',
                'name' => 'Admin',
                'account_type' => 'admin',
            ],
            'errors' => [],
            'meta' => null,
            'statusCode' => 200,
            'httpStatus' => 200,
        ]);

        $middleware = new EnsureAdminAccess($accounts);
        $request = Request::create('/admin/roles', 'GET');
        $request->setUserResolver(fn () => auth()->user());

        $response = $middleware->handle($request, fn () => response('admin-ok', 200));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('admin-ok', $response->getContent());
        $this->assertSame('admin', ApiSession::accountType());
        $this->assertSame('admin-token', ApiSession::token());
    }

    public function test_401_from_backend_clears_session_and_redirects_to_login(): void
    {
        ApiSession::store('stale-token', [
            'id' => 91,
            'email' => 'admin@example.com',
            'name' => 'Admin',
            'account_type' => 'admin',
        ]);

        $accounts = Mockery::mock(AccountAPI::class);
        $accounts->shouldReceive('getProfile')->once()->andReturn([
            'message' => 'Unauthenticated.',
            'data' => null,
            'errors' => [],
            'meta' => null,
            'statusCode' => 401,
            'httpStatus' => 401,
        ]);

        $middleware = new EnsureAdminAccess($accounts);
        $request = Request::create('/admin/users', 'GET');

        $response = $middleware->handle($request, fn () => response('should-not-run'));

        $this->assertTrue($response->isRedirect());
        $this->assertSame(route('frontend.account.login'), $response->headers->get('Location'));
        $this->assertFalse(ApiSession::check());
        $this->assertNull(ApiSession::token());
    }

    public function test_403_from_backend_keeps_session_and_redirects_to_workspace(): void
    {
        ApiSession::store('admin-token', [
            'id' => 92,
            'email' => 'admin@example.com',
            'name' => 'Admin',
            'account_type' => 'admin',
        ]);

        $accounts = Mockery::mock(AccountAPI::class);
        $accounts->shouldReceive('getProfile')->once()->andReturn([
            'message' => 'Forbidden.',
            'data' => null,
            'errors' => [],
            'meta' => null,
            'statusCode' => 403,
            'httpStatus' => 403,
        ]);

        $middleware = new EnsureAdminAccess($accounts);
        $request = Request::create('/admin/users', 'GET');

        $response = $middleware->handle($request, fn () => response('should-not-run'));

        $this->assertTrue($response->isRedirect());
        $this->assertSame(route('frontend.workspace'), $response->headers->get('Location'));
        $this->assertTrue(ApiSession::check());
        $this->assertSame('admin-token', ApiSession::token());
        $this->assertSame('admin', ApiSession::accountType());
    }

    public function test_stale_admin_session_is_corrected_when_backend_reports_candidate(): void
    {
        ApiSession::store('stale-admin-token', [
            'id' => 93,
            'email' => 'user@example.com',
            'name' => 'User',
            'account_type' => 'admin',
        ]);

        $accounts = Mockery::mock(AccountAPI::class);
        $accounts->shouldReceive('getProfile')->once()->andReturn([
            'message' => 'OK',
            'data' => [
                'id' => 93,
                'email' => 'user@example.com',
                'name' => 'User',
                'account_type' => 'candidate',
            ],
            'errors' => [],
            'meta' => null,
            'statusCode' => 200,
            'httpStatus' => 200,
        ]);

        $middleware = new EnsureAdminAccess($accounts);
        $request = Request::create('/admin/dashboard', 'GET');

        $response = $middleware->handle($request, fn () => response('should-not-run'));

        $this->assertTrue($response->isRedirect());
        $this->assertSame(route('frontend.workspace'), $response->headers->get('Location'));
        $this->assertSame('candidate', ApiSession::accountType());
        $this->assertSame('stale-admin-token', ApiSession::token());
    }
}

<?php

namespace Tests\Feature;

use App\Frontend\Auth\ApiSession;
use App\Models\AccountAPI;
use Mockery;
use Tests\TestCase;

class AccountSettingsTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_guest_cannot_access_profile_or_change_password(): void
    {
        $this->get(route('frontend.account.profile'))
            ->assertRedirect(route('frontend.account.login'));

        $this->get(route('frontend.account.change-password'))
            ->assertRedirect(route('frontend.account.login'));

        $this->post(route('frontend.account.change-password.submit'), [
            'current_password' => 'old-password',
            'new_password' => 'new-password',
            'new_password_confirmation' => 'new-password',
        ])->assertRedirect(route('frontend.account.login'));
    }

    public function test_authenticated_admin_can_view_profile_from_backend_me(): void
    {
        $this->actingAsAdminSession();

        $accounts = Mockery::mock(AccountAPI::class);
        $accounts->shouldReceive('getProfile')->once()->andReturn([
            'message' => 'OK',
            'data' => [
                'id' => 7,
                'name' => 'Priya Shah',
                'email' => 'admin@example.com',
                'account_type' => 'admin',
                'account_status' => 'active',
                'roles' => ['super_admin'],
                'permissions' => ['users.manage', 'roles.manage'],
                'created_at' => '2026-01-15T10:00:00+00:00',
            ],
            'errors' => [],
            'meta' => null,
            'statusCode' => 200,
            'httpStatus' => 200,
        ]);
        $this->app->instance(AccountAPI::class, $accounts);

        $this->get(route('frontend.account.profile'))
            ->assertOk()
            ->assertSee('Priya Shah', false)
            ->assertSee('admin@example.com', false)
            ->assertSee('super_admin', false)
            ->assertDontSee('ChangeMe123!', false);
    }

    public function test_profile_401_clears_session_and_redirects_to_login(): void
    {
        $this->actingAsAdminSession();

        $accounts = Mockery::mock(AccountAPI::class);
        $accounts->shouldReceive('getProfile')->once()->andReturn([
            'message' => 'Unauthenticated.',
            'data' => null,
            'errors' => [],
            'meta' => null,
            'statusCode' => 401,
            'httpStatus' => 401,
        ]);
        $this->app->instance(AccountAPI::class, $accounts);

        $this->get(route('frontend.account.profile'))
            ->assertRedirect(route('frontend.account.login'));

        $this->assertFalse(ApiSession::check());
    }

    public function test_profile_403_keeps_session_and_redirects_to_workspace(): void
    {
        $this->actingAsAdminSession();

        $accounts = Mockery::mock(AccountAPI::class);
        $accounts->shouldReceive('getProfile')->once()->andReturn([
            'message' => 'Forbidden.',
            'data' => null,
            'errors' => [],
            'meta' => null,
            'statusCode' => 403,
            'httpStatus' => 403,
        ]);
        $this->app->instance(AccountAPI::class, $accounts);

        $this->get(route('frontend.account.profile'))
            ->assertRedirect(route('frontend.workspace'));

        $this->assertTrue(ApiSession::check());
        $this->assertSame('admin-token', ApiSession::token());
    }

    public function test_change_password_validates_required_fields(): void
    {
        $this->actingAsAdminSession();

        $this->from(route('frontend.account.change-password'))
            ->post(route('frontend.account.change-password.submit'), [])
            ->assertRedirect(route('frontend.account.change-password'))
            ->assertSessionHasErrors(['current_password', 'new_password']);

        $this->assertTrue(ApiSession::check());
    }

    public function test_change_password_validates_confirmation(): void
    {
        $this->actingAsAdminSession();

        $this->from(route('frontend.account.change-password'))
            ->post(route('frontend.account.change-password.submit'), [
                'current_password' => 'current-secret',
                'new_password' => 'new-secret1',
                'new_password_confirmation' => 'mismatch',
            ])
            ->assertRedirect(route('frontend.account.change-password'))
            ->assertSessionHasErrors(['new_password']);
    }

    public function test_change_password_submit_does_not_mutate_and_reports_unavailable(): void
    {
        $this->actingAsAdminSession();

        $accounts = Mockery::mock(AccountAPI::class);
        $accounts->shouldNotReceive('getProfile');
        $this->app->instance(AccountAPI::class, $accounts);

        $this->from(route('frontend.account.change-password'))
            ->post(route('frontend.account.change-password.submit'), [
                'current_password' => 'current-secret',
                'new_password' => 'new-secret12',
                'new_password_confirmation' => 'new-secret12',
            ])
            ->assertRedirect(route('frontend.account.change-password'))
            ->assertSessionHasErrors(['error']);

        $this->assertTrue(ApiSession::check());
        $this->assertSame('admin-token', ApiSession::token());
    }

    public function test_candidate_still_denied_admin_users_page(): void
    {
        ApiSession::store('candidate-token', [
            'id' => 3,
            'email' => 'candidate@example.com',
            'name' => 'Candidate',
            'account_type' => 'candidate',
        ]);

        $this->get(route('frontend.admin.users.index'))
            ->assertRedirect(route('frontend.workspace'));
    }

    private function actingAsAdminSession(): void
    {
        ApiSession::store('admin-token', [
            'id' => 7,
            'email' => 'admin@example.com',
            'name' => 'Admin',
            'account_type' => 'admin',
        ]);
    }
}

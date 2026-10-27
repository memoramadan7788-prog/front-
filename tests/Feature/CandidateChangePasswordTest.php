<?php

namespace Tests\Feature;

use App\Frontend\Auth\ApiSession;
use App\Models\AccountAPI;
use App\Models\CandidateAPI;
use Mockery;
use Tests\TestCase;

class CandidateChangePasswordTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_candidate_form_is_available_with_submit_guard_attributes(): void
    {
        $this->actingAsCandidate();

        $html = $this->get(route('frontend.account.change-password'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('data-password-change-form', $html);
        $this->assertStringContainsString('data-password-change-available="1"', $html);
        $this->assertStringContainsString('data-password-change-submit', $html);
        $this->assertStringContainsString(__('common.password_change.hint_available'), $html);
        $this->assertStringNotContainsString('data-password-change-unavailable', $html);
        $this->assertStringNotContainsString('current-secret', $html);
        $this->assertStringNotContainsString('new-secret12', $html);
    }

    public function test_successful_password_change_keeps_session_and_does_not_echo_secrets(): void
    {
        $this->actingAsCandidate();

        $candidates = Mockery::mock(CandidateAPI::class);
        $candidates->shouldReceive('changePassword')
            ->once()
            ->with(Mockery::on(function (array $payload): bool {
                return ($payload['current_password'] ?? null) === 'current-secret'
                    && ($payload['new_password'] ?? null) === 'new-secret12'
                    && ($payload['new_password_confirmation'] ?? null) === 'new-secret12'
                    && array_keys($payload) === ['current_password', 'new_password', 'new_password_confirmation'];
            }))
            ->andReturn([
                'message' => 'Password changed successfully.',
                'data' => null,
                'statusCode' => 200,
                'errors' => null,
            ]);
        $this->app->instance(CandidateAPI::class, $candidates);

        $response = $this->from(route('frontend.account.change-password'))
            ->post(route('frontend.account.change-password.submit'), [
                'current_password' => 'current-secret',
                'new_password' => 'new-secret12',
                'new_password_confirmation' => 'new-secret12',
            ]);

        $response->assertRedirect(route('frontend.account.change-password'))
            ->assertSessionHas('success_key', 'flash.candidate.password_changed')
            ->assertSessionMissing('errors');

        $this->assertTrue(ApiSession::check());
        $this->assertSame('cand-token', ApiSession::token());

        $follow = $this->get(route('frontend.account.change-password'))->assertOk()->getContent();
        $this->assertStringNotContainsString('current-secret', $follow);
        $this->assertStringNotContainsString('new-secret12', $follow);
        $this->assertStringContainsString(__('flash.candidate.password_changed'), $follow);
    }

    public function test_incorrect_current_password_maps_422_field_errors(): void
    {
        $this->actingAsCandidate();

        $candidates = Mockery::mock(CandidateAPI::class);
        $candidates->shouldReceive('changePassword')->once()->andReturn([
            'message' => 'The given data was invalid.',
            'data' => null,
            'statusCode' => 422,
            'errors' => [
                'current_password' => ['The provided current password does not match our records.'],
            ],
        ]);
        $this->app->instance(CandidateAPI::class, $candidates);

        $this->from(route('frontend.candidate.change-password'))
            ->post(route('frontend.candidate.change-password.submit'), [
                'current_password' => 'wrong-old',
                'new_password' => 'new-secret12',
                'new_password_confirmation' => 'new-secret12',
            ])
            ->assertRedirect(route('frontend.candidate.change-password'))
            ->assertSessionHasErrors(['current_password']);

        $this->assertTrue(ApiSession::check());
        $this->assertSame('cand-token', ApiSession::token());
    }

    public function test_local_confirmation_mismatch_and_weak_password_validation(): void
    {
        $this->actingAsCandidate();

        $candidates = Mockery::mock(CandidateAPI::class);
        $candidates->shouldNotReceive('changePassword');
        $this->app->instance(CandidateAPI::class, $candidates);

        $this->from(route('frontend.account.change-password'))
            ->post(route('frontend.account.change-password.submit'), [
                'current_password' => 'current-secret',
                'new_password' => 'new-secret12',
                'new_password_confirmation' => 'mismatch',
            ])
            ->assertRedirect(route('frontend.account.change-password'))
            ->assertSessionHasErrors(['new_password']);

        $this->from(route('frontend.account.change-password'))
            ->post(route('frontend.account.change-password.submit'), [
                'current_password' => 'current-secret',
                'new_password' => 'short',
                'new_password_confirmation' => 'short',
            ])
            ->assertRedirect(route('frontend.account.change-password'))
            ->assertSessionHasErrors(['new_password']);
    }

    public function test_password_must_differ_from_current(): void
    {
        $this->actingAsCandidate();

        $candidates = Mockery::mock(CandidateAPI::class);
        $candidates->shouldNotReceive('changePassword');
        $this->app->instance(CandidateAPI::class, $candidates);

        $this->from(route('frontend.account.change-password'))
            ->post(route('frontend.account.change-password.submit'), [
                'current_password' => 'same-secret1',
                'new_password' => 'same-secret1',
                'new_password_confirmation' => 'same-secret1',
            ])
            ->assertRedirect(route('frontend.account.change-password'))
            ->assertSessionHasErrors(['new_password']);
    }

    public function test_change_password_401_clears_session(): void
    {
        $this->actingAsCandidate();

        $candidates = Mockery::mock(CandidateAPI::class);
        $candidates->shouldReceive('changePassword')->once()->andReturn([
            'message' => 'Unauthenticated.',
            'data' => null,
            'statusCode' => 401,
            'errors' => null,
        ]);
        $this->app->instance(CandidateAPI::class, $candidates);

        $this->post(route('frontend.account.change-password.submit'), [
            'current_password' => 'current-secret',
            'new_password' => 'new-secret12',
            'new_password_confirmation' => 'new-secret12',
        ])->assertRedirect(route('frontend.account.login'));

        $this->assertFalse(ApiSession::check());
    }

    public function test_change_password_403_keeps_session_and_redirects_workspace(): void
    {
        $this->actingAsCandidate();

        $candidates = Mockery::mock(CandidateAPI::class);
        $candidates->shouldReceive('changePassword')->once()->andReturn([
            'message' => 'Forbidden.',
            'data' => null,
            'statusCode' => 403,
            'errors' => null,
        ]);
        $this->app->instance(CandidateAPI::class, $candidates);

        $this->post(route('frontend.account.change-password.submit'), [
            'current_password' => 'current-secret',
            'new_password' => 'new-secret12',
            'new_password_confirmation' => 'new-secret12',
        ])->assertRedirect(route('frontend.workspace'));

        $this->assertTrue(ApiSession::check());
        $this->assertSame('cand-token', ApiSession::token());
    }

    public function test_company_and_admin_do_not_call_candidate_change_password_api(): void
    {
        $candidates = Mockery::mock(CandidateAPI::class);
        $candidates->shouldNotReceive('changePassword');
        $this->app->instance(CandidateAPI::class, $candidates);

        ApiSession::store('co-token', [
            'id' => 2,
            'email' => 'hr@example.com',
            'name' => 'HR',
            'account_type' => 'company',
        ]);

        $html = $this->get(route('frontend.company.change-password'))->assertOk()->getContent();
        $this->assertStringContainsString('data-password-change-unavailable', $html);
        $this->assertStringContainsString('data-password-change-available="0"', $html);

        $this->from(route('frontend.company.change-password'))
            ->post(route('frontend.company.change-password.submit'), [
                'current_password' => 'current-secret',
                'new_password' => 'new-secret12',
                'new_password_confirmation' => 'new-secret12',
            ])
            ->assertRedirect(route('frontend.company.change-password'))
            ->assertSessionHasErrors(['error']);

        ApiSession::store('admin-token', [
            'id' => 7,
            'email' => 'admin@example.com',
            'name' => 'Admin',
            'account_type' => 'admin',
        ]);

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

    public function test_candidate_profile_marks_password_change_available(): void
    {
        $this->actingAsCandidate();

        $accounts = Mockery::mock(AccountAPI::class);
        $accounts->shouldReceive('getProfile')->once()->andReturn([
            'message' => 'OK',
            'data' => [
                'id' => 41,
                'name' => 'Notify Candidate',
                'email' => 'cand@example.com',
                'account_type' => 'candidate',
            ],
            'errors' => [],
            'meta' => null,
            'statusCode' => 200,
            'httpStatus' => 200,
        ]);
        $this->app->instance(AccountAPI::class, $accounts);

        $html = $this->get(route('frontend.account.profile'))->assertOk()->getContent();
        $this->assertStringContainsString(__('common.password_change.hint_available'), $html);
        $this->assertStringContainsString(route('frontend.account.change-password', [], false), $html);
        $this->assertStringNotContainsString(__('common.password_change.unavailable_role'), $html);
    }

    public function test_arabic_change_password_copy(): void
    {
        $this->actingAsCandidate();

        $html = $this->withUnencryptedCookie('hb_locale', 'ar')
            ->get(route('frontend.account.change-password'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(__('common.change_password', [], 'ar'), $html);
        $this->assertStringContainsString(__('common.password_change.submit', [], 'ar'), $html);
        $this->assertStringContainsString('dir="rtl"', $html);
    }

    private function actingAsCandidate(): void
    {
        ApiSession::store('cand-token', [
            'id' => 41,
            'email' => 'cand@example.com',
            'name' => 'Notify Candidate',
            'account_type' => 'candidate',
            'roles' => ['candidate'],
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Frontend\Auth\ApiSession;
use App\Models\EmployeeAPI;
use App\Support\Frontend\CompanyMembership\CompanyMembershipGateway;
use Mockery;
use Tests\TestCase;

class CandidateCompanyMembershipTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_guest_is_redirected_from_join_company(): void
    {
        $this->get(route('frontend.candidate.join-company'))
            ->assertRedirect(route('frontend.account.login'));

        $this->post(route('frontend.candidate.join-company.submit'), [
            'company_id' => 1,
            'requested_job_title' => 'Engineer',
            'requested_department' => 'Engineering',
        ])->assertRedirect(route('frontend.account.login'));
    }

    public function test_page_shows_dropdown_unavailable_and_disables_submit(): void
    {
        $this->actingAsCandidate();
        $this->bindUnavailableGateway();

        $html = $this->get(route('frontend.candidate.join-company'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(__('candidate.membership.title'), $html);
        $this->assertStringContainsString(__('candidate.membership.vs_jobs_title'), $html);
        $this->assertStringContainsString(__('candidate.membership.list_unavailable_title'), $html);
        $this->assertStringContainsString(__('candidate.membership.status_unavailable_title'), $html);
        $this->assertStringContainsString(__('candidate.membership.requests_title'), $html);
        $this->assertStringContainsString(__('candidate.membership.company'), $html);
        $this->assertStringContainsString(__('candidate.membership.job_title'), $html);
        $this->assertStringContainsString(__('candidate.membership.department'), $html);
        $this->assertStringContainsString(__('candidate.membership.approval_note'), $html);
        $this->assertStringContainsString(__('status_vocab.membership.pending'), $html);
        $this->assertStringContainsString(__('status_vocab.membership.approved'), $html);
        $this->assertStringContainsString(__('status_vocab.membership.rejected'), $html);
        $this->assertStringContainsString(__('status_vocab.membership.removed'), $html);
        $this->assertStringContainsString(route('frontend.candidate.vacancies.index', [], false), $html);
        $this->assertStringContainsString(__('navigation.candidate.membership'), $html);
        $this->assertStringContainsString('data-discovery-unavailable', $html);
        $this->assertStringNotContainsString('name="company_id"', $html);
        $this->assertStringNotContainsString('Acme Technologies', $html);
        $this->assertStringNotContainsString('data-company-card', $html);
        $this->assertStringNotContainsString('data-company-list', $html);
        $this->assertMatchesRegularExpression('/type="submit"[^>]*\bdisabled\b|disabled[^>]*type="submit"/', $html);
    }

    public function test_post_while_discovery_unavailable_never_calls_join_api(): void
    {
        $this->actingAsCandidate();

        $employees = Mockery::mock(EmployeeAPI::class);
        $employees->shouldReceive('requestToJoin')->never();
        $this->app->instance(EmployeeAPI::class, $employees);

        $gateway = Mockery::mock(CompanyMembershipGateway::class);
        $gateway->shouldReceive('canSubmitJoinRequest')->once()->andReturn(false);
        $gateway->shouldReceive('requestToJoin')->never();
        $this->app->instance(CompanyMembershipGateway::class, $gateway);

        $this->from(route('frontend.candidate.join-company'))
            ->post(route('frontend.candidate.join-company.submit'), [
                'company_id' => 7,
                'requested_job_title' => 'Analyst',
                'requested_department' => 'Ops',
            ])
            ->assertRedirect(route('frontend.candidate.join-company'))
            ->assertSessionHasErrors('error');

        $this->assertSame(__('candidate.membership.submit_disabled'), session('errors')->first('error'));
    }

    public function test_companies_render_as_single_select_dropdown_without_showing_ids_as_labels(): void
    {
        $this->actingAsCandidate();

        $gateway = Mockery::mock(CompanyMembershipGateway::class);
        $gateway->shouldReceive('searchCompanies')->once()->with(null, 1)->andReturn([
            'available' => true,
            'statusCode' => 200,
            'message' => 'OK',
            'data' => [
                [
                    'id' => 12,
                    'name' => 'Northstar Labs',
                    'industry' => 'Technology',
                ],
                [
                    'id' => 15,
                    'name' => 'Meridian Studio',
                    'industry' => 'Design',
                ],
            ],
            'meta' => [
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => 100,
                'total' => 2,
            ],
            'errors' => null,
        ]);
        $gateway->shouldReceive('myMembership')->once()->andReturn([
            'available' => false,
            'statusCode' => 0,
            'message' => 'not configured',
            'data' => null,
            'errors' => null,
        ]);
        $gateway->shouldReceive('canSubmitJoinRequest')->once()->andReturn(true);
        $this->app->instance(CompanyMembershipGateway::class, $gateway);

        $html = $this->get(route('frontend.candidate.join-company'))->assertOk()->getContent();

        $this->assertStringContainsString('name="company_id"', $html);
        $this->assertStringContainsString('<select', $html);
        $this->assertStringContainsString('Northstar Labs', $html);
        $this->assertStringContainsString('Meridian Studio', $html);
        $this->assertStringContainsString('value="12"', $html);
        $this->assertStringContainsString('value="15"', $html);
        $this->assertStringContainsString(__('candidate.membership.company'), $html);
        $this->assertStringContainsString(__('candidate.membership.company_placeholder'), $html);
        $this->assertStringNotContainsString('type="radio"', $html);
        $this->assertStringNotContainsString('data-company-card', $html);
        $this->assertStringNotContainsString(__('candidate.membership.company_load_more'), $html);
        $this->assertStringNotContainsString(__('candidate.membership.list_unavailable_title'), $html);
        $this->assertStringNotContainsString('>12<', $html);
        $this->assertStringNotContainsString('>15<', $html);
        $this->assertDoesNotMatchRegularExpression('/type="submit"[^>]*\bdisabled\b|disabled[^>]*type="submit"/', $html);
    }

    public function test_empty_company_list_disables_submit(): void
    {
        $this->actingAsCandidate();

        $gateway = Mockery::mock(CompanyMembershipGateway::class);
        $gateway->shouldReceive('searchCompanies')->once()->with(null, 1)->andReturn([
            'available' => true,
            'statusCode' => 200,
            'message' => 'OK',
            'data' => [],
            'meta' => ['current_page' => 1, 'last_page' => 1, 'total' => 0],
            'errors' => null,
        ]);
        $gateway->shouldReceive('myMembership')->once()->andReturn([
            'available' => false,
            'statusCode' => 0,
            'message' => 'not configured',
            'data' => null,
            'errors' => null,
        ]);
        $gateway->shouldReceive('canSubmitJoinRequest')->once()->andReturn(true);
        $this->app->instance(CompanyMembershipGateway::class, $gateway);

        $html = $this->get(route('frontend.candidate.join-company'))->assertOk()->getContent();

        $this->assertStringContainsString(__('candidate.membership.company_list_empty_title'), $html);
        $this->assertMatchesRegularExpression('/type="submit"[^>]*\bdisabled\b|disabled[^>]*type="submit"/', $html);
    }

    public function test_successful_join_when_discovery_enabled_uses_existing_contract(): void
    {
        $this->actingAsCandidate();

        $gateway = Mockery::mock(CompanyMembershipGateway::class);
        $gateway->shouldReceive('canSubmitJoinRequest')->once()->andReturn(true);
        $gateway->shouldReceive('requestToJoin')->once()->with([
            'company_id' => '7',
            'requested_job_title' => 'Interviewer',
            'requested_department' => 'People',
        ])->andReturn([
            'statusCode' => 200,
            'message' => 'Join request submitted successfully. Awaiting company review.',
            'data' => ['employee' => ['id' => 42, 'status' => 'pending']],
            'errors' => null,
        ]);
        $this->app->instance(CompanyMembershipGateway::class, $gateway);

        $this->from(route('frontend.candidate.join-company'))
            ->post(route('frontend.candidate.join-company.submit'), [
                'company_id' => 7,
                'requested_job_title' => 'Interviewer',
                'requested_department' => 'People',
            ])
            ->assertRedirect(route('frontend.candidate.join-company'))
            ->assertSessionHas('success_key', 'flash.employee.join_requested');
    }

    public function test_membership_rows_render_when_gateway_returns_backend_data(): void
    {
        $this->actingAsCandidate();

        $gateway = Mockery::mock(CompanyMembershipGateway::class);
        $gateway->shouldReceive('searchCompanies')->once()->andReturn([
            'available' => false,
            'statusCode' => 0,
            'message' => 'not configured',
            'data' => [],
            'meta' => null,
            'errors' => null,
        ]);
        $gateway->shouldReceive('myMembership')->once()->andReturn([
            'available' => true,
            'statusCode' => 200,
            'message' => 'OK',
            'data' => [[
                'id' => 3,
                'company_name' => 'Northstar Labs',
                'requested_job_title' => 'Recruiter',
                'requested_department' => 'People',
                'status' => 'pending',
                'requested_at' => '2026-10-01T10:00:00+00:00',
            ]],
            'errors' => null,
        ]);
        $gateway->shouldReceive('canSubmitJoinRequest')->once()->andReturn(false);
        $this->app->instance(CompanyMembershipGateway::class, $gateway);

        $html = $this->get(route('frontend.candidate.join-company'))->assertOk()->getContent();

        $this->assertStringContainsString('Northstar Labs', $html);
        $this->assertStringContainsString('Recruiter', $html);
        $this->assertStringContainsString(__('status_vocab.membership.pending'), $html);
        $this->assertStringNotContainsString(__('candidate.membership.status_unavailable_title'), $html);
    }

    public function test_membership_api_error_shows_error_not_empty_success(): void
    {
        $this->actingAsCandidate();

        $gateway = Mockery::mock(CompanyMembershipGateway::class);
        $gateway->shouldReceive('searchCompanies')->once()->andReturn([
            'available' => false,
            'statusCode' => 0,
            'message' => 'not configured',
            'data' => [],
            'meta' => null,
            'errors' => null,
        ]);
        $gateway->shouldReceive('myMembership')->once()->andReturn([
            'available' => true,
            'statusCode' => 503,
            'message' => 'Service unavailable',
            'data' => null,
            'errors' => null,
        ]);
        $gateway->shouldReceive('canSubmitJoinRequest')->once()->andReturn(false);
        $this->app->instance(CompanyMembershipGateway::class, $gateway);

        $html = $this->get(route('frontend.candidate.join-company'))->assertOk()->getContent();

        $this->assertStringContainsString(__('candidate.membership.status_load_error_title'), $html);
        $this->assertStringContainsString('Service unavailable', $html);
        $this->assertStringNotContainsString(__('candidate.membership.requests_empty_title'), $html);
    }

    public function test_discovery_api_error_shows_error_not_fabricated_list(): void
    {
        $this->actingAsCandidate();

        $gateway = Mockery::mock(CompanyMembershipGateway::class);
        $gateway->shouldReceive('searchCompanies')->once()->andReturn([
            'available' => true,
            'statusCode' => 503,
            'message' => 'Discovery down',
            'data' => [],
            'meta' => null,
            'errors' => null,
        ]);
        $gateway->shouldReceive('myMembership')->once()->andReturn([
            'available' => false,
            'statusCode' => 0,
            'message' => 'not configured',
            'data' => null,
            'errors' => null,
        ]);
        $gateway->shouldReceive('canSubmitJoinRequest')->once()->andReturn(true);
        $this->app->instance(CompanyMembershipGateway::class, $gateway);

        $html = $this->get(route('frontend.candidate.join-company'))->assertOk()->getContent();

        $this->assertStringContainsString(__('candidate.membership.discovery_load_error_title'), $html);
        $this->assertStringContainsString('Discovery down', $html);
        $this->assertStringNotContainsString('name="company_id"', $html);
        $this->assertMatchesRegularExpression('/type="submit"[^>]*\bdisabled\b|disabled[^>]*type="submit"/', $html);
    }

    public function test_unauthorized_join_clears_session_and_redirects_to_login(): void
    {
        $this->actingAsCandidate();

        $gateway = Mockery::mock(CompanyMembershipGateway::class);
        $gateway->shouldReceive('canSubmitJoinRequest')->once()->andReturn(true);
        $gateway->shouldReceive('requestToJoin')->once()->andReturn([
            'statusCode' => 401,
            'message' => 'Unauthenticated.',
            'data' => null,
            'errors' => null,
        ]);
        $this->app->instance(CompanyMembershipGateway::class, $gateway);

        $this->post(route('frontend.candidate.join-company.submit'), [
            'company_id' => 7,
            'requested_job_title' => 'HR',
            'requested_department' => 'People',
        ])->assertRedirect(route('frontend.account.login'));

        $this->assertNull(ApiSession::token());
    }

    public function test_arabic_locale_renders_dropdown_membership_copy(): void
    {
        $this->actingAsCandidate();
        $this->bindUnavailableGateway();

        $html = $this->withUnencryptedCookie('hb_locale', 'ar')
            ->get(route('frontend.candidate.join-company'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(__('candidate.membership.list_unavailable_title', [], 'ar'), $html);
        $this->assertStringContainsString(__('candidate.membership.company', [], 'ar'), $html);
        $this->assertStringContainsString('dir="rtl"', $html);
    }

    private function actingAsCandidate(): void
    {
        ApiSession::store('cand-token', [
            'id' => 11,
            'email' => 'candidate@example.com',
            'name' => 'Candidate',
            'account_type' => 'candidate',
            'roles' => ['candidate'],
        ]);
    }

    private function bindUnavailableGateway(): void
    {
        $gateway = Mockery::mock(CompanyMembershipGateway::class);
        $gateway->shouldReceive('searchCompanies')->once()->andReturn([
            'available' => false,
            'statusCode' => 0,
            'message' => 'Company discovery endpoint is not configured.',
            'data' => [],
            'meta' => null,
            'errors' => null,
        ]);
        $gateway->shouldReceive('myMembership')->once()->andReturn([
            'available' => false,
            'statusCode' => 0,
            'message' => 'Personal membership status endpoint is not configured.',
            'data' => null,
            'errors' => null,
        ]);
        $gateway->shouldReceive('canSubmitJoinRequest')->once()->andReturn(false);
        $this->app->instance(CompanyMembershipGateway::class, $gateway);
    }
}

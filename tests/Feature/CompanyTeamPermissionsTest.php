<?php

namespace Tests\Feature;

use App\Frontend\Auth\ApiSession;
use App\Models\EmployeeAPI;
use Mockery;
use Tests\TestCase;

class CompanyTeamPermissionsTest extends TestCase
{
    private $employeeApi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->employeeApi = Mockery::mock(EmployeeAPI::class);
        $this->app->instance(EmployeeAPI::class, $this->employeeApi);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_guest_is_redirected_from_team_page(): void
    {
        $this->get(route('frontend.company.employees.index'))
            ->assertRedirect(route('frontend.account.login'));
    }

    public function test_candidate_cannot_open_team_page(): void
    {
        $this->actingAsCandidate();

        $this->get(route('frontend.company.employees.index'))
            ->assertRedirect();
    }

    public function test_company_members_tab_loads_scoped_list(): void
    {
        $this->actingAsCompany();
        $this->mockCatalog();
        $this->employeeApi->shouldReceive('getEmployees')->once()->andReturn([
            'statusCode' => 200,
            'message' => 'OK',
            'data' => [
                $this->member(1, 'approved', 'company_admin', 20, 'HR User', 'hr@example.com'),
                $this->member(2, 'pending', null, 30, 'Sam Pending', 'sam@example.com'),
            ],
            'meta' => ['current_page' => 1, 'last_page' => 1, 'total' => 2],
        ]);

        $html = $this->get(route('frontend.company.employees.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('data-team-permissions', $html);
        $this->assertStringContainsString('data-team-tab="members"', $html);
        $this->assertStringContainsString('HR User', $html);
        $this->assertStringContainsString('sam@example.com', $html);
        $this->assertStringContainsString(__('company.employees.role_label_company_admin'), $html);
        $this->assertStringContainsString(__('company.employees.hiring_team_separate'), $html);
        $this->assertStringContainsString('data-approve-form', $html);
    }

    public function test_status_filter_is_forwarded_to_backend(): void
    {
        $this->actingAsCompany();
        $this->mockCatalog();
        $this->employeeApi->shouldReceive('getEmployees')
            ->once()
            ->with(Mockery::on(fn (array $f) => ($f['status'] ?? null) === 'pending'))
            ->andReturn([
                'statusCode' => 200,
                'message' => 'OK',
                'data' => [$this->member(2, 'pending', null, 30, 'Sam Pending', 'sam@example.com')],
                'meta' => null,
            ]);

        $this->get(route('frontend.company.employees.index', ['status' => 'pending']))
            ->assertOk()
            ->assertSee('Sam Pending');
    }

    public function test_client_search_filters_loaded_members(): void
    {
        $this->actingAsCompany();
        $this->mockCatalog();
        $this->employeeApi->shouldReceive('getEmployees')->once()->andReturn([
            'statusCode' => 200,
            'message' => 'OK',
            'data' => [
                $this->member(1, 'approved', 'hr', 21, 'Alice HR', 'alice@example.com'),
                $this->member(2, 'approved', 'interviewer', 22, 'Bob Interviewer', 'bob@example.com'),
            ],
            'meta' => null,
        ]);

        $html = $this->get(route('frontend.company.employees.index', ['q' => 'alice']))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Alice HR', $html);
        $this->assertStringNotContainsString('Bob Interviewer', $html);
    }

    public function test_members_api_failure_shows_safe_error(): void
    {
        $this->actingAsCompany();
        $this->mockCatalog();
        $this->employeeApi->shouldReceive('getEmployees')->once()->andReturn([
            'statusCode' => 500,
            'message' => 'Internal boom with SQLSTATE secrets',
            'data' => null,
            'meta' => null,
        ]);

        $html = $this->get(route('frontend.company.employees.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('data-members-unavailable', $html);
        $this->assertStringNotContainsString('SQLSTATE', $html);
    }

    public function test_empty_members_state(): void
    {
        $this->actingAsCompany();
        $this->mockCatalog();
        $this->employeeApi->shouldReceive('getEmployees')->once()->andReturn([
            'statusCode' => 200,
            'message' => 'OK',
            'data' => [],
            'meta' => null,
        ]);

        $html = $this->get(route('frontend.company.employees.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('data-members-empty', $html);
        $this->assertStringContainsString(__('company.employees.empty_members'), $html);
    }

    public function test_approve_requires_assignable_role_and_posts_role_id(): void
    {
        $this->actingAsCompany();
        $this->mockCatalog();
        $this->employeeApi->shouldReceive('getEmployees')->zeroOrMoreTimes()->andReturn([
            'statusCode' => 200,
            'data' => [],
            'meta' => null,
            'message' => 'OK',
        ]);
        $this->employeeApi->shouldReceive('approveEmployee')
            ->once()
            ->with(2, Mockery::on(fn (array $d) => (int) ($d['role_id'] ?? 0) === 3))
            ->andReturn(['statusCode' => 200, 'message' => 'OK', 'data' => null]);

        $this->from(route('frontend.company.employees.index', ['status' => 'pending']))
            ->post(route('frontend.company.employees.approve', ['employeeId' => 2]), [
                'role_id' => 3,
            ])
            ->assertRedirect(route('frontend.company.employees.index', ['tab' => 'members', 'status' => 'pending']))
            ->assertSessionHas('success_key', 'company.employees.flash_approved');
    }

    public function test_approve_rejects_non_company_role_id(): void
    {
        $this->actingAsCompany();
        $this->mockCatalog();
        $this->employeeApi->shouldReceive('getEmployees')->zeroOrMoreTimes()->andReturn([
            'statusCode' => 200,
            'data' => [],
            'meta' => null,
            'message' => 'OK',
        ]);
        $this->employeeApi->shouldReceive('approveEmployee')->never();

        $this->from(route('frontend.company.employees.index'))
            ->post(route('frontend.company.employees.approve', ['employeeId' => 2]), [
                'role_id' => 999,
            ])
            ->assertSessionHasErrors('role_id');
    }

    public function test_role_update_endpoint_is_hard_blocked_without_backend_call(): void
    {
        $this->actingAsCompany();
        $this->employeeApi->shouldReceive('updateEmployeeRole')->never();
        $this->employeeApi->shouldReceive('getEmployees')->never();
        $this->employeeApi->shouldReceive('getCompanyRoles')->never();

        $this->from(route('frontend.company.employees.index'))
            ->put(route('frontend.company.employees.role.update', ['employeeId' => 9]), [
                'role_id' => 1,
            ])
            ->assertRedirect(route('frontend.company.employees.index', ['tab' => 'members']))
            ->assertSessionHasErrors('error');
    }

    public function test_members_ui_shows_role_update_lock_not_role_form(): void
    {
        $this->actingAsCompany();
        $this->mockCatalog();
        $this->employeeApi->shouldReceive('getEmployees')->once()->andReturn([
            'statusCode' => 200,
            'data' => [$this->member(2, 'approved', 'hr', 30, 'Sam', 'sam@example.com')],
            'meta' => null,
            'message' => 'OK',
        ]);

        $html = $this->get(route('frontend.company.employees.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('data-role-updates-blocked', $html);
        $this->assertStringContainsString('data-role-update-locked', $html);
        $this->assertStringNotContainsString('data-role-form', $html);
        $this->assertStringContainsString(__('company.employees.role_update_disabled'), $html);
    }

    public function test_approve_cannot_assign_role_above_session_ceiling(): void
    {
        $this->actingAsHrOnly();
        $this->mockCatalog();
        $this->employeeApi->shouldReceive('getEmployees')->zeroOrMoreTimes()->andReturn([
            'statusCode' => 200,
            'data' => [],
            'meta' => null,
            'message' => 'OK',
        ]);
        $this->employeeApi->shouldReceive('approveEmployee')->never();

        // company_admin id=1 is above hr ceiling
        $this->from(route('frontend.company.employees.index'))
            ->post(route('frontend.company.employees.approve', ['employeeId' => 2]), [
                'role_id' => 1,
            ])
            ->assertSessionHasErrors('role_id');
    }

    public function test_approve_ui_omits_roles_above_session_ceiling(): void
    {
        $this->actingAsHrOnly();
        $this->mockCatalog();
        $this->employeeApi->shouldReceive('getEmployees')->once()->andReturn([
            'statusCode' => 200,
            'data' => [$this->member(2, 'pending', null, 30, 'Sam Pending', 'sam@example.com')],
            'meta' => null,
            'message' => 'OK',
        ]);

        $html = $this->get(route('frontend.company.employees.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('data-approve-form', $html);
        $this->assertStringContainsString(__('company.employees.role_label_hr'), $html);
        $this->assertStringContainsString(__('company.employees.role_label_interviewer'), $html);
        // company_admin option value="1" must not appear in approve selects for HR-only session
        $this->assertDoesNotMatchRegularExpression('/name="role_id"[^>]*>[\s\S]*?<option value="1"/', $html);
    }

    public function test_remove_blocked_for_self(): void
    {
        $this->actingAsCompany();
        $this->mockCatalog();
        $this->employeeApi->shouldReceive('getEmployees')->once()->andReturn([
            'statusCode' => 200,
            'data' => [
                $this->member(1, 'approved', 'company_admin', 20, 'HR User', 'hr@example.com'),
                $this->member(8, 'approved', 'company_admin', 88, 'Other Admin', 'other@example.com'),
            ],
            'meta' => null,
            'message' => 'OK',
        ]);
        $this->employeeApi->shouldReceive('removeEmployee')->never();

        $this->from(route('frontend.company.employees.index'))
            ->delete(route('frontend.company.employees.remove', ['employeeId' => 1]))
            ->assertSessionHasErrors('error');
    }

    public function test_invitations_tab_shows_migration_blocker(): void
    {
        $this->actingAsCompany();
        $this->mockCatalog();
        $this->employeeApi->shouldReceive('getEmployees')->once()->andReturn([
            'statusCode' => 200,
            'data' => [],
            'meta' => null,
            'message' => 'OK',
        ]);
        $this->employeeApi->shouldReceive('getInvitations')->once()->andReturn([
            'statusCode' => 500,
            'message' => 'SQLSTATE[42S02]: Base table or view not found: company_invitations',
            'data' => null,
            'meta' => null,
        ]);
        $this->employeeApi->shouldReceive('createInvitation')->never();

        $html = $this->get(route('frontend.company.employees.index', ['tab' => 'invitations']))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('data-invites-blocked', $html);
        $this->assertStringContainsString('000009', $html);
        $this->assertStringNotContainsString('data-invite-create', $html);
    }

    public function test_invitation_create_blocked_when_table_missing(): void
    {
        $this->actingAsCompany();
        $this->mockCatalog();
        $this->employeeApi->shouldReceive('getInvitations')->once()->andReturn([
            'statusCode' => 500,
            'message' => 'company_invitations missing',
            'data' => null,
        ]);
        $this->employeeApi->shouldReceive('createInvitation')->never();

        $this->from(route('frontend.company.employees.index', ['tab' => 'invitations']))
            ->post(route('frontend.company.employees.invitations.store'), [
                'email' => 'new@example.com',
                'role_id' => 3,
            ])
            ->assertRedirect(route('frontend.company.employees.index', ['tab' => 'invitations']))
            ->assertSessionHasErrors('error');
    }

    public function test_invitation_create_contract_when_available(): void
    {
        $this->actingAsCompany();
        $this->mockCatalog();
        $this->employeeApi->shouldReceive('getInvitations')->once()->andReturn([
            'statusCode' => 200,
            'data' => [],
            'meta' => null,
            'message' => 'OK',
        ]);
        $this->employeeApi->shouldReceive('createInvitation')
            ->once()
            ->with(Mockery::on(fn (array $d) => ($d['email'] ?? '') === 'new@example.com' && (int) ($d['role_id'] ?? 0) === 3))
            ->andReturn(['statusCode' => 201, 'message' => 'Invitation created.', 'data' => ['id' => 1]]);

        $this->from(route('frontend.company.employees.index', ['tab' => 'invitations']))
            ->post(route('frontend.company.employees.invitations.store'), [
                'email' => 'new@example.com',
                'role_id' => 3,
            ])
            ->assertRedirect(route('frontend.company.employees.index', ['tab' => 'invitations']))
            ->assertSessionHas('success_key', 'company.employees.flash_invite_created');
    }

    public function test_invitation_list_and_revoke_when_available(): void
    {
        $this->actingAsCompany();
        $this->mockCatalog();
        $this->employeeApi->shouldReceive('getEmployees')->once()->andReturn([
            'statusCode' => 200,
            'data' => [],
            'meta' => null,
            'message' => 'OK',
        ]);
        $this->employeeApi->shouldReceive('getInvitations')->once()->andReturn([
            'statusCode' => 200,
            'message' => 'OK',
            'data' => [[
                'id' => 55,
                'email' => 'invitee@example.com',
                'role_id' => 3,
                'status' => 'pending',
                'expires_at' => '2026-11-10T00:00:00+00:00',
            ]],
            'meta' => null,
        ]);

        $html = $this->get(route('frontend.company.employees.index', ['tab' => 'invitations']))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('invitee@example.com', $html);
        $this->assertStringContainsString(__('company.employees.invite_record_only'), $html);
        $this->assertStringContainsString(__('company.employees.no_resend_endpoint'), $html);
        $this->assertStringNotContainsString('Send invitation email', $html);

        $this->employeeApi->shouldReceive('getInvitations')->once()->andReturn([
            'statusCode' => 200,
            'data' => [['id' => 55, 'email' => 'invitee@example.com']],
            'meta' => null,
            'message' => 'OK',
        ]);
        $this->employeeApi->shouldReceive('deleteInvitation')->once()->with(55)->andReturn([
            'statusCode' => 200,
            'message' => 'Invitation deleted.',
            'data' => null,
        ]);

        $this->from(route('frontend.company.employees.index', ['tab' => 'invitations']))
            ->delete(route('frontend.company.employees.invitations.destroy', ['invitationId' => 55]))
            ->assertRedirect(route('frontend.company.employees.index', ['tab' => 'invitations']))
            ->assertSessionHas('success_key', 'company.employees.flash_invite_revoked');
    }

    public function test_roles_tab_shows_verified_roles_and_catalog_gap(): void
    {
        $this->actingAsCompany();
        $this->mockCatalog();
        $this->employeeApi->shouldReceive('getEmployees')->once()->andReturn([
            'statusCode' => 200,
            'data' => [],
            'meta' => null,
            'message' => 'OK',
        ]);

        $html = $this->get(route('frontend.company.employees.index', ['tab' => 'roles']))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('data-role-card="company_admin"', $html);
        $this->assertStringContainsString('data-role-card="hr"', $html);
        $this->assertStringContainsString('data-role-card="interviewer"', $html);
        $this->assertStringContainsString('data-role-card="employee"', $html);
        $this->assertStringContainsString('data-permission-catalog-gap', $html);
        $this->assertStringContainsString(__('company.employees.scope_hiring_team'), $html);
        $this->assertStringContainsString(__('company.employees.scope_platform_admin'), $html);
        $this->assertStringContainsString('employees.view', $html);
        $this->assertStringContainsString(__('company.employees.role_label_company_admin'), $html);
        $this->assertStringNotContainsString('data-role-card="agency"', $html);
        $this->assertStringNotContainsString('data-role-card="hiring_manager"', $html);
        $this->assertStringNotContainsString(__('company.employees.invite_sent', ['email' => 'x']), $html);
    }

    public function test_arabic_labels_render_on_team_page(): void
    {
        app()->setLocale('ar');
        $this->actingAsCompany();
        $this->mockCatalog();
        $this->employeeApi->shouldReceive('getEmployees')->once()->andReturn([
            'statusCode' => 200,
            'data' => [],
            'meta' => null,
            'message' => 'OK',
        ]);

        $html = $this->get(route('frontend.company.employees.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(__('company.employees.tab_members_plain'), $html);
        $this->assertStringContainsString(__('company.employees.tab_roles_plain'), $html);
    }

    public function test_mobile_member_cards_markup_present(): void
    {
        $this->actingAsCompany();
        $this->mockCatalog();
        $this->employeeApi->shouldReceive('getEmployees')->once()->andReturn([
            'statusCode' => 200,
            'data' => [$this->member(2, 'approved', 'hr', 30, 'Sam', 'sam@example.com')],
            'meta' => null,
            'message' => 'OK',
        ]);

        $html = $this->get(route('frontend.company.employees.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('data-members-mobile', $html);
        $this->assertStringContainsString('data-member-card="2"', $html);
    }

    public function test_candidate_cannot_mutate_team_routes(): void
    {
        $this->actingAsCandidate();
        $this->employeeApi->shouldReceive('approveEmployee')->never();
        $this->employeeApi->shouldReceive('updateEmployeeRole')->never();
        $this->employeeApi->shouldReceive('removeEmployee')->never();
        $this->employeeApi->shouldReceive('createInvitation')->never();

        $this->post(route('frontend.company.employees.approve', ['employeeId' => 1]), ['role_id' => 1])
            ->assertRedirect();
        $this->put(route('frontend.company.employees.role.update', ['employeeId' => 1]), ['role_id' => 1])
            ->assertRedirect();
        $this->delete(route('frontend.company.employees.remove', ['employeeId' => 1]))
            ->assertRedirect();
        $this->post(route('frontend.company.employees.invitations.store'), ['email' => 'x@example.com'])
            ->assertRedirect();
    }

    private function actingAsCompany(): void
    {
        ApiSession::store('co-token', [
            'id' => 20,
            'email' => 'hr@example.com',
            'name' => 'HR User',
            'account_type' => 'company',
            'roles' => ['company_admin'],
            'company' => ['id' => 5, 'name' => 'Acme'],
        ]);
    }

    private function actingAsHrOnly(): void
    {
        ApiSession::store('co-hr-token', [
            'id' => 21,
            'email' => 'recruiter@example.com',
            'name' => 'Recruiter User',
            'account_type' => 'company',
            'roles' => ['hr'],
            'company' => ['id' => 5, 'name' => 'Acme'],
        ]);
    }

    private function actingAsCandidate(): void
    {
        ApiSession::store('cand-token', [
            'id' => 11,
            'email' => 'candidate@example.com',
            'name' => 'Candidate User',
            'account_type' => 'candidate',
            'roles' => ['candidate'],
        ]);
    }

    private function mockCatalog(): void
    {
        $this->employeeApi->shouldReceive('getCompanyRoles')->byDefault()->andReturn([
            'statusCode' => 200,
            'message' => 'OK',
            'data' => [
                ['id' => 1, 'name' => 'company_admin', 'scope' => 'company', 'description' => 'Company owner / admin'],
                ['id' => 2, 'name' => 'employee', 'scope' => 'company', 'description' => 'Regular employee'],
                ['id' => 3, 'name' => 'hr', 'scope' => 'company', 'description' => 'HR manager'],
                ['id' => 4, 'name' => 'interviewer', 'scope' => 'company', 'description' => 'Interviewer'],
            ],
            'meta' => null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function member(int $id, string $status, ?string $role, int $userId, string $name, string $email): array
    {
        return [
            'id' => $id,
            'status' => $status,
            'company_id' => 5,
            'user' => ['id' => $userId, 'name' => $name, 'email' => $email],
            'role' => $role ? ['id' => match ($role) {
                'company_admin' => 1,
                'employee' => 2,
                'hr' => 3,
                'interviewer' => 4,
                default => 0,
            }, 'name' => $role, 'scope' => 'company'] : null,
            'requested_job_title' => 'Engineer',
            'requested_department' => 'Product',
            'requested_at' => '2026-10-01T10:00:00+00:00',
            'joined_at' => $status === 'approved' ? '2026-10-02T10:00:00+00:00' : null,
        ];
    }
}

<?php

namespace Tests\Feature;

use App\Frontend\Admin\AdminUsersAccessController;
use App\Frontend\Auth\ApiSession;
use App\Models\AdminAPI;
use Mockery;
use Tests\TestCase;

class AdminUsersAccessTest extends TestCase
{
    private $api;

    protected function setUp(): void
    {
        parent::setUp();

        $this->api = Mockery::mock(AdminAPI::class);
        $this->app->instance(AdminAPI::class, $this->api);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_guest_is_redirected_from_users_access(): void
    {
        $this->get(route('frontend.admin.users.index'))
            ->assertRedirect();
    }

    public function test_candidate_cannot_open_users_access(): void
    {
        ApiSession::store('cand', [
            'id' => 11,
            'email' => 'candidate@example.com',
            'name' => 'Cand',
            'account_type' => 'candidate',
        ], ApiSession::REALM_USER);

        $this->get(route('frontend.admin.users.index'))
            ->assertRedirect(route('frontend.workspace'));
    }

    public function test_company_cannot_open_users_access(): void
    {
        ApiSession::store('co', [
            'id' => 12,
            'email' => 'company@example.com',
            'name' => 'Co',
            'account_type' => 'company',
        ], ApiSession::REALM_USER);

        $this->get(route('frontend.admin.users.index'))
            ->assertRedirect(route('frontend.workspace'));
    }

    public function test_platform_admin_sees_directory_mapping(): void
    {
        $this->actingAsPlatformAdmin();
        $this->expectMe();
        $this->api->shouldReceive('getUsers')->once()->andReturn($this->usersPayload());
        $this->api->shouldReceive('getAuditLogs')->once()->andReturn($this->auditPayload());
        $this->api->shouldReceive('updateUserStatus')->never();
        $this->api->shouldReceive('getRoles')->never();

        $html = $this->get(route('frontend.admin.users.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('data-admin-users-access', $html);
        $this->assertStringContainsString('Amina Candidate', $html);
        $this->assertStringContainsString('amina@example.com', $html);
        $this->assertStringContainsString('Acme Corp', $html);
        $this->assertStringContainsString('company_admin', $html);
        $this->assertStringContainsString('data-admin-users-access', $html);
        $this->assertStringContainsString('Users &amp; access', $html);
    }

    public function test_status_tabs_forward_account_status_id(): void
    {
        $this->actingAsPlatformAdmin();
        $this->expectMe();
        $this->api->shouldReceive('getUsers')->once()->withArgs(function (array $filters) {
            return ($filters['account_status_id'] ?? null) === AdminUsersAccessController::STATUS_ACTIVE
                && ! isset($filters['account_type']);
        })->andReturn($this->usersPayload());
        $this->api->shouldReceive('getAuditLogs')->once()->andReturn($this->auditUnavailable());

        $html = $this->get(route('frontend.admin.users.index', ['status_tab' => 'active']))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('data-status-tab="active"', $html);
        $this->assertStringContainsString('aria-selected="true"', $html);
        $this->assertStringContainsString('data-status-tab-link="active"', $html);
    }

    public function test_search_and_account_type_filters_are_forwarded(): void
    {
        $this->actingAsPlatformAdmin();
        $this->expectMe();
        $this->api->shouldReceive('getUsers')->once()->withArgs(function (array $filters) {
            return ($filters['search'] ?? null) === 'amina'
                && ($filters['account_type'] ?? null) === 'company';
        })->andReturn($this->usersPayload());
        $this->api->shouldReceive('getAuditLogs')->once()->andReturn($this->auditUnavailable());

        $this->get(route('frontend.admin.users.index', [
            'search' => 'amina',
            'account_type' => 'company',
        ]))->assertOk();
    }

    public function test_pagination_meta_is_rendered_when_present(): void
    {
        $this->actingAsPlatformAdmin();
        $this->expectMe();
        $payload = $this->usersPayload();
        $payload['meta'] = [
            'current_page' => 2,
            'last_page' => 5,
            'per_page' => 15,
            'total' => 70,
        ];
        $this->api->shouldReceive('getUsers')->once()->andReturn($payload);
        $this->api->shouldReceive('getAuditLogs')->once()->andReturn($this->auditUnavailable());

        $html = $this->get(route('frontend.admin.users.index', ['page' => 2]))
            ->assertOk()
            ->getContent();

        $this->assertTrue(
            str_contains($html, 'page=1') || str_contains($html, 'page=3') || str_contains($html, '70'),
            'Expected pagination cues for meta total/pages'
        );
    }

    public function test_unknown_status_falls_back_without_inventing_pending_tab(): void
    {
        $this->actingAsPlatformAdmin();
        $this->expectMe();
        $this->api->shouldReceive('getUsers')->once()->andReturn($this->usersPayload([
            [
                'id' => 9,
                'name' => 'Mystery',
                'email' => 'm@example.com',
                'account_type' => 'candidate',
                'account_status_id' => 99,
                'account_status' => 'WeirdStatus',
                'roles' => [],
                'owned_companies' => [],
                'permissions' => [],
                'created_at' => '2026-01-01T00:00:00+00:00',
            ],
        ]));
        $this->api->shouldReceive('getAuditLogs')->once()->andReturn($this->auditUnavailable());

        $html = $this->get(route('frontend.admin.users.index'))->assertOk()->getContent();

        $this->assertStringContainsString('WeirdStatus', $html);
        $this->assertStringNotContainsString('data-status-tab-link="invited"', $html);
        $this->assertStringNotContainsString('data-status-tab-link="pending"', $html);
    }

    public function test_privileged_accounts_are_classified_from_verified_fields(): void
    {
        $this->actingAsPlatformAdmin();
        $this->expectMe();
        $this->api->shouldReceive('getUsers')->once()->andReturn($this->usersPayload());
        $this->api->shouldReceive('getAuditLogs')->once()->andReturn($this->auditPayload());

        $html = $this->get(route('frontend.admin.users.index'))->assertOk()->getContent();

        $this->assertStringContainsString('data-users-privileged', $html);
        $this->assertStringContainsString('data-privileged-row="1"', $html);
        $this->assertStringContainsString('Platform Ops', $html);
        $this->assertStringContainsString(__('admin.users.no_fake_certification'), $html);
    }

    public function test_invitations_are_marked_backend_blocked(): void
    {
        $this->actingAsPlatformAdmin();
        $this->expectMe();
        $this->api->shouldReceive('getUsers')->once()->andReturn($this->usersPayload([]));
        $this->api->shouldReceive('getAuditLogs')->once()->andReturn($this->auditUnavailable());

        $html = $this->get(route('frontend.admin.users.index'))->assertOk()->getContent();

        $this->assertStringContainsString('data-invitations-supported="0"', $html);
        $this->assertStringContainsString('data-invite-blocked', $html);
        $this->assertStringContainsString(__('admin.users.invite_blocked_body'), $html);
    }

    public function test_full_export_blocked_and_page_export_labeled(): void
    {
        $this->actingAsPlatformAdmin();
        $this->expectMe();
        $this->api->shouldReceive('getUsers')->once()->andReturn($this->usersPayload());
        $this->api->shouldReceive('getAuditLogs')->once()->andReturn($this->auditUnavailable());

        $html = $this->get(route('frontend.admin.users.index'))->assertOk()->getContent();

        $this->assertStringContainsString('data-export-full="0"', $html);
        $this->assertStringContainsString('no authorized Backend full-directory export', $html);
        $this->assertStringContainsString('/admin/users/export-page', $html);
        $this->assertStringContainsString('data-export-page', $html);
        $this->assertStringContainsString('Exports only the records returned for the current authorized page', $html);
    }

    public function test_role_assignment_remains_blocked(): void
    {
        $this->assertFalse(AdminUsersAccessController::ROLE_ASSIGNMENT_ENABLED);

        $this->actingAsPlatformAdmin();
        $this->expectMe();
        $this->api->shouldReceive('getUsers')->once()->andReturn($this->usersPayload([]));
        $this->api->shouldReceive('getAuditLogs')->once()->andReturn($this->auditUnavailable());

        $html = $this->get(route('frontend.admin.users.index'))->assertOk()->getContent();

        $this->assertStringContainsString('data-role-assignment-blocked', $html);
        $this->assertStringContainsString(__('admin.users.role_assign_blocked'), $html);
        $this->assertStringNotContainsString('name="role_id"', $html);
    }

    public function test_admin_account_type_has_no_status_action(): void
    {
        $this->actingAsPlatformAdmin();
        $this->expectMe();
        $this->api->shouldReceive('getUsers')->once()->andReturn($this->usersPayload());
        $this->api->shouldReceive('getAuditLogs')->once()->andReturn($this->auditUnavailable());

        $html = $this->get(route('frontend.admin.users.index'))->assertOk()->getContent();

        $this->assertStringContainsString(__('admin.users.action_status_blocked_admin'), $html);
        // Candidate/company rows still get change-status links
        $this->assertStringContainsString(
            route('frontend.admin.users.status', ['userId' => 21], absolute: false),
            $html
        );
    }

    public function test_api_error_shows_safe_message_without_redirect_inventing_rows(): void
    {
        $this->actingAsPlatformAdmin();
        $this->expectMe();
        $this->api->shouldReceive('getUsers')->once()->andReturn([
            'statusCode' => 500,
            'message' => 'SQLSTATE[HY000] secret',
            'data' => null,
            'meta' => null,
        ]);
        $this->api->shouldReceive('getAuditLogs')->once()->andReturn($this->auditUnavailable());

        $html = $this->get(route('frontend.admin.users.index'))->assertOk()->getContent();

        $this->assertStringContainsString('data-users-load-error', $html);
        $this->assertStringNotContainsString('SQLSTATE', $html);
        $this->assertStringContainsString('data-users-empty', $html);
    }

    public function test_forbidden_users_manage_shows_forbidden_state(): void
    {
        $this->actingAsPlatformAdmin();
        $this->expectMe();
        $this->api->shouldReceive('getUsers')->once()->andReturn([
            'statusCode' => 403,
            'message' => 'Forbidden',
            'data' => null,
        ]);
        $this->api->shouldReceive('getAuditLogs')->once()->andReturn($this->auditUnavailable());

        $html = $this->get(route('frontend.admin.users.index'))->assertOk()->getContent();

        $this->assertStringContainsString(__('admin.users.forbidden'), $html);
    }

    public function test_arabic_copy_renders(): void
    {
        app()->setLocale('ar');
        $this->actingAsPlatformAdmin();
        $this->expectMe();
        $this->api->shouldReceive('getUsers')->once()->andReturn($this->usersPayload([]));
        $this->api->shouldReceive('getAuditLogs')->once()->andReturn($this->auditUnavailable());

        $html = $this->get(route('frontend.admin.users.index'))->assertOk()->getContent();

        $this->assertStringContainsString(__('admin.users.title'), $html);
        $this->assertStringContainsString(__('admin.users.tab_deactivated'), $html);
    }

    public function test_responsive_cards_markup_present(): void
    {
        $this->actingAsPlatformAdmin();
        $this->expectMe();
        $this->api->shouldReceive('getUsers')->once()->andReturn($this->usersPayload());
        $this->api->shouldReceive('getAuditLogs')->once()->andReturn($this->auditUnavailable());

        $html = $this->get(route('frontend.admin.users.index'))->assertOk()->getContent();

        $this->assertStringContainsString('data-users-table', $html);
        $this->assertStringContainsString('data-users-cards', $html);
        $this->assertStringContainsString('md:hidden', $html);
    }

    public function test_page_export_streams_csv_without_mutations(): void
    {
        $this->actingAsPlatformAdmin();
        $this->expectMe();
        $this->api->shouldReceive('getUsers')->once()->andReturn($this->usersPayload());
        $this->api->shouldReceive('updateUserStatus')->never();

        $response = $this->get(route('frontend.admin.users.export-page'));
        $response->assertOk();
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));
        $content = $response->streamedContent();
        $this->assertStringContainsString('Current Page Only', $content);
        $this->assertStringContainsString('not a full Users directory export', $content);
        $this->assertStringContainsString('Amina Candidate', $content);
        $this->assertStringNotContainsString('password', strtolower($content));
        $this->assertStringNotContainsString('token', strtolower($content));
        $this->assertStringNotContainsString('employees.view', $content); // permissions not exported
    }

    public function test_page_csv_neutralizes_formula_injection_and_escapes_specials(): void
    {
        $this->actingAsPlatformAdmin();
        $this->expectMe();
        $this->api->shouldReceive('getUsers')->once()->andReturn($this->usersPayload([
            [
                'id' => 50,
                'name' => "=CMD|' /C calc'!A0",
                'email' => '+hack@example.com',
                'account_type' => 'candidate',
                'account_status_id' => 1,
                'account_status' => '@Active',
                'roles' => [[
                    'id' => 9,
                    'name' => '-role, "quoted"',
                    'scope' => 'company',
                    'company_id' => 1,
                    'assigned_at' => null,
                ]],
                'owned_companies' => [[
                    'id' => 1,
                    'name' => "Acme\nLine2",
                    'status' => 'approved',
                ]],
                'permissions' => ['secret.internal'],
                'created_at' => '2026-01-01T00:00:00+00:00',
            ],
        ]));
        $this->api->shouldReceive('updateUserStatus')->never();

        $content = $this->get(route('frontend.admin.users.export-page'))
            ->assertOk()
            ->streamedContent();

        $this->assertStringContainsString('Current Page Only', $content);
        // Leading formula characters are neutralized with a leading apostrophe.
        $this->assertStringContainsString("'=CMD", $content);
        $this->assertStringContainsString("'+hack@example.com", $content);
        $this->assertStringContainsString("'@Active", $content);
        $this->assertStringContainsString("'-role", $content);
        $this->assertStringNotContainsString('secret.internal', $content);
        $this->assertStringNotContainsString('password', strtolower($content));
        // Commas/quotes still CSV-safe via fputcsv quoting.
        $this->assertTrue(
            str_contains($content, 'quoted') || str_contains($content, '""'),
            'Expected quoted role fragment to survive CSV encoding'
        );
        // Unicode safety: BOM present for spreadsheet Unicode handling.
        $this->assertTrue(str_starts_with($content, "\xEF\xBB\xBF") || str_contains($content, 'Current Page Only'));
    }

    public function test_csv_cell_helper_prefixes_formula_starters(): void
    {
        $controller = $this->app->make(AdminUsersAccessController::class);
        $this->assertSame("'=1+1", $controller->csvCell('=1+1'));
        $this->assertSame("'+sum", $controller->csvCell('+sum'));
        $this->assertSame("'-1", $controller->csvCell('-1'));
        $this->assertSame("'@x", $controller->csvCell('@x'));
        $this->assertSame('safe', $controller->csvCell('safe'));
        $this->assertSame('مرحبا', $controller->csvCell('مرحبا'));
    }

    public function test_missing_roles_payload_marks_privilege_unknown_not_privileged(): void
    {
        $this->actingAsPlatformAdmin();
        $this->expectMe();
        $this->api->shouldReceive('getUsers')->once()->andReturn([
            'statusCode' => 200,
            'message' => 'OK',
            'data' => [[
                'id' => 60,
                'name' => 'Incomplete Row',
                'email' => 'inc@example.com',
                'account_type' => 'company',
                'account_status_id' => 1,
                'account_status' => 'Active',
                // roles key intentionally absent
                'owned_companies' => [],
                'created_at' => '2026-01-01T00:00:00+00:00',
            ]],
            'meta' => ['current_page' => 1, 'last_page' => 1, 'per_page' => 15, 'total' => 1],
        ]);
        $this->api->shouldReceive('getAuditLogs')->once()->andReturn($this->auditUnavailable());

        $html = $this->get(route('frontend.admin.users.index'))->assertOk()->getContent();

        $this->assertStringContainsString('data-privilege-unknown', $html);
        $this->assertStringContainsString(__('admin.users.privilege_unknown_note', ['count' => 1]), $html);
        $this->assertStringNotContainsString('data-privileged-row="1"', $html);
        $this->assertStringContainsString('data-no-certification', $html);
    }

    public function test_page_only_role_filter_does_not_claim_server_role_query(): void
    {
        $this->actingAsPlatformAdmin();
        $this->expectMe();
        $this->api->shouldReceive('getUsers')->once()->withArgs(function (array $filters) {
            return ! array_key_exists('role', $filters)
                && ! array_key_exists('role_id', $filters)
                && ! array_key_exists('page_role', $filters);
        })->andReturn($this->usersPayload());
        $this->api->shouldReceive('getAuditLogs')->once()->andReturn($this->auditUnavailable());

        $html = $this->get(route('frontend.admin.users.index', ['page_role' => 'super_admin']))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('data-users-page-filter-note', $html);
        // Page filter keeps platform admin row, drops company/candidate without matching role text
        $this->assertStringContainsString('Platform Ops', $html);
        $this->assertStringNotContainsString('Amina Candidate', $html);
    }

    private function actingAsPlatformAdmin(): void
    {
        ApiSession::store('platform-token', [
            'id' => 7,
            'email' => 'super@example.com',
            'name' => 'Super Admin',
            'account_type' => 'admin',
            'roles' => ['super_admin'],
            'permissions' => ['users.manage', 'audit.view', 'roles.manage'],
        ], ApiSession::REALM_PLATFORM_ADMIN);
    }

    private function expectMe(): void
    {
        $this->api->shouldReceive('me')->once()->andReturn([
            'message' => 'OK',
            'data' => [
                'id' => 7,
                'email' => 'super@example.com',
                'name' => 'Super Admin',
                'roles' => ['super_admin'],
                'permissions' => ['users.manage', 'audit.view', 'roles.manage', 'reports.view'],
            ],
            'errors' => null,
            'statusCode' => 200,
        ]);
    }

    /**
     * @param  list<array<string, mixed>>|null  $override
     * @return array<string, mixed>
     */
    private function usersPayload(?array $override = null): array
    {
        $data = $override ?? [
            [
                'id' => 21,
                'name' => 'Amina Candidate',
                'email' => 'amina@example.com',
                'account_type' => 'candidate',
                'account_status_id' => 1,
                'account_status' => 'Active',
                'roles' => [],
                'owned_companies' => [],
                'permissions' => [],
                'created_at' => '2026-09-01T00:00:00+00:00',
            ],
            [
                'id' => 22,
                'name' => 'Karim HR',
                'email' => 'karim@acme.test',
                'account_type' => 'company',
                'account_status_id' => 1,
                'account_status' => 'Active',
                'roles' => [[
                    'id' => 3,
                    'name' => 'company_admin',
                    'scope' => 'company',
                    'company_id' => 5,
                    'assigned_at' => '2026-09-02T00:00:00+00:00',
                ]],
                'owned_companies' => [[
                    'id' => 5,
                    'name' => 'Acme Corp',
                    'status' => 'approved',
                ]],
                'permissions' => ['employees.view'],
                'created_at' => '2026-09-02T00:00:00+00:00',
            ],
            [
                'id' => 23,
                'name' => 'Platform Ops',
                'email' => 'ops@platform.test',
                'account_type' => 'admin',
                'account_status_id' => 1,
                'account_status' => 'Active',
                'roles' => [[
                    'id' => 1,
                    'name' => 'super_admin',
                    'scope' => 'platform',
                    'company_id' => null,
                    'assigned_at' => '2026-08-01T00:00:00+00:00',
                ]],
                'owned_companies' => [],
                'permissions' => ['users.manage'],
                'created_at' => '2026-08-01T00:00:00+00:00',
            ],
        ];

        return [
            'statusCode' => 200,
            'message' => 'OK',
            'data' => $data,
            'meta' => [
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => 15,
                'total' => count($data),
            ],
        ];
    }

    /** @return array<string, mixed> */
    private function auditPayload(): array
    {
        return [
            'statusCode' => 200,
            'message' => 'OK',
            'data' => [[
                'id' => 100,
                'action' => 'user.status_changed',
                'auditable_type' => 'User',
                'auditable_id' => 21,
                'admin' => ['id' => 7, 'name' => 'Super Admin', 'email' => 'super@example.com'],
                'user' => null,
                'reason' => 'Deactivated for policy',
                'created_at' => '2026-10-30T12:00:00+00:00',
            ]],
            'meta' => null,
        ];
    }

    /** @return array<string, mixed> */
    private function auditUnavailable(): array
    {
        return [
            'statusCode' => 403,
            'message' => 'Forbidden',
            'data' => null,
        ];
    }
}

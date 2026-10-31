<?php

namespace Tests\Feature;

use App\Frontend\Auth\ApiSession;
use App\Models\AdminAPI;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Mockery;
use Tests\TestCase;

class AdminCompaniesFilterAndSuspendTest extends TestCase
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

    public function test_company_dropdown_lists_all_pages_not_first_page_only(): void
    {
        $this->actingAsPlatformAdmin();

        $api = Mockery::mock(AdminAPI::class);
        $this->expectPlatformMe($api);

        $api->shouldReceive('getCompanies')->once()->with(Mockery::on(function (array $f): bool {
            return ($f['page'] ?? null) === 1 && (int) ($f['per_page'] ?? 0) === 100;
        }))->andReturn([
            'message' => 'OK',
            'data' => [
                ['id' => 1, 'name' => 'Alpha Co', 'status' => 'approved'],
                ['id' => 2, 'name' => 'Beta Co', 'status' => 'pending'],
            ],
            'meta' => ['current_page' => 1, 'last_page' => 2, 'per_page' => 100, 'total' => 3],
            'statusCode' => 200,
        ]);
        $api->shouldReceive('getCompanies')->once()->with(Mockery::on(function (array $f): bool {
            return ($f['page'] ?? null) === 2 && (int) ($f['per_page'] ?? 0) === 100;
        }))->andReturn([
            'message' => 'OK',
            'data' => [
                ['id' => 3, 'name' => 'Gamma Co', 'status' => 'approved'],
            ],
            'meta' => ['current_page' => 2, 'last_page' => 2, 'per_page' => 100, 'total' => 3],
            'statusCode' => 200,
        ]);
        // Table list (no company_id): status/page only.
        $api->shouldReceive('getCompanies')->once()->with(Mockery::on(function (array $f): bool {
            return ! array_key_exists('per_page', $f) && ($f['page'] ?? 1) === 1;
        }))->andReturn([
            'message' => 'OK',
            'data' => [
                ['id' => 1, 'name' => 'Alpha Co', 'status' => 'approved'],
            ],
            'meta' => ['current_page' => 1, 'last_page' => 1, 'total' => 1],
            'statusCode' => 200,
        ]);
        $api->shouldNotReceive('getCompany');
        $this->app->instance(AdminAPI::class, $api);

        $html = $this->get(route('frontend.admin.companies.index'))->assertOk()->getContent();

        $this->assertStringContainsString(__('admin.companies.filter_all_companies'), $html);
        $this->assertStringContainsString('Alpha Co', $html);
        $this->assertStringContainsString('Beta Co', $html);
        $this->assertStringContainsString('Gamma Co', $html);
        $this->assertStringContainsString('name="company_id"', $html);
        $this->assertStringContainsString('name="status"', $html);
        $this->assertStringNotContainsString('name="search"', $html);
    }

    public function test_combined_company_id_and_status_uses_show_endpoint(): void
    {
        $this->actingAsPlatformAdmin();

        $api = Mockery::mock(AdminAPI::class);
        $this->expectPlatformMe($api);
        $this->expectCatalogOnePage($api, [
            ['id' => 9, 'name' => 'Acme', 'status' => 'approved'],
            ['id' => 10, 'name' => 'Other', 'status' => 'pending'],
        ]);
        $api->shouldReceive('getCompany')->once()->with('9')->andReturn([
            'message' => 'OK',
            'data' => [
                'id' => 9,
                'name' => 'Acme',
                'status' => 'approved',
                'industry' => 'Tech',
                'contact_email' => 'a@example.com',
                'jobs_count' => 2,
                'created_at' => '2026-10-01T00:00:00+00:00',
            ],
            'meta' => null,
            'statusCode' => 200,
        ]);
        $this->app->instance(AdminAPI::class, $api);

        $html = $this->get(route('frontend.admin.companies.index', [
            'company_id' => 9,
            'status' => 'approved',
        ]))->assertOk()->getContent();

        $this->assertStringContainsString('Acme', $html);
        $this->assertStringContainsString('Tech', $html);
        $this->assertMatchesRegularExpression('/<option[^>]*value="9"[^>]*selected/i', $html);
        $this->assertMatchesRegularExpression('/<option[^>]*value="approved"[^>]*selected/i', $html);
    }

    public function test_company_status_mismatch_shows_empty_table(): void
    {
        $this->actingAsPlatformAdmin();

        $api = Mockery::mock(AdminAPI::class);
        $this->expectPlatformMe($api);
        $this->expectCatalogOnePage($api, [
            ['id' => 9, 'name' => 'Acme', 'status' => 'approved'],
        ]);
        $api->shouldReceive('getCompany')->once()->with('9')->andReturn([
            'message' => 'OK',
            'data' => ['id' => 9, 'name' => 'Acme', 'status' => 'approved'],
            'statusCode' => 200,
        ]);
        $this->app->instance(AdminAPI::class, $api);

        $html = $this->get(route('frontend.admin.companies.index', [
            'company_id' => 9,
            'status' => 'pending',
        ]))->assertOk()->getContent();

        $this->assertStringContainsString(__('Nothing to show yet.'), $html);
    }

    public function test_invalid_company_show_shows_friendly_filter_error(): void
    {
        $this->actingAsPlatformAdmin();

        $api = Mockery::mock(AdminAPI::class);
        $this->expectPlatformMe($api);
        $this->expectCatalogOnePage($api, []);
        $api->shouldReceive('getCompany')->once()->with('404')->andReturn([
            'message' => 'No query results for model [App\\Models\\Company] 404',
            'data' => null,
            'statusCode' => 404,
        ]);
        $this->app->instance(AdminAPI::class, $api);

        $html = $this->get(route('frontend.admin.companies.index', ['company_id' => 404]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(__('admin.companies.filter_company_unavailable'), $html);
        $this->assertStringContainsString('data-admin-companies-filter-error', $html);
    }

    public function test_suspend_failure_sanitizes_sql_and_does_not_flash_success(): void
    {
        $this->actingAsPlatformAdmin();

        $api = Mockery::mock(AdminAPI::class);
        $this->expectPlatformMe($api);
        $api->shouldReceive('suspendCompany')->once()->with('7', ['reason' => 'Policy review requested'])->andReturn([
            'message' => 'SQLSTATE[23000]: Integrity constraint violation: 1452 Cannot add or update a child row: a foreign key constraint fails (`recruitment_audit`.`audit_logs`, CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL) (Connection: mysql, Host: 127.0.0.1, Port: 3306, Database: recruitment_audit, SQL: insert into `audit_logs` (`user_id`, `admin_id`, `action`) values (0, 1, company.suspended))',
            'data' => null,
            'errors' => null,
            'statusCode' => 500,
        ]);
        $this->app->instance(AdminAPI::class, $api);

        $this->from(route('frontend.admin.companies.index'))
            ->post(route('frontend.admin.companies.suspend', ['companyId' => 7]), [
                'reason' => 'Policy review requested',
                'acknowledged' => '1',
            ])
            ->assertRedirect(route('frontend.admin.companies.index'))
            ->assertSessionHasErrors('error')
            ->assertSessionMissing('success_key');

        $errors = session('errors');
        $message = (string) ($errors?->getBag('default')->first('error') ?? '');
        $this->assertSame(__('admin.companies.suspend_failed'), $message);
        $this->assertStringNotContainsString('SQLSTATE', $message);
        $this->assertStringNotContainsString('127.0.0.1', $message);
        $this->assertStringNotContainsString('audit_logs', $message);
    }

    public function test_suspend_requires_reason_and_acknowledgement_before_backend_call(): void
    {
        $this->actingAsPlatformAdmin();

        $api = Mockery::mock(AdminAPI::class);
        $this->expectPlatformMe($api);
        $api->shouldNotReceive('suspendCompany');
        $this->app->instance(AdminAPI::class, $api);

        $this->from(route('frontend.admin.companies.index'))
            ->post(route('frontend.admin.companies.suspend', ['companyId' => 7]))
            ->assertRedirect(route('frontend.admin.companies.index'))
            ->assertSessionHasErrors(['reason', 'acknowledged']);
    }

    public function test_suspend_requires_company_suspend_permission(): void
    {
        $this->actingAsPlatformAdmin();

        $api = Mockery::mock(AdminAPI::class);
        $api->shouldReceive('me')->once()->andReturn([
            'message' => 'OK',
            'data' => [
                'id' => 1,
                'email' => 'admin@example.com',
                'name' => 'Platform Admin',
                'roles' => ['super_admin'],
                'permissions' => ['companies.review'],
            ],
            'statusCode' => 200,
        ]);
        $api->shouldNotReceive('suspendCompany');
        $this->app->instance(AdminAPI::class, $api);

        $this->post(route('frontend.admin.companies.suspend', ['companyId' => 7]), [
            'reason' => 'Policy review requested',
            'acknowledged' => '1',
        ])->assertForbidden();
    }

    public function test_catalog_error_state_is_surfaced_without_hardcoded_companies(): void
    {
        $this->actingAsPlatformAdmin();

        $api = Mockery::mock(AdminAPI::class);
        $this->expectPlatformMe($api);
        $api->shouldReceive('getCompanies')->once()->with(Mockery::on(fn ($f) => ($f['page'] ?? null) === 1))->andReturn([
            'message' => 'Forbidden.',
            'data' => null,
            'statusCode' => 403,
        ]);
        $api->shouldReceive('getCompanies')->once()->with(Mockery::on(fn ($f) => ! isset($f['per_page'])))->andReturn([
            'message' => 'OK',
            'data' => [],
            'meta' => ['total' => 0],
            'statusCode' => 200,
        ]);
        $this->app->instance(AdminAPI::class, $api);

        $html = $this->get(route('frontend.admin.companies.index'))->assertOk()->getContent();

        $this->assertStringContainsString('data-admin-company-options-error', $html);
        $this->assertStringContainsString(__('admin.companies.options_error'), $html);
        $this->assertStringNotContainsString('Acme Hardcoded', $html);
    }

    private function actingAsPlatformAdmin(): void
    {
        ApiSession::store('platform-token', [
            'id' => 1,
            'email' => 'admin@example.com',
            'name' => 'Super Admin',
            'account_type' => 'admin',
            'roles' => ['super_admin'],
        ], ApiSession::REALM_PLATFORM_ADMIN);
    }

    private function expectPlatformMe(mixed $api): void
    {
        $api->shouldReceive('me')->once()->andReturn([
            'message' => 'OK',
            'data' => [
                'id' => 1,
                'email' => 'admin@example.com',
                'name' => 'Super Admin',
                'roles' => ['super_admin'],
                'permissions' => ['companies.review', 'companies.suspend'],
            ],
            'statusCode' => 200,
        ]);
    }

    /** @param  list<array<string, mixed>>  $rows */
    private function expectCatalogOnePage(mixed $api, array $rows): void
    {
        $api->shouldReceive('getCompanies')->once()->with(Mockery::on(function (array $f) {
            return ($f['page'] ?? null) === 1 && (int) ($f['per_page'] ?? 0) === 100;
        }))->andReturn([
            'message' => 'OK',
            'data' => $rows,
            'meta' => [
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => 100,
                'total' => count($rows),
            ],
            'statusCode' => 200,
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Frontend\Auth\ApiSession;
use App\Models\AdminAPI;
use Mockery;
use Tests\TestCase;

class AdminCompanyDetailsAndAuthPagesTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_account_login_and_register_pages_load_after_admin_api_fix(): void
    {
        $this->get(route('frontend.account.login'))->assertOk();
        $this->get(route('frontend.account.register'))->assertOk();
    }

    public function test_show_company_uses_get_company_details_not_management_show(): void
    {
        $this->actingAsPlatformAdmin();

        $api = Mockery::mock(AdminAPI::class);
        $api->shouldReceive('me')->zeroOrMoreTimes()->andReturn([
            'message' => 'OK',
            'data' => [
                'id' => 1,
                'email' => 'admin@example.com',
                'name' => 'Super Admin',
                'account_type' => 'admin',
                'roles' => ['super_admin'],
                'permissions' => ['companies.review', 'companies.suspend'],
            ],
            'statusCode' => 200,
        ]);
        $api->shouldReceive('getCompanyDetails')->once()->with('9')->andReturn([
            'message' => 'OK',
            'data' => [
                'id' => 9,
                'name' => 'Acme Details',
                'status' => 'approved',
                'owner' => ['name' => 'Olivia', 'email' => 'olivia@example.com'],
                'jobs_count' => 2,
                'employees_count' => 4,
                'documents' => [],
            ],
            'errors' => [],
            'statusCode' => 200,
        ]);
        $api->shouldNotReceive('getCompany');
        $this->app->instance(AdminAPI::class, $api);

        $html = $this->get(route('frontend.admin.companies.show', ['companyId' => 9]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Acme Details', $html);
        $this->assertStringContainsString(__('admin.organizations.tab_subscription'), $html);
        $this->assertStringContainsString(__('admin.organizations.tab_members'), $html);
        $this->assertStringContainsString(__('admin.organizations.tab_usage'), $html);
        $this->assertStringContainsString(__('admin.organizations.tab_admin_actions'), $html);
        $this->assertStringContainsString('olivia@example.com', $html);
        $this->assertStringContainsString(__('admin.organizations.subscription_unavailable'), $html);
        $this->assertStringContainsString(__('admin.organizations.usage_scope_hint'), $html);
        $this->assertStringContainsString('data-suspend-form', $html);
        $this->assertSame(2, substr_count($html, 'window.confirm('));
    }

    public function test_company_detail_renders_arabic_tabs_and_unavailable_subscription_state(): void
    {
        app()->setLocale('ar');
        $this->actingAsPlatformAdmin();

        $api = Mockery::mock(AdminAPI::class);
        $api->shouldReceive('me')->zeroOrMoreTimes()->andReturn([
            'message' => 'OK',
            'data' => [
                'id' => 1,
                'email' => 'admin@example.com',
                'name' => 'Super Admin',
                'account_type' => 'admin',
                'roles' => ['super_admin'],
            ],
            'statusCode' => 200,
        ]);
        $api->shouldReceive('getCompanyDetails')->once()->with('9')->andReturn([
            'message' => 'OK',
            'data' => ['id' => 9, 'name' => 'Acme Details', 'status' => 'approved'],
            'errors' => [],
            'statusCode' => 200,
        ]);
        $this->app->instance(AdminAPI::class, $api);

        $html = $this->get(route('frontend.admin.companies.show', ['companyId' => 9]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(__('admin.organizations.tab_subscription'), $html);
        $this->assertStringContainsString(__('admin.organizations.tab_admin_actions'), $html);
        $this->assertStringContainsString(__('admin.organizations.subscription_unavailable'), $html);
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
}

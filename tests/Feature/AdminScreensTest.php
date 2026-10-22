<?php

namespace Tests\Feature;

use App\Support\Frontend\MockAdminData;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AdminScreensTest extends TestCase
{
    public function test_admin_dashboard_renders_frame_forty_four_structure(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Platform operations', false)
            ->assertSee('Priya Shah', false)
            ->assertSee('Operational snapshot', false)
            ->assertSee('Active organizations', false)
            ->assertSee('Published jobs', false)
            ->assertSee('Active engagements', false)
            ->assertSee('Needs review', false)
            ->assertSee('Operational priorities', false)
            ->assertSee('Job policy review', false)
            ->assertSee('Company verification', false)
            ->assertSee('Organization health', false)
            ->assertSee('Northstar Labs', false)
            ->assertSee('Cedar Systems', false)
            ->assertSee('Recent events', false)
            ->assertSee('Security & privacy')
            ->assertSee('No critical alerts', false)
            ->assertSee(route('admin.organizations.index'), false)
            ->assertDontSee('44 /', false)
            ->assertDontSee('Frame 44', false)
            ->assertDontSee('Frontend UI only', false);
    }

    public function test_admin_organizations_renders_frame_forty_five_structure(): void
    {
        $this->get(route('admin.organizations.index'))
            ->assertOk()
            ->assertSee('Organizations', false)
            ->assertSee('Manage company and recruitment-agency workspaces', false)
            ->assertSee('Add organization', false)
            ->assertSee('Active', false)
            ->assertSee('Pending verification', false)
            ->assertSee('Suspended', false)
            ->assertSee('Archived', false)
            ->assertSee('Northstar Labs', false)
            ->assertSee('Hirebridge Talent', false)
            ->assertSee('Olivia Bennett', false)
            ->assertSee('Lifecycle review', false)
            ->assertSee('Cedar Systems', false)
            ->assertSee('Pinecrest Recruiting', false)
            ->assertSee('data-admin-organizations', false)
            ->assertDontSee('45 /', false)
            ->assertDontSee('Frame 45', false);
    }

    public function test_admin_sidebar_activates_implemented_routes_only(): void
    {
        $dashboard = $this->get(route('admin.dashboard'));
        $dashboard->assertOk();
        $dashboard->assertSee(route('admin.dashboard'), false);
        $dashboard->assertSee(route('admin.organizations.index'), false);
        $dashboard->assertSee(route('admin.users.index'), false);
        $dashboard->assertSee(__('navigation.admin.users'));

        $this->assertTrue(Route::has('admin.dashboard'));
        $this->assertTrue(Route::has('admin.organizations.index'));
        $this->assertTrue(Route::has('admin.users.index'));
        $this->assertTrue(Route::has('admin.organizations.show'));

        $users = $this->get(route('admin.users.index'));
        $users->assertOk();
        $users->assertSee(route('admin.jobs.index'), false);
        $users->assertSee(route('admin.agencies.index'), false);
        $users->assertSee(route('admin.settings.index'), false);
        $users->assertSee(__('navigation.admin.settings'));
        $users->assertSee(__('common.soon'), false);
    }

    public function test_admin_organization_mock_data_aligns_with_platform_records(): void
    {
        $northstar = MockAdminData::organization('org-ns-001');
        $this->assertNotNull($northstar);
        $this->assertSame('Northstar Labs', $northstar['name']);
        $this->assertSame('Olivia Bennett', $northstar['owner']);
        $this->assertSame('active', $northstar['tab']);

        $index = MockAdminData::organizationsIndex();
        $this->assertSame(12, $index['tabs'][0]['count']);
        $this->assertSame(2, $index['tabs'][1]['count']);
        $this->assertSame(1, $index['tabs'][2]['count']);
        $this->assertSame(2, $index['tabs'][3]['count']);

        $dashboard = MockAdminData::dashboard();
        $this->assertSame('12', $dashboard['stats'][0]['value']);
        $this->assertSame('5', $dashboard['stats'][3]['value']);
    }

    public function test_admin_organizations_accepts_tab_query_without_frame_labels(): void
    {
        $this->get(route('admin.organizations.index', ['tab' => 'pending', 'q' => 'Cedar']))
            ->assertOk()
            ->assertSee('data-default-tab="pending"', false)
            ->assertSee('data-initial-q="Cedar"', false)
            ->assertDontSee('45 /', false);
    }
}

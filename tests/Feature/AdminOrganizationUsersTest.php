<?php

namespace Tests\Feature;

use App\Support\Frontend\MockAdminData;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AdminOrganizationUsersTest extends TestCase
{
    public function test_organization_show_renders_northstar_detail(): void
    {
        $this->get(route('admin.organizations.show', 'org-ns-001'))
            ->assertOk()
            ->assertSee('Northstar Labs', false)
            ->assertSee('ORG-NS-001', false)
            ->assertSee('Company workspace', false)
            ->assertSee('Olivia Bennett', false)
            ->assertSee('Workspace profile', false)
            ->assertSee('Members & access')
            ->assertSee('Alex Morgan', false)
            ->assertSee('Review verification', false)
            ->assertSee('Review suspension', false)
            ->assertSee('Overview', false)
            ->assertSee('Subscription', false)
            ->assertSee('Admin actions', false)
            ->assertSee('Software &amp; collaboration', false)
            ->assertDontSee('46 /', false)
            ->assertDontSee('Frame 46', false)
            ->assertDontSee('Frontend UI only', false);
    }

    public function test_organization_show_returns_distinct_data_for_other_orgs(): void
    {
        $hirebridge = $this->get(route('admin.organizations.show', 'org-ht-001'));
        $hirebridge->assertOk()
            ->assertSee('Hirebridge Talent', false)
            ->assertSee('ORG-HT-001', false)
            ->assertSee('Agency workspace', false)
            ->assertSee('Jamie Rivera', false)
            ->assertDontSee('Software &amp; collaboration · Austin, Texas', false)
            ->assertDontSee('ORG-NS-001', false);

        $cedar = $this->get(route('admin.organizations.show', 'org-cd-001'));
        $cedar->assertOk()
            ->assertSee('Cedar Systems', false)
            ->assertSee('Limited Admin overview', false)
            ->assertSee('Riley Quinn', false)
            ->assertSee('ORG-CD-001', false)
            ->assertDontSee('ORG-NS-001', false)
            ->assertDontSee('Software &amp; collaboration · Austin, Texas', false);
    }

    public function test_unknown_organization_returns_not_found(): void
    {
        $this->get(route('admin.organizations.show', 'org-does-not-exist'))
            ->assertNotFound();
    }

    public function test_organizations_index_links_to_show_routes(): void
    {
        $this->get(route('admin.organizations.index'))
            ->assertOk()
            ->assertSee(route('admin.organizations.show', 'org-ns-001'), false)
            ->assertSee(route('admin.organizations.show', 'org-ht-001'), false)
            ->assertDontSee('data-org-preview', false);
    }

    public function test_users_index_renders_directory_structure(): void
    {
        $this->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Users & access')
            ->assertSee('Cross-organization identity', false)
            ->assertSee('Invite platform user', false)
            ->assertSee('All users', false)
            ->assertSee('Maya Chen', false)
            ->assertSee('Olivia Bennett', false)
            ->assertSee('Alex Morgan', false)
            ->assertSee('Priya Shah', false)
            ->assertSee('Morgan Price', false)
            ->assertSee('No silent account takeover', false)
            ->assertSee('data-admin-users', false)
            ->assertSee(route('admin.organizations.show', 'org-ns-001'), false)
            ->assertDontSee('47 /', false)
            ->assertDontSee('Frame 47', false)
            ->assertDontSee('of 96', false);
    }

    public function test_users_index_accepts_tab_and_search_query(): void
    {
        $this->get(route('admin.users.index', ['tab' => 'suspended', 'q' => 'Morgan']))
            ->assertOk()
            ->assertSee('data-default-tab="suspended"', false)
            ->assertSee('data-initial-q="Morgan"', false);
    }

    public function test_admin_routes_and_sidebar_include_users(): void
    {
        $this->assertTrue(Route::has('admin.organizations.show'));
        $this->assertTrue(Route::has('admin.users.index'));

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(route('admin.users.index'), false)
            ->assertSee(route('admin.organizations.show', 'org-ns-001'), false);
    }

    public function test_mock_users_count_matches_directory_without_fabricated_population(): void
    {
        $index = MockAdminData::usersIndex();
        $this->assertCount(12, $index['users']);
        $this->assertSame(12, $index['tabs'][0]['count']);
        $this->assertSame(9, $index['tabs'][1]['count']);
        $this->assertSame(2, $index['tabs'][2]['count']);
        $this->assertSame(1, $index['tabs'][3]['count']);

        $northstar = MockAdminData::organizationShow('org-ns-001');
        $this->assertNotNull($northstar);
        $this->assertSame('full', $northstar['detail_level']);
        $this->assertCount(5, $northstar['members']);

        $limited = MockAdminData::organizationShow('org-ms-001');
        $this->assertNotNull($limited);
        $this->assertSame('limited', $limited['detail_level']);
    }

    public function test_organization_show_admin_actions_remain_non_submitting(): void
    {
        $html = $this->get(route('admin.organizations.show', 'org-ns-001'))->assertOk()->getContent();

        $this->assertStringContainsString('data-org-suspend-review', $html);
        $this->assertStringNotContainsString('method="post"', strtolower($html));
        $this->assertStringNotContainsString('method="patch"', strtolower($html));
        $this->assertStringNotContainsString('impersonat', strtolower($html));
    }
}

<?php

namespace Tests\Feature;

use App\Support\Frontend\MockAdminData;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AdminJobsAgenciesTest extends TestCase
{
    public function test_job_moderation_renders_frame_forty_eight_structure(): void
    {
        $this->get(route('admin.jobs.index'))
            ->assertOk()
            ->assertSee('Job moderation', false)
            ->assertSee('Review policy concerns with context', false)
            ->assertSee('LF-091', false)
            ->assertSee('Product Analyst', false)
            ->assertSee('Lumen Financial', false)
            ->assertSee('OR-014', false)
            ->assertSee('CD-008', false)
            ->assertSee('Missing salary range', false)
            ->assertSee('Request changes', false)
            ->assertSee('Approve', false)
            ->assertSee('data-admin-jobs', false)
            ->assertSee(route('admin.organizations.show', 'org-lf-001'), false)
            ->assertDontSee('48 /', false)
            ->assertDontSee('Frame 48', false)
            ->assertDontSee('Frontend UI only', false);
    }

    public function test_job_moderation_accepts_status_and_search_query(): void
    {
        $this->get(route('admin.jobs.index', ['status' => 'pending', 'q' => 'LF-091']))
            ->assertOk()
            ->assertSee('data-default-status="pending"', false)
            ->assertSee('data-initial-q="LF-091"', false);
    }

    public function test_agency_operations_renders_frame_forty_nine_structure(): void
    {
        $this->get(route('admin.agencies.index'))
            ->assertOk()
            ->assertSee('Agency operations', false)
            ->assertSee('Recruitment services made concrete', false)
            ->assertSee('Create engagement', false)
            ->assertSee('ENG-104', false)
            ->assertSee('NS-204', false)
            ->assertSee('Senior Product Designer', false)
            ->assertSee('Hirebridge Talent', false)
            ->assertSee('Jamie Rivera', false)
            ->assertSee('Marcus Reed', false)
            ->assertSee('data-admin-agencies', false)
            ->assertSee(route('admin.organizations.show', 'org-ns-001'), false)
            ->assertSee(route('admin.organizations.show', 'org-ht-001'), false)
            ->assertDontSee('49 /', false)
            ->assertDontSee('Frame 49', false);
    }

    public function test_agency_operations_accepts_search_query(): void
    {
        $this->get(route('admin.agencies.index', ['q' => 'ENG-104']))
            ->assertOk()
            ->assertSee('data-initial-q="ENG-104"', false);
    }

    public function test_admin_sidebar_and_routes_include_jobs_and_agencies(): void
    {
        $this->assertTrue(Route::has('admin.jobs.index'));
        $this->assertTrue(Route::has('admin.agencies.index'));

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(route('admin.jobs.index'), false)
            ->assertSee(route('admin.agencies.index'), false);

        $html = $this->get(route('admin.jobs.index'))->assertOk()->getContent();
        $this->assertStringNotContainsString('method="post"', strtolower($html));
        $this->assertStringContainsString('No job status was changed', $html);
    }

    public function test_mock_moderation_and_agency_data_align_with_platform_records(): void
    {
        $jobs = MockAdminData::jobsModerationIndex();
        $this->assertCount(3, $jobs['jobs']);
        $this->assertSame('LF-091', $jobs['jobs'][0]['code']);
        $this->assertSame('org-lf-001', $jobs['jobs'][0]['org_id']);

        $agencies = MockAdminData::agenciesIndex();
        $this->assertCount(4, $agencies['engagements']);
        $this->assertSame('4', $agencies['stats']['active_engagements']);
        $this->assertSame('ENG-104', $agencies['engagements'][0]['code']);
        $this->assertSame('NS-204', $agencies['engagements'][0]['job_code']);
        $this->assertSame('org-ns-001', $agencies['engagements'][0]['client_org_id']);
        $this->assertSame('org-ht-001', $agencies['engagements'][0]['agency_org_id']);
    }

    public function test_moderation_actions_remain_non_submitting(): void
    {
        $html = $this->get(route('admin.agencies.index'))->assertOk()->getContent();
        $this->assertStringNotContainsString('method="post"', strtolower($html));
        $this->assertStringNotContainsString('method="patch"', strtolower($html));
        $this->assertStringContainsString('No engagement was created', $html);
    }
}

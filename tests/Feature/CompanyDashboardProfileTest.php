<?php

namespace Tests\Feature;

use Tests\TestCase;

class CompanyDashboardProfileTest extends TestCase
{
    public function test_company_dashboard_matches_frame_twenty_five(): void
    {
        $this->get(route('company.dashboard'))
            ->assertOk()
            ->assertSee('25 / Company workspace / Overview', false)
            ->assertSee('Good morning, Olivia', false)
            ->assertSee('Northstar Labs hiring overview', false)
            ->assertSee('Open requisitions', false)
            ->assertSee('Active candidates', false)
            ->assertSee('Interviews scheduled', false)
            ->assertSee('Hired this month', false)
            ->assertSee('Grace Liu · NS-204', false)
            ->assertSee('Applicant pipeline', false)
            ->assertSee('Applied', false)
            ->assertSee('Screening', false)
            ->assertSee('Interview', false)
            ->assertSee('Offer', false)
            ->assertSee('Hired', false)
            ->assertSee('Active requisitions', false)
            ->assertSee('NS-204', false)
            ->assertSee('Senior Product Designer', false)
            ->assertSee('Upcoming interviews', false)
            ->assertSee('Maya Chen', false)
            ->assertSee('Portfolio review · NS-204', false)
            ->assertSee('Hiring activity', false)
            ->assertSee('Pending actions', false)
            ->assertSee('Create job', false)
            ->assertSee('Last 30 days', false)
            ->assertDontSee('Frontend UI only', false)
            ->assertSee(route('company.profile'), false)
            ->assertDontSee('Elena Rodriguez', false);
    }

    public function test_company_profile_is_workflow_derived(): void
    {
        $this->get(route('company.profile'))
            ->assertOk()
            ->assertSee('Company workspace / Company profile', false)
            ->assertSee('Company profile', false)
            ->assertSee('Northstar Labs', false)
            ->assertSee('Company identity', false)
            ->assertSee('Northstar Labs, Inc.', false)
            ->assertSee('Workspace details', false)
            ->assertSee('hirebridge.example/', false)
            ->assertSee('northstar-labs', false)
            ->assertSee('Software &amp; collaboration', false)
            ->assertSee('Austin, Texas, United States', false)
            ->assertSee('Hiring contact', false)
            ->assertSee('Olivia Bennett', false)
            ->assertSee('olivia.bennett@northstarlabs.example', false)
            ->assertSee('Verification status', false)
            ->assertSee('Verified', false)
            ->assertSee('View public profile', false)
            ->assertSee('Save identity', false)
            ->assertSee('Save workspace details', false)
            ->assertDontSee('no dedicated Figma profile frame', false)
            ->assertDontSee('Frontend UI only', false)
            ->assertDontSee('Batch 5A', false)
            ->assertDontSee('Company profile saved', false);
    }

    public function test_company_sidebar_wires_overview_and_profile(): void
    {
        $dashboard = $this->get(route('company.dashboard'));
        $dashboard->assertOk();
        $dashboard->assertSee(route('company.dashboard'), false);
        $dashboard->assertSee(route('company.profile'), false);
        $dashboard->assertSee('Jobs &amp; requisitions', false);
        $dashboard->assertSee('Soon', false);

        $profile = $this->get(route('company.profile'));
        $profile->assertOk();
        $profile->assertSee(route('company.dashboard'), false);
        $profile->assertSee('aria-current="page"', false);
    }

    public function test_company_routes_are_get_only(): void
    {
        $this->get(route('company.dashboard'))->assertOk();
        $this->get(route('company.profile'))->assertOk();
        $this->post('/company/dashboard')->assertMethodNotAllowed();
        $this->post('/company/profile')->assertMethodNotAllowed();
    }

    public function test_candidate_dashboard_still_works(): void
    {
        $this->get(route('candidate.dashboard'))
            ->assertOk()
            ->assertSee('Good morning, Maya', false);
    }
}

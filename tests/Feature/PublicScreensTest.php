<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicScreensTest extends TestCase
{
    public function test_public_home_renders_full_landing_sections(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('The right people.', false);
        $response->assertSee('A good place to start.', false);
        $response->assertSee('Senior Product Designer', false);
        $response->assertSee('collaborative-workplace', false);
    }

    public function test_jobs_index_and_show_use_mock_catalog(): void
    {
        $this->get(route('jobs.index'))
            ->assertOk()
            ->assertSee('Find your next role', false)
            ->assertDontSee('Mock listings', false)
            ->assertSee('NS-204', false);

        $this->get(route('jobs.show', 'ns-204'))
            ->assertOk()
            ->assertSee('Senior Product Designer', false)
            ->assertSee('Apply for this role', false)
            ->assertSee(route('candidate.applications.apply', 'ns-204'), false)
            ->assertSee('data-public-apply', false)
            ->assertDontSee('Applications require authentication', false)
            ->assertDontSee('03 /', false)
            ->assertDontSee('Mock job', false);
    }

    public function test_public_apply_preserves_job_context_on_candidate_apply_route(): void
    {
        $this->get(route('candidate.applications.apply', 'ms-108'))
            ->assertOk()
            ->assertSee('Meridian Studio', false)
            ->assertSee('Lead Product Designer', false)
            ->assertSee('MS-108', false);
    }

    public function test_public_jobs_index_has_working_filter_controls(): void
    {
        $this->get(route('jobs.index'))
            ->assertOk()
            ->assertSee('data-jobs-directory', false)
            ->assertSee('data-jobs-sort', false)
            ->assertSee('data-clear-filters', false)
            ->assertSee('data-jobs-pagination', false)
            ->assertDontSee('02 /', false);
    }

    public function test_unknown_demo_job_returns_not_found(): void
    {
        $this->get(route('jobs.show', 'missing-job'))->assertNotFound();
    }

    public function test_about_company_and_contact_pages_render(): void
    {
        $this->get(route('about'))
            ->assertOk()
            ->assertSee('Specialist recruitment', false)
            ->assertSee('Contingent search', false);

        $this->get(route('companies.show', 'northstar-labs'))
            ->assertOk()
            ->assertSee('Northstar Labs', false)
            ->assertSee('Current openings', false);

        $this->get(route('contact'))
            ->assertOk()
            ->assertSee('Request a consultation', false)
            ->assertDontSee('Frontend only', false);
    }

    public function test_stage_one_and_two_preview_routes_remain_in_local(): void
    {
        $this->get(route('frontend.layouts.app'))->assertOk();
        $this->get(route('frontend.components'))->assertOk();
    }
}

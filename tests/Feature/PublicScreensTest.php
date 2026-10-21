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
            ->assertDontSee('Mock job', false);
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

<?php

namespace Tests\Feature;

use Tests\TestCase;

class SharedUiFoundationTest extends TestCase
{
    public function test_component_showcase_renders_shared_ui(): void
    {
        $response = $this->get(route('frontend.components', ['role' => 'company']));

        $response->assertOk();
        $response->assertSee('Component showcase', false);
        $response->assertSee('Applicant pipeline', false);
        $response->assertSee('Maya Chen', false);
        $response->assertSee('Notifications', false);
        $response->assertSee('Mock data', false);
    }

    public function test_app_layout_includes_role_sidebar_and_topbar(): void
    {
        $response = $this->get(route('frontend.layouts.app', ['role' => 'interviewer']));

        $response->assertOk();
        $response->assertSee('Interviewer', false);
        $response->assertSee('My schedule', false);
        $response->assertSee('Alex Morgan', false);
        $response->assertSee('Search Hirebridge', false);
        $response->assertSee('Soon', false);
    }

    public function test_existing_stage_one_routes_remain(): void
    {
        $this->get(route('home'))->assertOk();
        $this->get(route('frontend.layouts.auth'))->assertOk();
        $this->get(route('frontend.layouts.app'))->assertOk();
    }
}

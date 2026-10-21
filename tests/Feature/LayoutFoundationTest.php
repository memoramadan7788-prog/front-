<?php

namespace Tests\Feature;

use Tests\TestCase;

class LayoutFoundationTest extends TestCase
{
    public function test_home_page_renders_public_layout(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('hirebridge', false);
        $response->assertSee('Find a job', false);
        $response->assertSee('Your next chapter starts here', false);
    }

    public function test_auth_layout_preview_renders(): void
    {
        $response = $this->get(route('frontend.layouts.auth'));

        $response->assertOk();
        $response->assertSee('Welcome back', false);
        $response->assertSee('Mock UI only', false);
    }

    public function test_app_layout_preview_renders_for_each_ui_role(): void
    {
        foreach (['candidate', 'company', 'interviewer', 'admin'] as $role) {
            $response = $this->get(route('frontend.layouts.app', ['role' => $role]));

            $response->assertOk();
            $response->assertSee('Application workspace', false);
            $response->assertSee('Content canvas', false);
        }
    }
}

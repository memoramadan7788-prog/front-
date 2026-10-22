<?php

namespace Tests\Feature;

use App\Support\Frontend\MockAdminData;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AdminSettingsTest extends TestCase
{
    public function test_platform_settings_renders_frame_fifty_structure(): void
    {
        $this->get(route('admin.settings.index'))
            ->assertOk()
            ->assertSee('Platform settings &amp; audit', false)
            ->assertSee('Security, privacy, integrations', false)
            ->assertSee('Audit log', false)
            ->assertSee('Security &amp; privacy', false)
            ->assertSee('Permissions', false)
            ->assertSee('Integrations', false)
            ->assertSee('Priya Shah', false)
            ->assertSee('LF-091', false)
            ->assertSee('Requested job changes', false)
            ->assertSee('Pinecrest Recruiting', false)
            ->assertSee('Blocked', false)
            ->assertSee('Restricted settings', false)
            ->assertSee('data-admin-settings', false)
            ->assertDontSee('50 /', false)
            ->assertDontSee('Frame 50', false)
            ->assertDontSee('Frontend UI only', false);
    }

    public function test_settings_accepts_tab_and_search_query(): void
    {
        $this->get(route('admin.settings.index', ['tab' => 'security', 'q' => 'Priya']))
            ->assertOk()
            ->assertSee('data-default-tab="security"', false)
            ->assertSee('data-initial-q="Priya"', false)
            ->assertSee('Multi-factor authentication', false);
    }

    public function test_settings_permissions_and_integrations_panels_render(): void
    {
        $this->get(route('admin.settings.index', ['tab' => 'permissions']))
            ->assertOk()
            ->assertSee('Super Admin', false)
            ->assertSee('Company admin', false)
            ->assertSee('Inspect role', false);

        $this->get(route('admin.settings.index', ['tab' => 'integrations']))
            ->assertOk()
            ->assertSee('Email delivery', false)
            ->assertSee('Calendar sync', false)
            ->assertSee('Video meetings', false)
            ->assertSee('HRIS export', false)
            ->assertSee('Unavailable', false);
    }

    public function test_admin_sidebar_and_dashboard_link_to_settings(): void
    {
        $this->assertTrue(Route::has('admin.settings.index'));

        $dashboard = $this->get(route('admin.dashboard'))->assertOk();
        $dashboard->assertSee(route('admin.settings.index'), false);
        $dashboard->assertSee('Review access policies', false);

        $html = $this->get(route('admin.settings.index'))->assertOk()->getContent();
        $this->assertStringNotContainsString('method="post"', strtolower($html));
        $this->assertStringNotContainsString('method="patch"', strtolower($html));
        $this->assertStringContainsString('No restricted setting was changed', $html);
        $this->assertStringContainsString('not live platform telemetry', $html);
    }

    public function test_mock_audit_events_are_coherent_and_honestly_counted(): void
    {
        $page = MockAdminData::settingsIndex();
        $events = $page['events'];

        $this->assertSame(8, $page['page_size']);
        $this->assertCount(16, $events);
        $this->assertSame('aud-001', $events[0]['id']);
        $this->assertSame('LF-091 · Lumen Financial', $events[0]['object']);
        $this->assertSame('Priya Shah', $events[0]['actor']);
        $this->assertSame('blocked', $events[7]['result']);
        $this->assertSame('Pinecrest Recruiting', $events[7]['object']);

        $octRange = array_values(array_filter(
            $events,
            static fn (array $event): bool => ($event['range'] ?? '') === 'oct19_20'
        ));
        $this->assertCount(10, $octRange);
    }

    public function test_sensitive_settings_actions_remain_non_submitting(): void
    {
        $html = $this->get(route('admin.settings.index'))->assertOk()->getContent();
        $this->assertStringContainsString('No authentication, session, retention, or privacy setting was changed', $html);
        $this->assertStringContainsString('No account access was changed', $html);
        $this->assertStringContainsString('No external connection or credential was changed', $html);
    }
}

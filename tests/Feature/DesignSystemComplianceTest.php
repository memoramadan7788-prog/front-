<?php

namespace Tests\Feature;

use Tests\TestCase;

class DesignSystemComplianceTest extends TestCase
{
    public function test_frame_fifty_one_color_tokens_match_reference(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertNotFalse($css);
        $this->assertStringContainsString('--hb-navy: #142b4a;', $css);
        $this->assertStringContainsString('--hb-blue: #2457c5;', $css);
        $this->assertStringContainsString('--hb-teal: #14b8a6;', $css);
        $this->assertStringContainsString('--hb-page: #f5f7fa;', $css);
        $this->assertStringContainsString('--hb-surface: #ffffff;', $css);
        $this->assertStringContainsString('--hb-text: #1f2937;', $css);
        $this->assertStringContainsString('--hb-muted: #64748b;', $css);
        $this->assertStringContainsString('--hb-border: #e2e8f0;', $css);
        $this->assertStringContainsString('--hb-success: #276749;', $css);
        $this->assertStringContainsString('--hb-success-soft: #edf7f0;', $css);
        $this->assertStringContainsString('--hb-pending: #a85b18;', $css);
        $this->assertStringContainsString('--hb-pending-soft: #fff7e8;', $css);
        $this->assertStringContainsString('--hb-error: #b04a40;', $css);
        $this->assertStringContainsString('--hb-error-soft: #fde0d0;', $css);
        $this->assertStringContainsString('--hb-info-soft: #edf3ff;', $css);
        $this->assertStringContainsString('.hb-btn-destructive', $css);
        $this->assertStringContainsString('pointer-events: none;', $css);
        $this->assertStringContainsString('.hb-page-title', $css);
    }

    public function test_shared_design_system_components_are_non_empty(): void
    {
        $paths = [
            'components/alert.blade.php',
            'components/empty-state.blade.php',
            'components/skeleton.blade.php',
            'components/status-badge.blade.php',
            'components/progress.blade.php',
            'components/stat-card.blade.php',
            'components/form/input.blade.php',
            'components/form/select.blade.php',
            'components/form/textarea.blade.php',
            'components/form/checkbox.blade.php',
            'components/form/file-upload.blade.php',
        ];

        foreach ($paths as $path) {
            $full = resource_path('views/'.$path);
            $this->assertFileExists($full);
            $this->assertGreaterThan(0, filesize($full), $path.' should not be empty');
        }
    }

    public function test_component_showcase_includes_frame_fifty_one_patterns(): void
    {
        $this->get(route('frontend.components', ['role' => 'company']))
            ->assertOk()
            ->assertSee('Preferences saved', false)
            ->assertSee('You have unsaved changes', false)
            ->assertSee('No saved roles yet', false)
            ->assertSee('Applied', false)
            ->assertSee('Direct hire', false)
            ->assertSee('Agency-managed', false);
    }

    public function test_admin_settings_uses_semantic_destructive_action(): void
    {
        $html = $this->get(route('admin.settings.index'))->assertOk()->getContent();

        $this->assertStringContainsString('hb-btn-destructive', $html);
        $this->assertStringNotContainsString('bg-red-800', $html);
        $this->assertStringContainsString('text-hb-error', $html);
    }

    public function test_secondary_button_component_uses_blue_border_pattern(): void
    {
        $button = file_get_contents(resource_path('views/components/button.blade.php'));

        $this->assertNotFalse($button);
        $this->assertStringContainsString('border border-hb-blue', $button);
        $this->assertStringContainsString('text-hb-blue', $button);
        $this->assertStringContainsString('min-h-11', $button);
    }
}

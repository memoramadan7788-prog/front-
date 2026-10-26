<?php

namespace Tests\Unit;

use App\Support\Frontend\SafeInternalRedirect;
use Tests\TestCase;

class SafeInternalRedirectTest extends TestCase
{
    public function test_accepts_relative_app_paths_with_query(): void
    {
        $this->assertSame(
            '/candidate/dashboard?tab=1',
            SafeInternalRedirect::sanitize('/candidate/dashboard?tab=1')
        );
    }

    public function test_rejects_external_and_protocol_relative_targets(): void
    {
        $fallback = route('frontend.home', [], false);

        $this->assertSame($fallback, SafeInternalRedirect::sanitize('https://evil.example/phish'));
        $this->assertSame($fallback, SafeInternalRedirect::sanitize('//evil.example/phish'));
        $this->assertSame($fallback, SafeInternalRedirect::sanitize('javascript:alert(1)'));
        $this->assertSame($fallback, SafeInternalRedirect::sanitize('candidate/dashboard'));
    }

    public function test_same_host_absolute_url_is_reduced_to_path(): void
    {
        config(['app.url' => 'http://localhost']);

        $this->assertSame(
            '/jobs/42',
            SafeInternalRedirect::sanitize('http://localhost/jobs/42')
        );
    }

    public function test_locale_switch_path_is_rejected_to_avoid_loops(): void
    {
        $fallback = route('frontend.home', [], false);

        $this->assertSame($fallback, SafeInternalRedirect::sanitize('/locale/ar'));
        $this->assertSame($fallback, SafeInternalRedirect::sanitize('/locale/en?redirect=/home'));
    }
}

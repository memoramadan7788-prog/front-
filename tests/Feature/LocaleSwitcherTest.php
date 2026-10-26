<?php

namespace Tests\Feature;

use App\Frontend\Auth\ApiSession;
use App\Http\Middleware\SetFrontendLocale;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class LocaleSwitcherTest extends TestCase
{
    public function test_guest_public_pages_include_language_switcher(): void
    {
        $html = $this->get(route('frontend.home'))->assertOk()->getContent();

        $this->assertStringContainsString('data-language-switch', $html);
        $this->assertStringContainsString('data-lang-option="en"', $html);
        $this->assertStringContainsString('data-lang-option="ar"', $html);
        $this->assertStringContainsString('data-theme-switch', $html);

        $jobsHtml = $this->get(route('frontend.jobs.index'))->assertOk()->getContent();
        $this->assertStringContainsString('data-language-switch', $jobsHtml);
    }

    public function test_authenticated_topbar_includes_language_switcher_near_theme(): void
    {
        $this->actingAsCandidate();

        $html = $this->get(route('frontend.candidate.dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString('data-language-switch', $html);
        $this->assertStringContainsString('data-theme-switch', $html);
        $this->assertStringContainsString('data-lang-option="ar"', $html);

        $themePos = strpos($html, 'data-theme-switch');
        $langPos = strpos($html, 'data-language-switch');
        $this->assertNotFalse($themePos);
        $this->assertNotFalse($langPos);
        $this->assertGreaterThan($themePos, $langPos);
    }

    public function test_switching_to_arabic_sets_cookie_and_preserves_safe_redirect(): void
    {
        $this->get(route('frontend.locale.switch', [
            'locale' => 'ar',
            'redirect' => '/candidate/dashboard',
        ]))
            ->assertRedirect('/candidate/dashboard')
            ->assertPlainCookie(SetFrontendLocale::COOKIE, 'ar');
    }

    public function test_switching_to_english_sets_cookie(): void
    {
        $this->get(route('frontend.locale.switch', [
            'locale' => 'en',
            'redirect' => '/jobs',
        ]))
            ->assertRedirect('/jobs')
            ->assertPlainCookie(SetFrontendLocale::COOKIE, 'en');
    }

    public function test_unsupported_locale_falls_back_without_changing_auth(): void
    {
        $this->actingAsCandidate();

        $this->get(route('frontend.locale.switch', [
            'locale' => 'fr',
            'redirect' => '/candidate/dashboard',
        ]))
            ->assertRedirect('/candidate/dashboard')
            ->assertPlainCookie(SetFrontendLocale::COOKIE, 'en');

        $this->assertTrue(ApiSession::check());
        $this->assertSame('candidate', ApiSession::accountType());
    }

    public function test_unsafe_redirect_is_rejected(): void
    {
        $this->get(route('frontend.locale.switch', [
            'locale' => 'ar',
            'redirect' => 'https://evil.example/steal',
        ]))
            ->assertRedirect(route('frontend.home', [], false))
            ->assertPlainCookie(SetFrontendLocale::COOKIE, 'ar');
    }

    public function test_locale_cookie_drives_rtl_html_on_public_page(): void
    {
        $html = $this->withUnencryptedCookie(SetFrontendLocale::COOKIE, 'ar')
            ->get(route('frontend.jobs.index'))
            ->assertOk()
            ->getContent();

        $this->assertSame('ar', App::getLocale());
        $this->assertMatchesRegularExpression('/<html[^>]*\slang="ar"/', $html);
        $this->assertMatchesRegularExpression('/<html[^>]*\sdir="rtl"/', $html);
        $this->assertStringContainsString('data-lang-option="ar"', $html);
        $this->assertStringContainsString('aria-current="true"', $html);
    }

    public function test_locale_cookie_drives_ltr_html_on_authenticated_page(): void
    {
        $this->actingAsCandidate();

        $html = $this->withUnencryptedCookie(SetFrontendLocale::COOKIE, 'en')
            ->get(route('frontend.candidate.dashboard'))
            ->assertOk()
            ->getContent();

        $this->assertSame('en', App::getLocale());
        $this->assertMatchesRegularExpression('/<html[^>]*\slang="en"/', $html);
        $this->assertMatchesRegularExpression('/<html[^>]*\sdir="ltr"/', $html);
    }

    public function test_locale_persists_across_navigation(): void
    {
        $this->withUnencryptedCookie(SetFrontendLocale::COOKIE, 'ar')
            ->get(route('frontend.home'))
            ->assertOk();
        $this->assertSame('ar', App::getLocale());

        $this->withUnencryptedCookie(SetFrontendLocale::COOKIE, 'ar')
            ->get(route('frontend.jobs.index'))
            ->assertOk();
        $this->assertSame('ar', App::getLocale());
    }

    public function test_auth_layout_includes_language_switcher(): void
    {
        $html = $this->get(route('frontend.account.login'))->assertOk()->getContent();

        $this->assertStringContainsString('data-language-switch', $html);
        $this->assertStringContainsString('data-theme-switch', $html);
    }

    public function test_language_switch_links_include_current_page_redirect(): void
    {
        $html = $this->get(route('frontend.jobs.index'))->assertOk()->getContent();

        $this->assertStringContainsString(
            'redirect='.rawurlencode('/jobs'),
            $html
        );
    }

    public function test_workspace_navigation_still_renders_after_locale_switch_partial(): void
    {
        $this->actingAsCandidate();

        $this->get(route('frontend.candidate.dashboard'))
            ->assertOk()
            ->assertSee('data-sidebar-nav-toggle', false)
            ->assertSee(__('navigation.candidate.discover_jobs'));
    }

    private function actingAsCandidate(): void
    {
        ApiSession::store('cand-token', [
            'id' => 11,
            'email' => 'candidate@example.com',
            'name' => 'Candidate',
            'account_type' => 'candidate',
            'roles' => ['candidate'],
        ]);
    }
}

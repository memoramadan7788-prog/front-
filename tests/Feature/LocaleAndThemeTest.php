<?php

namespace Tests\Feature;

use App\Http\Middleware\SetFrontendLocale;
use Tests\TestCase;

class LocaleAndThemeTest extends TestCase
{
    public function test_default_locale_is_english_with_ltr(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('lang="en"', false);
        $response->assertSee('dir="ltr"', false);
        $response->assertSee('Find a job', false);
        $response->assertSee('data-theme-switch', false);
        $response->assertSee('data-theme-option="light"', false);
        $response->assertSee('data-theme-option="dark"', false);
        $response->assertSee('data-theme-option="system"', false);
    }

    public function test_arabic_cookie_renders_translated_rtl_ui(): void
    {
        $response = $this->withUnencryptedCookie(SetFrontendLocale::COOKIE, 'ar')
            ->get(route('home'));

        $response->assertOk();
        $response->assertSee('lang="ar"', false);
        $response->assertSee('dir="rtl"', false);
        $response->assertSee('ابحث عن وظيفة', false);
        $response->assertSee('ابدأ الآن', false);
        $response->assertDontSee('>Find a job<', false);
    }

    public function test_locale_switch_sets_cookie_and_redirects_back(): void
    {
        $response = $this->from(route('login'))
            ->get(route('locale.switch', 'ar'));

        $response->assertRedirect(route('login'));
        $response->assertPlainCookie(SetFrontendLocale::COOKIE, 'ar');
    }

    public function test_arabic_auth_and_candidate_chrome_translate(): void
    {
        $this->withUnencryptedCookie(SetFrontendLocale::COOKIE, 'ar')
            ->get(route('login'))
            ->assertOk()
            ->assertSee('مرحبًا بعودتك', false)
            ->assertSee('dir="rtl"', false);

        $this->withUnencryptedCookie(SetFrontendLocale::COOKIE, 'ar')
            ->get(route('candidate.dashboard'))
            ->assertOk()
            ->assertSee('استكشاف الوظائف', false)
            ->assertSee('طلباتي', false)
            ->assertSee('data-theme-switch', false);
    }

    public function test_layouts_include_theme_boot_and_js_i18n(): void
    {
        $home = $this->get(route('home'));
        $home->assertOk();
        $home->assertSee('hb_theme', false);
        $home->assertSee('window.__hbI18n', false);
        $home->assertSee('window.__hbLocale', false);

        $this->get(route('candidate.dashboard'))
            ->assertOk()
            ->assertSee('window.__hbI18n', false);
    }

    public function test_invalid_locale_falls_back_to_english(): void
    {
        $this->withUnencryptedCookie(SetFrontendLocale::COOKIE, 'fr')
            ->get(route('home'))
            ->assertOk()
            ->assertSee('lang="en"', false)
            ->assertSee('dir="ltr"', false)
            ->assertSee('Find a job', false);
    }
}

<?php

namespace Tests\Feature;

use App\Frontend\Auth\ApiSession;
use App\Http\Middleware\SetFrontendLocale;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Lang;
use Tests\TestCase;

class LocalizationCompletionTest extends TestCase
{
    public function test_flash_catalog_exists_in_english_and_arabic(): void
    {
        $keys = [
            'flash.candidate.profile_updated',
            'flash.candidate.password_changed',
            'flash.candidate.skill_removed',
            'flash.admin.company_approved',
            'flash.company.vacancy_created',
            'flash.employee.join_requested',
            'flash.application.withdrawn',
            'flash.interview.booked',
            'flash.interviewer.slot_added',
            'flash.offer.accepted',
            'flash.exam.submitted',
        ];

        foreach ($keys as $key) {
            $this->assertTrue(Lang::has($key, 'en'), "Missing EN key {$key}");
            $this->assertTrue(Lang::has($key, 'ar'), "Missing AR key {$key}");
            $this->assertNotSame(__($key, [], 'en'), __($key, [], 'ar'), "EN/AR should differ for {$key}");
        }
    }

    public function test_success_key_flash_localizes_on_candidate_dashboard(): void
    {
        ApiSession::store('cand-token', [
            'id' => 11,
            'email' => 'c@example.com',
            'name' => 'Cand',
            'account_type' => 'candidate',
        ]);

        App::setLocale('en');
        $en = $this->withSession(['success_key' => 'flash.candidate.profile_updated'])
            ->get(route('frontend.candidate.dashboard'))
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString(__('flash.candidate.profile_updated', [], 'en'), $en);
        $this->assertStringNotContainsString(__('flash.candidate.profile_updated', [], 'ar'), $en);

        App::setLocale('ar');
        $ar = $this->withSession(['success_key' => 'flash.candidate.profile_updated'])
            ->get(route('frontend.candidate.dashboard'))
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString(__('flash.candidate.profile_updated', [], 'ar'), $ar);
        $this->assertStringNotContainsString(__('flash.candidate.profile_updated', [], 'en'), $ar);
    }

    public function test_auth_pitch_and_hall_landing_use_translated_copy(): void
    {
        // Pitch copy remains on centered auth pages (register). Login uses the branding panel.
        $registerEn = $this->withUnencryptedCookie(SetFrontendLocale::COOKIE, 'en')
            ->get(route('frontend.account.register'))
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString(__('auth.pitch.headline', [], 'en'), $registerEn);

        $loginEn = $this->withUnencryptedCookie(SetFrontendLocale::COOKIE, 'en')
            ->get(route('frontend.account.login'))
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString(__('auth.login.title', [], 'en'), $loginEn);
        $this->assertStringContainsString(__('auth.brand_headline', [], 'en'), $loginEn);

        $loginAr = $this->withUnencryptedCookie(SetFrontendLocale::COOKIE, 'ar')
            ->get(route('frontend.account.login'))
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString(__('auth.login.title', [], 'ar'), $loginAr);
        $this->assertStringContainsString(__('auth.brand_headline', [], 'ar'), $loginAr);
        $this->assertMatchesRegularExpression('/<html[^>]*\sdir="rtl"/', $loginAr);

        $landingAr = $this->withUnencryptedCookie(SetFrontendLocale::COOKIE, 'ar')
            ->get(route('frontend.hall.landing'))
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString(__('hall.journeys.title', [], 'ar'), $landingAr);
        $this->assertStringContainsString(__('hall.slides.0.title', [], 'ar'), $landingAr);
        $this->assertMatchesRegularExpression('/<html[^>]*\sdir="rtl"/', $landingAr);

        $homeAr = $this->withUnencryptedCookie(SetFrontendLocale::COOKIE, 'ar')
            ->get(route('frontend.home'))
            ->assertOk()
            ->getContent();
        $this->assertMatchesRegularExpression('/<html[^>]*\sdir="rtl"/', $homeAr);
        $this->assertStringContainsString('data-language-switch', $homeAr);
        $this->assertStringContainsString(__('hall.exterior.headline', [], 'ar'), $homeAr);
    }

    public function test_controllers_no_longer_hardcode_arabic_success_flashes(): void
    {
        $files = glob(base_path('app/Frontend/**/*.php')) ?: [];
        // Recursive glob fallback
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(base_path('app/Frontend'))
        );
        foreach ($iterator as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $contents = file_get_contents($file->getPathname());
            $this->assertDoesNotMatchRegularExpression(
                "/with\\(\\s*'success'\\s*,\\s*'[^']*[\\x{0600}-\\x{06FF}]/u",
                $contents,
                'Hardcoded Arabic success flash remains in '.$file->getPathname()
            );
        }
    }
}

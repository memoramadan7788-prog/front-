<?php

namespace Tests\Feature;

use App\Http\Middleware\SetFrontendLocale;
use Tests\TestCase;

class FrontendAcceptanceRegressionTest extends TestCase
{
    public function test_public_job_ns204_and_apply_context_remain_linked(): void
    {
        $this->get(route('jobs.show', 'ns-204'))
            ->assertOk()
            ->assertSee('Senior Product Designer', false)
            ->assertSee('Northstar Labs', false)
            ->assertSee('NS-204', false)
            ->assertSee(route('candidate.applications.apply', 'ns-204'), false);

        $this->get(route('candidate.applications.apply', 'ns-204'))
            ->assertOk()
            ->assertSee('NS-204', false)
            ->assertSee('Senior Product Designer', false);
    }

    public function test_candidate_messages_compose_label_updates_with_thread_contact(): void
    {
        $html = $this->get(route('candidate.messages.index'))->assertOk()->getContent();

        $this->assertStringContainsString('data-compose-label', $html);
        $this->assertStringContainsString('data-compose-template', $html);
        $this->assertStringContainsString('__NAME__', $html);
        $this->assertStringContainsString('Reply to Olivia Bennett', $html);
    }

    public function test_scorecard_rating_inputs_cover_full_chip_hit_area(): void
    {
        $html = $this->get(route('interviewer.evaluations.create', 'int-ns204-portfolio'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('data-score-rating="problem_framing"', $html);
        $this->assertStringContainsString('absolute inset-0 z-10 h-full w-full cursor-pointer opacity-0', $html);
        $this->assertDoesNotMatchRegularExpression(
            '/data-score-rating="[^"]+"[^>]*class="[^"]*sr-only/',
            $html
        );
        $this->assertDoesNotMatchRegularExpression(
            '/class="[^"]*sr-only[^"]*"[^>]*data-score-rating="/',
            $html
        );
    }

    public function test_admin_settings_and_organization_detail_remain_reachable(): void
    {
        $this->get(route('admin.settings.index'))
            ->assertOk()
            ->assertSee('Platform settings', false)
            ->assertSee('Audit log', false);

        $this->get(route('admin.organizations.show', 'org-ns-001'))
            ->assertOk()
            ->assertSee('Northstar Labs', false);
    }

    public function test_cross_module_ns204_interview_identifiers_align(): void
    {
        $this->get(route('company.applicants.show', 'maya-chen'))
            ->assertOk()
            ->assertSee('Maya Chen', false)
            ->assertSee('NS-204', false);

        $this->get(route('interviewer.interviews.prepare', 'int-ns204-portfolio'))
            ->assertOk()
            ->assertSee('Maya Chen', false)
            ->assertSee('NS-204', false);

        $this->get(route('candidate.applications.show', 'hb-ns204-mc-1012'))
            ->assertOk()
            ->assertSee('NS-204', false);
    }

    public function test_topbar_sticky_pointer_events_remain_configured(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));
        $topbar = file_get_contents(resource_path('views/partials/topbar.blade.php'));

        $this->assertNotFalse($css);
        $this->assertNotFalse($topbar);
        $this->assertStringContainsString('header.sticky.top-0', $css);
        $this->assertStringContainsString('pointer-events: none;', $css);
        $this->assertStringContainsString('pointer-events: auto;', $css);
        $this->assertStringContainsString('pointer-events-none', $topbar);
        $this->assertStringContainsString('pointer-events-auto', $topbar);
    }

    public function test_arabic_locale_translates_notification_titles_and_work_models(): void
    {
        $this->withUnencryptedCookie(SetFrontendLocale::COOKIE, 'ar')
            ->get(route('candidate.notifications.index'))
            ->assertOk()
            ->assertSee('تم تأكيد مقابلة المحفظة', false)
            ->assertSee('رسالة جديدة من Olivia Bennett', false);

        $this->withUnencryptedCookie(SetFrontendLocale::COOKIE, 'ar')
            ->get(route('jobs.index'))
            ->assertOk()
            ->assertSee('هجين', false)
            ->assertSee('عن بُعد', false)
            ->assertSee('في الموقع', false);
    }

    public function test_frame51_alert_and_empty_state_are_used_in_production_views(): void
    {
        $submitted = file_get_contents(resource_path('views/candidate/applications/submitted.blade.php'));
        $offers = file_get_contents(resource_path('views/candidate/offers/index.blade.php'));
        $saved = file_get_contents(resource_path('views/candidate/jobs/saved.blade.php'));

        $this->assertNotFalse($submitted);
        $this->assertNotFalse($offers);
        $this->assertNotFalse($saved);
        $this->assertStringContainsString('<x-alert', $submitted);
        $this->assertStringContainsString('<x-empty-state', $offers);
        $this->assertStringContainsString('<x-alert', $saved);
    }

    public function test_company_schedule_instructions_are_localized(): void
    {
        $html = $this->withUnencryptedCookie(SetFrontendLocale::COOKIE, 'ar')
            ->get(route('company.interviews.schedule', ['applicant' => 'maya-chen']))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('شارك تفاصيل التحضير قبل كل مقابلة', $html);
    }
}

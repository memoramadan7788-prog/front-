<?php

namespace Tests\Feature;

use App\Http\Middleware\SetFrontendLocale;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class UxBatch5ClosureTest extends TestCase
{
    public function test_sticky_layouts_expose_scroll_clearance_hooks(): void
    {
        $company = $this->get(route('company.jobs.index'));
        $company->assertOk();
        $company->assertSee('data-mobile-sidebar', false);
        $company->assertSee('inert', false);
        $company->assertSee('aria-hidden="true"', false);
        $company->assertSee('sticky top-0 z-30', false);
        $company->assertSee('isolate', false);

        $public = $this->get(route('jobs.show', 'ns-204'));
        $public->assertOk();
        $public->assertSee('sticky top-0 z-40 isolate', false);
        $public->assertSee('data-public-apply', false);
    }

    public function test_closed_mobile_drawer_is_inert_in_app_layout(): void
    {
        $response = $this->get(route('candidate.dashboard'));

        $response->assertOk();
        $response->assertSee('data-mobile-sidebar', false);
        $response->assertSee('data-mobile-sidebar-toggle', false);
        $this->assertMatchesRegularExpression(
            '/data-mobile-sidebar[\s\S]*?\binert\b/',
            $response->getContent()
        );
        $this->assertMatchesRegularExpression(
            '/data-mobile-sidebar[\s\S]*?aria-hidden="true"/',
            $response->getContent()
        );
    }

    public function test_public_save_job_reuses_candidate_saved_job_contract(): void
    {
        $response = $this->get(route('jobs.show', 'ns-204'));

        $response->assertOk();
        $response->assertSee('data-save-job="ns-204"', false);
        $response->assertSee('data-save-label', false);
        $response->assertSee('data-save-live', false);
        $response->assertDontSee('disabled title="'.__('common.soon').'"', false);
        $response->assertSee(__('public.jobs.save_job'), false);

        $this->get(route('candidate.jobs.saved'))
            ->assertOk()
            ->assertSee('data-candidate-jobs-mode="saved"', false)
            ->assertSee('data-job-id="ns-204"', false);
    }

    public function test_public_and_candidate_job_identifiers_align_for_ns_204(): void
    {
        $this->get(route('jobs.show', 'ns-204'))
            ->assertOk()
            ->assertSee('NS-204', false)
            ->assertSee('Senior Product Designer', false)
            ->assertSee(route('candidate.applications.apply', 'ns-204'), false);

        $this->get(route('candidate.jobs.index'))
            ->assertOk()
            ->assertSee('data-job-id="ns-204"', false)
            ->assertSee('data-save-job="ns-204"', false);

        $this->get(route('company.jobs.show', 'ns-204'))
            ->assertOk()
            ->assertSee('NS-204', false)
            ->assertSee('Senior Product Designer', false);
    }

    public function test_company_recruitment_entity_continuity_routes_remain(): void
    {
        $this->get(route('company.jobs.show', 'ns-204'))->assertOk();
        $this->get(route('company.applicants.index', ['job' => 'ns-204']))
            ->assertOk()
            ->assertSee('Maya Chen', false);
        $this->get(route('company.applicants.show', 'maya-chen'))
            ->assertOk()
            ->assertSee('Maya Chen', false)
            ->assertSee('NS-204', false);
        $this->get(route('company.interviews.index'))->assertOk();
        $this->get(route('company.evaluations.show', 'eval-ethan-cole'))->assertOk();
        $this->get(route('company.offers.create', ['offer' => 'of-2026-021']))
            ->assertOk()
            ->assertSee('Ethan Cole', false);
    }

    public function test_company_messages_remain_honestly_pending(): void
    {
        $response = $this->get(route('company.dashboard'));

        $response->assertOk();
        $response->assertSee(__('navigation.company.messages'), false);
        $response->assertSee(__('common.soon'), false);
        $this->assertFalse(Route::has('company.messages.index'));
    }

    public function test_arabic_public_job_detail_localizes_save_and_timeline(): void
    {
        $this->withUnencryptedCookie(SetFrontendLocale::COOKIE, 'ar')
            ->get(route('jobs.show', 'ns-204'))
            ->assertOk()
            ->assertSee('حفظ الوظيفة', false)
            ->assertSee('تم التقديم', false)
            ->assertSee('الفرز', false)
            ->assertDontSee('>Applied<', false);
    }

    public function test_critical_named_routes_remain_registered(): void
    {
        foreach ([
            'home',
            'jobs.index',
            'jobs.show',
            'login',
            'register',
            'onboarding.candidate',
            'onboarding.company',
            'candidate.dashboard',
            'candidate.jobs.index',
            'candidate.jobs.saved',
            'candidate.applications.index',
            'candidate.applications.apply',
            'candidate.messages.index',
            'candidate.interviews.index',
            'company.dashboard',
            'company.jobs.index',
            'company.jobs.show',
            'company.applicants.index',
            'company.applicants.show',
            'company.interviews.index',
            'company.evaluations.show',
            'company.offers.create',
            'company.employees.index',
            'company.employees.requests',
            'company.analytics.index',
            'company.assessments.index',
            'company.talent-pool.index',
        ] as $name) {
            $this->assertTrue(
                Route::has($name),
                "Missing named route: {$name}"
            );
        }
    }
}

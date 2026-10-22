<?php

namespace Tests\Feature;

use App\Support\Frontend\BreadcrumbNavigator;
use Tests\TestCase;

class BreadcrumbNavigationTest extends TestCase
{
    public function test_strip_frame_number_removes_leading_digits(): void
    {
        $this->assertSame(
            'Company workspace / Overview',
            BreadcrumbNavigator::stripFrameNumber('25 / Company workspace / Overview'),
        );
        $this->assertSame(
            'My workspace / Overview',
            BreadcrumbNavigator::stripFrameNumber('13 / My workspace / Overview'),
        );
        $this->assertSame(
            'Company workspace / Overview',
            BreadcrumbNavigator::stripFrameNumber('Company workspace / Overview'),
        );
    }

    public function test_candidate_dashboard_breadcrumb_has_no_frame_number(): void
    {
        $this->get(route('candidate.dashboard'))
            ->assertOk()
            ->assertSee('My workspace', false)
            ->assertSee('Overview', false)
            ->assertDontSee('13 / My workspace', false)
            ->assertSee('aria-label="Breadcrumb"', false)
            ->assertSee('aria-current="page"', false);
    }

    public function test_company_dashboard_breadcrumb_has_no_frame_number(): void
    {
        $this->get(route('company.dashboard'))
            ->assertOk()
            ->assertSee('Company workspace', false)
            ->assertSee('Overview', false)
            ->assertDontSee('25 / Company workspace', false)
            ->assertSee(route('company.dashboard'), false);
    }

    public function test_candidate_offers_show_uses_offers_breadcrumb_label(): void
    {
        $this->get(route('candidate.offers.show', ['offer' => 'offer-ms108']))
            ->assertOk()
            ->assertSee('Offers', false)
            ->assertDontSee('My workspace / My applications', false);
    }

    public function test_candidate_interviews_show_uses_localized_breadcrumb(): void
    {
        $this->get(route('candidate.interviews.show', ['interview' => 'int-ns204-portfolio']))
            ->assertOk()
            ->assertSee('My workspace', false)
            ->assertSee('Interviews', false)
            ->assertDontSee('20 / My workspace / Interviews', false);
    }

    public function test_topbar_profile_settings_links_by_role(): void
    {
        $this->get(route('candidate.dashboard'))
            ->assertOk()
            ->assertSee(route('candidate.profile'), false)
            ->assertSee('Profile settings', false);

        $this->get(route('company.dashboard'))
            ->assertOk()
            ->assertSee(route('company.profile'), false)
            ->assertSee('Profile settings', false);
    }

    public function test_app_layout_includes_skip_link(): void
    {
        $this->get(route('candidate.dashboard'))
            ->assertOk()
            ->assertSee('Skip to content', false)
            ->assertSee('href="#main-content"', false);
    }

    public function test_shared_modal_close_aria_is_localized(): void
    {
        $this->get(route('candidate.interviews.index'))
            ->assertOk()
            ->assertSee('aria-label="Close dialog"', false)
            ->assertSee('data-hb-modal-focus-trap', false);
    }

    public function test_candidate_breadcrumb_parent_links_to_workspace(): void
    {
        $segments = BreadcrumbNavigator::segments('My workspace / Interviews', 'candidate');

        $this->assertCount(2, $segments);
        $this->assertSame('My workspace', $segments[0]['label']);
        $this->assertSame(route('candidate.dashboard'), $segments[0]['href']);
        $this->assertFalse($segments[0]['current']);
        $this->assertSame('Interviews', $segments[1]['label']);
        $this->assertNull($segments[1]['href']);
        $this->assertTrue($segments[1]['current']);
    }
}

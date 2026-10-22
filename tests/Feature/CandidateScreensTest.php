<?php

namespace Tests\Feature;

use Tests\TestCase;

class CandidateScreensTest extends TestCase
{
    public function test_candidate_dashboard_matches_figma_thirteen(): void
    {
        $this->get(route('candidate.dashboard'))
            ->assertOk()
            ->assertSee('My workspace', false)
            ->assertSee('Overview', false)
            ->assertDontSee('13 / My workspace', false)
            ->assertSee('Good morning, Maya', false)
            ->assertSee('Here’s what’s next in your search', false)
            ->assertSee('Discover jobs', false)
            ->assertSee('Active applications', false)
            ->assertSee('Saved roles', false)
            ->assertSee('Profile strength', false)
            ->assertSee('85%', false)
            ->assertSee('Portfolio interview', false)
            ->assertSee('Northstar Labs', false)
            ->assertSee('NS-204', false)
            ->assertSee('Confirmed · 45 minutes', false)
            ->assertSee('Alex Morgan · Interviewer · Product Design', false)
            ->assertSee('Your Northstar application', false)
            ->assertSee('Olivia Bennett confirmed your portfolio review', false)
            ->assertSee('Recommended for you', false)
            ->assertSee('Senior UX Designer', false)
            ->assertSee('AH-312', false)
            ->assertSee('Complete your profile', false)
            ->assertSee('Finish profile', false)
            ->assertSee('Your next actions', false)
            ->assertSee('Review Meridian offer', false)
            ->assertDontSee('Frontend UI only', false)
            ->assertDontSee('Offer accepted', false);
    }

    public function test_candidate_profile_matches_figma_twenty_two(): void
    {
        $this->get(route('candidate.profile'))
            ->assertOk()
            ->assertSee('My workspace', false)
            ->assertSee('Profile &amp; resume', false)
            ->assertDontSee('22 / My workspace', false)
            ->assertSee('Your experience, in your own words', false)
            ->assertSee('Preview profile', false)
            ->assertSee('Maya Chen', false)
            ->assertSee('Senior Product Designer', false)
            ->assertSee('Austin, Texas', false)
            ->assertSee('Headline', false)
            ->assertSee('About you', false)
            ->assertSee('https://mayachen.design', false)
            ->assertSee('Save profile', false)
            ->assertSee('Experience', false)
            ->assertSee('Fieldwork', false)
            ->assertSee('Parcel Systems', false)
            ->assertSee('March 2022–Present', false)
            ->assertSee('Add experience', false)
            ->assertSee('Education &amp; skills', false)
            ->assertSee('Texas State University', false)
            ->assertSee('BFA, Communication Design', false)
            ->assertSee('Figma', false)
            ->assertSee('Design systems', false)
            ->assertSee('Edit education &amp; skills', false)
            ->assertSee('Current resume', false)
            ->assertSee('Maya_Chen_Resume.pdf', false)
            ->assertSee('248 KB · Updated Oct 10, 2026', false)
            ->assertSee('Replace resume', false)
            ->assertSee('Download PDF', false)
            ->assertSee('Visibility &amp; consent', false)
            ->assertSee('Only employers I apply to can view my profile', false)
            ->assertSee('Allow agency recruiters to request access', false)
            ->assertSee('Show my profile in public search', false)
            ->assertSee('Save visibility', false)
            ->assertDontSee('Frontend UI only', false)
            ->assertDontSee('Profile saved', false);
    }

    public function test_candidate_sidebar_wires_overview_and_profile(): void
    {
        $dashboard = $this->get(route('candidate.dashboard'));
        $dashboard->assertOk();
        $dashboard->assertSee(route('candidate.dashboard'), false);
        $dashboard->assertSee(route('candidate.profile'), false);
        $dashboard->assertSee(route('candidate.jobs.index'), false);
        $dashboard->assertSee(route('candidate.jobs.saved'), false);

        $profile = $this->get(route('candidate.profile'));
        $profile->assertOk();
        $profile->assertSee(route('candidate.dashboard'), false);
        $profile->assertSee('aria-current="page"', false);
    }

    public function test_candidate_routes_are_get_only(): void
    {
        $this->get(route('candidate.dashboard'))->assertOk();
        $this->get(route('candidate.profile'))->assertOk();
        $this->post('/candidate/dashboard')->assertMethodNotAllowed();
        $this->post('/candidate/profile')->assertMethodNotAllowed();
    }

    public function test_prior_routes_remain(): void
    {
        $this->get(route('onboarding.candidate'))->assertOk();
        $this->get(route('login'))->assertOk();
        $this->get(route('home'))->assertOk();
        $this->get(route('jobs.index'))->assertOk();
    }
}

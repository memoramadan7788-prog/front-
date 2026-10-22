<?php

namespace Tests\Feature;

use Tests\TestCase;

class OnboardingScreensTest extends TestCase
{
    public function test_candidate_onboarding_matches_figma_eleven_app_shell(): void
    {
        $this->get(route('onboarding.candidate'))
            ->assertOk()
            ->assertSee('Build a profile that feels like you', false)
            ->assertSee('My workspace', false)
            ->assertSee('Profile &amp; resume', false)
            ->assertDontSee('11 / My workspace', false)
            ->assertSee('Candidate onboarding', false)
            ->assertSee('Basics', false)
            ->assertSee('Professional headline', false)
            ->assertSee('Skills &amp; preferences', false)
            ->assertSee('What are you looking for?', false)
            ->assertSee('Preferred roles', false)
            ->assertSee('Hybrid + Remote', false)
            ->assertSee('Product design · Figma · Design systems · User research', false)
            ->assertSee('$140,000 / year', false)
            ->assertSee('I’m open to recruiter-supported opportunities', false)
            ->assertSee('Continue', false)
            ->assertSee('Your profile so far', false)
            ->assertSee('25% complete', false)
            ->assertSee('Maya Chen', false)
            ->assertSee('You’re in control', false)
            ->assertSee('Salary preferences aren’t shown publicly', false)
            ->assertDontSee('11 /', false)
            ->assertDontSee('profile data is not saved', false)
            ->assertDontSee('Profile saved', false);
    }

    public function test_company_onboarding_matches_figma_twelve_app_shell(): void
    {
        $this->get(route('onboarding.company'))
            ->assertOk()
            ->assertSee('Set up your hiring workspace', false)
            ->assertSee('Company workspace', false)
            ->assertSee('Overview', false)
            ->assertDontSee('12 / Company workspace', false)
            ->assertSee('Company onboarding', false)
            ->assertSee('Company identity', false)
            ->assertSee('Legal company name', false)
            ->assertSee('Workspace details', false)
            ->assertSee('Company display name', false)
            ->assertSee('Workspace URL', false)
            ->assertSee('hirebridge.example/', false)
            ->assertSee('I’m authorized to create and manage this company workspace', false)
            ->assertSee('Workspace preview', false)
            ->assertSee('Setup in progress', false)
            ->assertSee('Up next', false)
            ->assertSee('Save &amp; continue', false)
            ->assertSee('Olivia Bennett', false)
            ->assertSee('Job publishing becomes available after company review', false)
            ->assertDontSee('12 /', false)
            ->assertDontSee('companies are not registered or approved', false)
            ->assertDontSee('Company approved', false);
    }

    public function test_company_status_preview_does_not_claim_approval(): void
    {
        $this->get(route('onboarding.company.status'))
            ->assertOk()
            ->assertSee('Pending review', false)
            ->assertSee('waiting on document review', false)
            ->assertDontSee('Company approved', false);
    }

    public function test_register_links_to_onboarding_previews(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee(route('onboarding.candidate'), false)
            ->assertSee(route('onboarding.company'), false);
    }

    public function test_onboarding_routes_are_get_only(): void
    {
        $this->get(route('onboarding.candidate'))->assertOk();
        $this->get(route('onboarding.company'))->assertOk();
        $this->post('/onboarding/candidate')->assertMethodNotAllowed();
        $this->post('/onboarding/company')->assertMethodNotAllowed();
    }

    public function test_prior_auth_and_public_routes_remain(): void
    {
        $this->get(route('login'))->assertOk();
        $this->get(route('home'))->assertOk();
        $this->get(route('jobs.index'))->assertOk();
    }
}

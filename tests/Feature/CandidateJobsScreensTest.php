<?php

namespace Tests\Feature;

use Tests\TestCase;

class CandidateJobsScreensTest extends TestCase
{
    public function test_candidate_job_search_matches_frame_fourteen_structure(): void
    {
        $this->get(route('candidate.jobs.index'))
            ->assertOk()
            ->assertSee('My workspace', false)
            ->assertSee('Discover jobs', false)
            ->assertDontSee('14 / My workspace', false)
            ->assertSee('Work that fits your next chapter', false)
            ->assertSee('Recommendations based on your design experience', false)
            ->assertSee('Edit preferences', false)
            ->assertSee('Senior product design', false)
            ->assertSee('Austin + Remote', false)
            ->assertSee('Recommended', false)
            ->assertSee('Save this search', false)
            ->assertSee('Your preferences', false)
            ->assertSee('Hybrid · Austin', false)
            ->assertSee('$140k+ USD', false)
            ->assertSee('Design systems', false)
            ->assertSee('12 recommended roles', false)
            ->assertSee('Staff Product Designer', false)
            ->assertSee('Grove &amp; Co.', false)
            ->assertSee('GC-041', false)
            ->assertSee('Review &amp; apply →', false)
            ->assertSee('Why it fits:', false)
            ->assertSee('Senior UX Designer', false)
            ->assertSee('AH-312', false)
            ->assertSee('View application →', false)
            ->assertSee('Application in Screening', false)
            ->assertSee('Design Systems Designer', false)
            ->assertSee('LF-087', false)
            ->assertSee('Already Applied', false)
            ->assertSee('Help us improve your recommendations', false)
            ->assertSee('Update preferences', false)
            ->assertDontSee('Frontend UI only', false)
            ->assertDontSee('Application submitted', false);
    }

    public function test_candidate_saved_jobs_matches_frame_fifteen_structure(): void
    {
        $this->get(route('candidate.jobs.saved'))
            ->assertOk()
            ->assertSee('My workspace', false)
            ->assertSee('Saved jobs', false)
            ->assertDontSee('15 / My workspace', false)
            ->assertSee('A considered shortlist', false)
            ->assertSee('Discover more roles', false)
            ->assertSee('Search saved roles', false)
            ->assertSee('All work models', false)
            ->assertSee('Recently saved', false)
            ->assertSee('Two roles have updates', false)
            ->assertSee('Atlas Health added interview details', false)
            ->assertSee('Senior UX Designer', false)
            ->assertSee('AH-312', false)
            ->assertSee('Oct 18 · Applied', false)
            ->assertSee('Design Systems Designer', false)
            ->assertSee('LF-087', false)
            ->assertSee('Staff Product Designer', false)
            ->assertSee('GC-041', false)
            ->assertSee('Oct 14 · Open', false)
            ->assertSee('Remove', false)
            ->assertSee('Keep a shortlist that works for you', false)
            ->assertSee('Your saved search', false)
            ->assertSee('Weekly email · Enabled', false)
            ->assertSee('Manage saved-search alerts', false)
            ->assertDontSee('Frontend UI only', false)
            ->assertDontSee('Job saved to your account', false);
    }

    public function test_candidate_jobs_routes_are_get_only(): void
    {
        $this->get(route('candidate.jobs.index'))->assertOk();
        $this->get(route('candidate.jobs.saved'))->assertOk();
        $this->post('/candidate/jobs')->assertMethodNotAllowed();
        $this->post('/candidate/jobs/saved')->assertMethodNotAllowed();
    }

    public function test_sidebar_and_prior_candidate_routes_remain(): void
    {
        $this->get(route('candidate.dashboard'))
            ->assertOk()
            ->assertSee(route('candidate.jobs.index'), false);

        $this->get(route('candidate.profile'))->assertOk();
        $this->get(route('onboarding.candidate'))->assertOk();
        $this->get(route('jobs.index'))->assertOk();
    }
}

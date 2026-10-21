<?php

namespace Tests\Feature;

use Tests\TestCase;

class CandidateApplicationsScreensTest extends TestCase
{
    public function test_apply_page_matches_frame_sixteen_structure(): void
    {
        $this->get(route('candidate.applications.apply', 'ns-204'))
            ->assertOk()
            ->assertSee('16 / My workspace / My applications', false)
            ->assertSee('Apply to Northstar Labs', false)
            ->assertSee('Senior Product Designer · NS-204', false)
            ->assertSee('You started this application on October 12, 2026', false)
            ->assertSee('Your profile', false)
            ->assertSee('Screening questions', false)
            ->assertSee('Review &amp; submit', false)
            ->assertSee('Your materials', false)
            ->assertSee('Maya Chen', false)
            ->assertSee('maya.chen@example.com', false)
            ->assertSee('Maya_Chen_Resume.pdf', false)
            ->assertSee('Choose a different resume', false)
            ->assertSee('https://mayachen.design', false)
            ->assertSee('A few role-specific questions', false)
            ->assertSee('Can you work hybrid in Austin?', false)
            ->assertSee('What interests you about this role?', false)
            ->assertSee('Expected annual base salary', false)
            ->assertSee('Save draft', false)
            ->assertSee('Review application', false)
            ->assertSee('Role summary', false)
            ->assertSee('Olivia Bennett', false)
            ->assertSee('Before you submit', false)
            ->assertDontSee('Frontend UI only', false)
            ->assertSee('candidate-apply-modal', false)
            ->assertDontSee('Application was received by Hirebridge', false);
    }

    public function test_submitted_preview_matches_frame_seventeen_structure(): void
    {
        $this->get(route('candidate.applications.submitted', ['job' => 'ns-204']))
            ->assertOk()
            ->assertSee('17 / My workspace / My applications', false)
            ->assertSee('Your application is on its way', false)
            ->assertSee('Submitted to Northstar Labs', false)
            ->assertDontSee('Frontend confirmation preview only', false)
            ->assertDontSee('No real application was received', false)
            ->assertSee('HB-NS204-MC-1012', false)
            ->assertSee('Senior Product Designer · NS-204', false)
            ->assertSee('What happens next', false)
            ->assertSee('Track this application', false)
            ->assertSee('Keep your momentum', false)
            ->assertSee('Browse recommended jobs', false)
            ->assertSee(route('candidate.applications.show', 'hb-ns204-mc-1012'), false)
            ->assertSee(route('candidate.jobs.index'), false);
    }

    public function test_applications_index_matches_frame_eighteen_structure(): void
    {
        $this->get(route('candidate.applications.index'))
            ->assertOk()
            ->assertSee('18 / My workspace / My applications', false)
            ->assertSee('My applications', false)
            ->assertSee('Every conversation, document, and next step in one place.', false)
            ->assertSee('Active (4)', false)
            ->assertSee('Archived (1)', false)
            ->assertSee('Search role or company', false)
            ->assertSee('All stages', false)
            ->assertSee('Latest activity', false)
            ->assertSee('Senior Product Designer · Northstar Labs', false)
            ->assertSee('NS-204 · Direct hire · Applied Oct 12', false)
            ->assertSee('Portfolio interview', false)
            ->assertSee('View application', false)
            ->assertSee('Lead Product Designer · Meridian Studio · MS-108', false)
            ->assertSee('Senior UX Designer · Atlas Health · AH-312', false)
            ->assertSee('Design Systems Designer · Lumen Financial · LF-087', false)
            ->assertSee('Archived application', false)
            ->assertSee('Product Designer · Grove &amp; Co. · GC-039', false)
            ->assertSee('Withdrawn', false)
            ->assertDontSee('Frontend UI only', false);
    }

    public function test_application_detail_matches_frame_nineteen_structure(): void
    {
        $this->get(route('candidate.applications.show', 'hb-ns204-mc-1012'))
            ->assertOk()
            ->assertSee('19 / My workspace / My applications', false)
            ->assertSee('Senior Product Designer', false)
            ->assertSee('Northstar Labs · NS-204 · Application HB-NS204-MC-1012', false)
            ->assertSee('Message hiring team', false)
            ->assertSee('Overview', false)
            ->assertSee('Documents', false)
            ->assertSee('Activity', false)
            ->assertSee('Portfolio interview', false)
            ->assertSee('Thursday, October 22', false)
            ->assertSee('Alex Morgan', false)
            ->assertSee('Application activity', false)
            ->assertSee('Your hiring contacts', false)
            ->assertSee('Olivia Bennett', false)
            ->assertSee('Daniel Brooks', false)
            ->assertSee('Submitted materials', false)
            ->assertSee('Withdraw application', false)
            ->assertDontSee('Frontend UI only', false);
    }

    public function test_application_routes_are_get_only_and_resolvable(): void
    {
        $this->get(route('candidate.applications.index'))->assertOk();
        $this->get(route('candidate.applications.apply', 'gc-041'))->assertOk();
        $this->get(route('candidate.applications.submitted'))->assertOk();
        $this->get(route('candidate.applications.show', 'ah-312'))->assertOk();
        $this->get(route('candidate.applications.show', 'missing-id'))->assertNotFound();

        $this->post('/candidate/applications')->assertMethodNotAllowed();
        $this->post('/candidate/applications/submitted')->assertMethodNotAllowed();
    }

    public function test_sidebar_and_job_cards_link_into_applications(): void
    {
        $this->get(route('candidate.dashboard'))
            ->assertOk()
            ->assertSee(route('candidate.applications.index'), false)
            ->assertSee(route('candidate.applications.show', 'hb-ns204-mc-1012'), false);

        $this->get(route('candidate.jobs.index'))
            ->assertOk()
            ->assertSee(route('candidate.applications.apply', 'gc-041'), false)
            ->assertSee(route('candidate.applications.show', 'ah-312'), false)
            ->assertSee('Apply in modal', false)
            ->assertSee('candidate-apply-modal', false);

        $this->get(route('candidate.jobs.saved'))
            ->assertOk()
            ->assertSee(route('candidate.applications.show', 'ah-312'), false)
            ->assertSee(route('candidate.applications.apply', 'gc-041'), false);
    }
}

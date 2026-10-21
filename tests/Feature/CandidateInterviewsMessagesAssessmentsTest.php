<?php

namespace Tests\Feature;

use Tests\TestCase;

class CandidateInterviewsMessagesAssessmentsTest extends TestCase
{
    public function test_interviews_index_matches_frame_twenty_structure(): void
    {
        $this->get(route('candidate.interviews.index'))
            ->assertOk()
            ->assertSee('20 / My workspace / Interviews', false)
            ->assertSee('Your interviews', false)
            ->assertSee('All times shown in Central Time', false)
            ->assertSee('Upcoming (1)', false)
            ->assertSee('Past (1)', false)
            ->assertSee('October 19–23, 2026', false)
            ->assertSee('Week view · Central Time', false)
            ->assertSee('Portfolio interview', false)
            ->assertSee('Northstar Labs · Senior Product Designer · NS-204', false)
            ->assertSee('Confirmed · 45 minutes', false)
            ->assertSee('Thursday, October 22', false)
            ->assertSee('Alex Morgan', false)
            ->assertSee('View preparation', false)
            ->assertSee('Request reschedule', false)
            ->assertSee('Preparation checklist', false)
            ->assertSee('Review the role description', false)
            ->assertSee('Previous conversation', false)
            ->assertSee('Recruiter screening', false)
            ->assertDontSee('Frontend UI only', false);
    }

    public function test_interview_show_renders_detail(): void
    {
        $this->get(route('candidate.interviews.show', 'int-ns204-portfolio'))
            ->assertOk()
            ->assertSee('Portfolio interview', false)
            ->assertSee('View preparation', false)
            ->assertSee(route('candidate.applications.show', 'hb-ns204-mc-1012'), false);

        $this->get(route('candidate.interviews.show', 'missing'))->assertNotFound();
    }

    public function test_messages_index_matches_frame_twenty_one_structure(): void
    {
        $this->get(route('candidate.messages.index'))
            ->assertOk()
            ->assertSee('21 / My workspace / Messages', false)
            ->assertSee('Messages', false)
            ->assertSee('Keep hiring conversations and useful context together.', false)
            ->assertSee('Inbox', false)
            ->assertSee('Search conversations', false)
            ->assertSee('Olivia Bennett', false)
            ->assertSee('Portfolio interview details', false)
            ->assertSee('Jamie Rivera', false)
            ->assertSee('Sam Wilson', false)
            ->assertSee('Northstar Labs · Senior Product Designer · NS-204', false)
            ->assertSee('Northstar_Portfolio_Guide.pdf', false)
            ->assertSee('Write your message', false)
            ->assertSee('Send message', false)
            ->assertSee('Application context', false)
            ->assertSee('View application', false)
            ->assertDontSee('Local preview only', false)
            ->assertDontSee('Frontend UI only', false);
    }

    public function test_assessments_follow_workflow_stubs(): void
    {
        $this->get(route('candidate.assessments.index'))
            ->assertOk()
            ->assertSee('Assessments', false)
            ->assertSee('Design systems practical', false)
            ->assertSee('UX research scenario', false)
            ->assertDontSee('No dedicated Figma frame', false)
            ->assertSee('Open instructions', false);

        $this->get(route('candidate.assessments.show', 'exam-ns204-systems'))
            ->assertOk()
            ->assertSee('Before you begin', false)
            ->assertSee('Start assessment', false)
            ->assertSee('Stay in a quiet space', false)
            ->assertDontSee('webcam stream is started', false);

        $this->get(route('candidate.assessments.take', 'exam-ns204-systems'))
            ->assertOk()
            ->assertSee('Time left', false)
            ->assertSee('Question 1', false)
            ->assertSee('Review &amp; submit', false);

        $this->get(route('candidate.assessments.result', 'exam-ns204-systems'))
            ->assertOk()
            ->assertSee('Assessment results', false)
            ->assertDontSee('Demonstration score only', false);

        $this->get(route('candidate.assessments.take', 'exam-ah312-research'))
            ->assertNotFound();
    }

    public function test_batch_four_d_routes_are_get_only(): void
    {
        $this->get(route('candidate.interviews.index'))->assertOk();
        $this->get(route('candidate.messages.index'))->assertOk();
        $this->get(route('candidate.assessments.index'))->assertOk();
        $this->post('/candidate/interviews')->assertMethodNotAllowed();
        $this->post('/candidate/messages')->assertMethodNotAllowed();
        $this->post('/candidate/assessments')->assertMethodNotAllowed();
    }

    public function test_sidebar_and_cross_links_remain(): void
    {
        $this->get(route('candidate.dashboard'))
            ->assertOk()
            ->assertSee(route('candidate.interviews.index'), false)
            ->assertSee(route('candidate.messages.index'), false)
            ->assertSee(route('candidate.assessments.index'), false);

        $this->get(route('candidate.applications.show', 'hb-ns204-mc-1012'))
            ->assertOk()
            ->assertSee(route('candidate.interviews.show', 'int-ns204-portfolio'), false);

        $this->get(route('candidate.applications.index'))->assertOk();
        $this->get(route('candidate.jobs.index'))->assertOk();
    }
}

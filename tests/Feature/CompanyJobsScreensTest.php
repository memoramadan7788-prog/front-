<?php

namespace Tests\Feature;

use Tests\TestCase;

class CompanyJobsScreensTest extends TestCase
{
    public function test_company_jobs_index_matches_frame_twenty_six(): void
    {
        $this->get(route('company.jobs.index'))
            ->assertOk()
            ->assertSee('Company workspace', false)
            ->assertSee('Jobs &amp; requisitions', false)
            ->assertDontSee('26 / Company workspace', false)
            ->assertSee('Jobs &amp; requisitions', false)
            ->assertSee('Manage ownership, publication, and hiring progress across Northstar Labs.', false)
            ->assertSee('Create job', false)
            ->assertSee('Open (5)', false)
            ->assertSee('Draft (1)', false)
            ->assertSee('Closed (2)', false)
            ->assertSee('All (8)', false)
            ->assertSee('Search title or requisition ID', false)
            ->assertSee('All owners', false)
            ->assertSee('All hiring models', false)
            ->assertSee('Draft awaiting review', false)
            ->assertSee('NS-207', false)
            ->assertSee('Customer Success Manager', false)
            ->assertSee('Continue editing', false)
            ->assertSee('Clear publication ownership', false)
            ->assertSee('Review permissions', false)
            ->assertDontSee('Frontend UI only', false)
            ->assertSee(route('company.jobs.create'), false);
    }

    public function test_company_job_create_matches_frame_twenty_seven(): void
    {
        $this->get(route('company.jobs.create', ['job' => 'ns-207']))
            ->assertOk()
            ->assertSee('Company workspace', false)
            ->assertSee('Jobs &amp; requisitions', false)
            ->assertDontSee('27 / Company workspace', false)
            ->assertSee('Create a job', false)
            ->assertSee('NS-207', false)
            ->assertSee('Customer Success Manager', false)
            ->assertSee('Role details', false)
            ->assertSee('Description', false)
            ->assertSee('Hiring process', false)
            ->assertSee('Review &amp; publish', false)
            ->assertSee('Job title', false)
            ->assertSee('Salary minimum - USD', false)
            ->assertSee('Publication checklist', false)
            ->assertSee('Draft · Not public', false)
            ->assertSee('Accessible job descriptions', false)
            ->assertSee('Save draft', false)
            ->assertSee('Continue to review', false)
            ->assertSee('Publish job', false)
            ->assertSee('hb_company_job_draft_ns-207', false)
            ->assertDontSee('Frontend UI only', false)
            ->assertDontSee('Job published successfully', false);
    }

    public function test_company_job_create_without_job_starts_new_draft(): void
    {
        $this->get(route('company.jobs.create'))
            ->assertOk()
            ->assertSee('Create a job', false)
            ->assertSee('hb_company_job_draft_new', false)
            ->assertSee('Role details', false)
            ->assertDontSee('Job published successfully', false);
    }

    public function test_continue_editing_preserves_draft_job_context(): void
    {
        $this->get(route('company.jobs.index'))
            ->assertOk()
            ->assertSee(route('company.jobs.create', ['job' => 'ns-207']), false);

        $this->get(route('company.jobs.show', 'ns-207'))
            ->assertOk()
            ->assertSee(route('company.jobs.create', ['job' => 'ns-207']), false);

        $this->get(route('company.jobs.create', ['job' => 'unknown-job']))
            ->assertNotFound();
    }

    public function test_jobs_pipeline_column_links_to_applicants(): void
    {
        $this->get(route('company.jobs.index'))
            ->assertOk()
            ->assertSee(route('company.applicants.index'), false)
            ->assertSee('pipelineUrl', false);

        $this->get(route('company.jobs.show', 'ns-204'))
            ->assertOk()
            ->assertSee(route('company.applicants.show', 'maya-chen'), false)
            ->assertSee(route('company.applicants.show', 'ethan-cole'), false)
            ->assertSee('/company/applicants?job=ns-204&amp;stage=Interview', false);
    }

    public function test_company_job_show_matches_frame_twenty_eight(): void
    {
        $this->get(route('company.jobs.show', 'ns-204'))
            ->assertOk()
            ->assertSee('Company workspace', false)
            ->assertSee('Jobs &amp; requisitions', false)
            ->assertDontSee('28 / Company workspace', false)
            ->assertSee('Senior Product Designer', false)
            ->assertSee('NS-204', false)
            ->assertSee('Product &amp; Design', false)
            ->assertSee('Austin hybrid', false)
            ->assertSee('$140k–$170k USD', false)
            ->assertSee('Olivia Bennett', false)
            ->assertSee('Published Oct 9', false)
            ->assertSee('View public job', false)
            ->assertSee('Edit job', false)
            ->assertSee('Overview', false)
            ->assertSee('Pipeline (12)', false)
            ->assertSee('Hiring team', false)
            ->assertSee('Publication', false)
            ->assertSee('Move the process forward', false)
            ->assertSee('Maya Chen', false)
            ->assertSee('Ethan Cole', false)
            ->assertSee('Open applicant pipeline', false)
            ->assertSee('Sourcing &amp; publication', false)
            ->assertSee('Stage expectations', false)
            ->assertSee('Daniel Brooks', false)
            ->assertSee('Alex Morgan', false)
            ->assertDontSee('Mock job', false)
            ->assertDontSee('Frame 28 PNG was not found', false)
            ->assertDontSee('Frontend UI only — company job details', false)
            ->assertDontSee('Frontend layout preview', false);
    }

    public function test_unknown_company_job_returns_not_found(): void
    {
        $this->get(route('company.jobs.show', 'ns-999'))->assertNotFound();
    }

    public function test_company_job_routes_are_get_only(): void
    {
        $this->get(route('company.jobs.index'))->assertOk();
        $this->get(route('company.jobs.create'))->assertOk();
        $this->get(route('company.jobs.show', 'ns-204'))->assertOk();
        $this->post('/company/jobs')->assertMethodNotAllowed();
        $this->post('/company/jobs/create')->assertMethodNotAllowed();
        $this->post('/company/jobs/ns-204')->assertMethodNotAllowed();
    }

    public function test_company_sidebar_wires_jobs_routes(): void
    {
        $response = $this->get(route('company.jobs.index'));
        $response->assertOk();
        $response->assertSee(route('company.dashboard'), false);
        $response->assertSee(route('company.jobs.index'), false);
        $response->assertSee(route('company.profile'), false);
        $response->assertSee('aria-current="page"', false);
    }

    public function test_dashboard_create_job_links_to_wizard(): void
    {
        $this->get(route('company.dashboard'))
            ->assertOk()
            ->assertSee(route('company.jobs.create'), false)
            ->assertSee(route('company.jobs.index'), false)
            ->assertDontSee('Create job arrives in Batch 5B', false);
    }

    public function test_candidate_jobs_still_work(): void
    {
        $this->get(route('candidate.jobs.index'))
            ->assertOk()
            ->assertSee('Work that fits your next chapter', false);
    }
}

<?php

namespace Tests\Feature;

use App\Http\Middleware\SetFrontendLocale;
use Tests\TestCase;

class CompanyApplicantsScreensTest extends TestCase
{
    public function test_company_applicants_pipeline_matches_frame_thirty(): void
    {
        $this->get(route('company.applicants.index'))
            ->assertOk()
            ->assertSee('30 / Company workspace / Applicants', false)
            ->assertSee('Applicant pipeline', false)
            ->assertSee('NS-204', false)
            ->assertSee('Senior Product Designer', false)
            ->assertSee('12 candidates', false)
            ->assertSee('Olivia Bennett', false)
            ->assertSee('Add candidate', false)
            ->assertSee('Board', false)
            ->assertSee('List', false)
            ->assertSee('Activity', false)
            ->assertSee('Search candidates or skills', false)
            ->assertSee('All sources', false)
            ->assertSee('Sofia Nguyen', false)
            ->assertSee('Maya Chen', false)
            ->assertSee('Ethan Cole', false)
            ->assertSee('Grace Liu', false)
            ->assertSee('Draft offer', false)
            ->assertSee('Hired Oct 16', false)
            ->assertSee('7 direct-hire', false)
            ->assertSee('5 agency-managed', false)
            ->assertSee('View closed outcomes', false)
            ->assertSee(route('company.applicants.show', 'maya-chen'), false)
            ->assertDontSee('Frontend UI only', false)
            ->assertDontSee('coming soon', false);
    }

    public function test_company_applicant_show_matches_frame_thirty_one(): void
    {
        $this->get(route('company.applicants.show', 'maya-chen'))
            ->assertOk()
            ->assertSee('31 / Company workspace / Applicants', false)
            ->assertSee('Maya Chen', false)
            ->assertSee('Senior Product Designer · Austin, TX · 8 years', false)
            ->assertSee('Interview', false)
            ->assertSee('Direct hire', false)
            ->assertSee('NS-204', false)
            ->assertSee('Message Maya', false)
            ->assertSee('Move stage', false)
            ->assertSee('Profile', false)
            ->assertSee('Interview plan', false)
            ->assertSee('Notes &amp; activity', false)
            ->assertSee('Communications', false)
            ->assertSee('Experience &amp; portfolio', false)
            ->assertSee('Fieldwork', false)
            ->assertSee('Parcel Systems', false)
            ->assertSee('View resume', false)
            ->assertSee('Open portfolio', false)
            ->assertSee('Screening history', false)
            ->assertSee('Portfolio review', false)
            ->assertSee('Alex Morgan', false)
            ->assertSee('maya.chen@example.com', false)
            ->assertSee('mayachen.design', false)
            ->assertSee(route('company.applicants.index', ['job' => 'ns-204']), false)
            ->assertDontSee('Frontend UI only', false);
    }

    public function test_unknown_applicant_returns_not_found(): void
    {
        $this->get(route('company.applicants.show', 'unknown-person'))->assertNotFound();
    }

    public function test_unknown_job_filter_returns_not_found(): void
    {
        $this->get(route('company.applicants.index', ['job' => 'ns-999']))->assertNotFound();
    }

    public function test_company_applicant_routes_are_get_only(): void
    {
        $this->get(route('company.applicants.index'))->assertOk();
        $this->get(route('company.applicants.show', 'maya-chen'))->assertOk();
        $this->post('/company/applicants')->assertMethodNotAllowed();
        $this->post('/company/applicants/maya-chen')->assertMethodNotAllowed();
    }

    public function test_company_sidebar_wires_applicants_routes(): void
    {
        $response = $this->get(route('company.applicants.index'));
        $response->assertOk();
        $response->assertSee(route('company.applicants.index'), false);
        $response->assertSee(route('company.dashboard'), false);
        $response->assertSee('aria-current="page"', false);
    }

    public function test_job_details_open_pipeline_links_to_applicants(): void
    {
        $this->get(route('company.jobs.show', 'ns-204'))
            ->assertOk()
            ->assertSee(route('company.applicants.index', ['job' => 'ns-204']), false)
            ->assertDontSee('Applicant pipeline coming soon', false);
    }

    public function test_dashboard_open_board_links_to_applicants(): void
    {
        $this->get(route('company.dashboard'))
            ->assertOk()
            ->assertSee(route('company.applicants.index', ['job' => 'ns-204']), false)
            ->assertSee('Open board', false)
            ->assertDontSee('Open board · Soon', false);
    }

    public function test_arabic_applicants_pipeline_renders(): void
    {
        $this->withUnencryptedCookie(SetFrontendLocale::COOKIE, 'ar')
            ->get(route('company.applicants.index'))
            ->assertOk()
            ->assertSee('مسار المتقدمين', false)
            ->assertSee('إضافة مرشح', false)
            ->assertSee('لوحة', false)
            ->assertSee('ابحث عن المرشحين أو المهارات', false);
    }

    public function test_arabic_applicant_profile_renders(): void
    {
        $this->withUnencryptedCookie(SetFrontendLocale::COOKIE, 'ar')
            ->get(route('company.applicants.show', 'maya-chen'))
            ->assertOk()
            ->assertSee('Maya Chen', false)
            ->assertSee('الملف', false)
            ->assertSee('خطة المقابلة', false)
            ->assertSee('نقل المرحلة', false)
            ->assertSee('العودة إلى المسار', false);
    }
}

<?php

namespace Tests\Feature;

use App\Http\Middleware\SetFrontendLocale;
use Tests\TestCase;

class CompanyAnalyticsAssessmentsScreensTest extends TestCase
{
    public function test_hiring_analytics_matches_frame_thirty_eight(): void
    {
        $this->get(route('company.analytics.index'))
            ->assertOk()
            ->assertSee('Company workspace', false)
            ->assertSee('Hiring analytics', false)
            ->assertDontSee('38 / Company workspace', false)
            ->assertSee('Hiring analytics', false)
            ->assertSee('Northstar Labs · Current cohort for open requisitions', false)
            ->assertSee('Export report', false)
            ->assertSee('Candidate records', false)
            ->assertSee('28', false)
            ->assertSee('Interview-stage share', false)
            ->assertSee('29%', false)
            ->assertSee('Median screening age', false)
            ->assertSee('Hired in cohort', false)
            ->assertSee('Grace Liu · NS-204', false)
            ->assertSee('Current stage distribution', false)
            ->assertSee('Time in current stage', false)
            ->assertSee('Source performance', false)
            ->assertSee('Direct hire', false)
            ->assertSee('Agency-managed', false)
            ->assertSee('Requisition breakdown', false)
            ->assertSee('NS-204 · Design', false)
            ->assertSee('NS-205 · Engineering', false)
            ->assertSee('NS-206 · Product', false)
            ->assertDontSee('Frontend UI only', false);
    }

    public function test_company_assessments_index_and_create(): void
    {
        $this->get(route('company.assessments.index'))
            ->assertOk()
            ->assertSee('Assessments', false)
            ->assertSee('Design systems practical', false)
            ->assertSee('Frontend reliability scenarios', false)
            ->assertSee('Create assessment', false);

        $this->get(route('company.assessments.create'))
            ->assertOk()
            ->assertSee('Create assessment', false)
            ->assertSee('Draft configuration only', false)
            ->assertSee('Save draft', false);
    }

    public function test_assessment_show_results_and_unknown_id(): void
    {
        $this->get(route('company.assessments.show', 'exam-ns204-systems'))
            ->assertOk()
            ->assertSee('Design systems practical', false)
            ->assertSee('Maya Chen', false)
            ->assertSee('Ethan Cole', false)
            ->assertSee(route('company.applicants.show', 'maya-chen'), false)
            ->assertSee(route('company.applicants.show', 'ethan-cole'), false);

        $this->get(route('company.assessments.results', 'exam-ns204-systems'))
            ->assertOk()
            ->assertSee('Ethan Cole', false)
            ->assertSee('82%', false)
            ->assertSee(route('company.applicants.show', 'ethan-cole'), false);

        $this->get(route('company.assessments.show', 'missing-assessment'))
            ->assertNotFound();
    }

    public function test_analytics_and_assessment_routes_are_get_only(): void
    {
        $this->get(route('company.analytics.index'))->assertOk();
        $this->get(route('company.assessments.index'))->assertOk();
        $this->post('/company/analytics')->assertMethodNotAllowed();
        $this->post('/company/assessments')->assertMethodNotAllowed();
    }

    public function test_sidebar_wires_analytics_and_assessments(): void
    {
        $this->get(route('company.analytics.index'))
            ->assertOk()
            ->assertSee(route('company.analytics.index'), false)
            ->assertSee('aria-current="page"', false);

        $this->get(route('company.assessments.index'))
            ->assertOk()
            ->assertSee(route('company.assessments.index'), false)
            ->assertSee('aria-current="page"', false);
    }

    public function test_arabic_analytics_and_assessments_render(): void
    {
        $this->withUnencryptedCookie(SetFrontendLocale::COOKIE, 'ar')
            ->get(route('company.analytics.index'))
            ->assertOk()
            ->assertSee('تحليلات التوظيف', false)
            ->assertSee('توزيع المرحلة الحالية', false);

        $this->withUnencryptedCookie(SetFrontendLocale::COOKIE, 'ar')
            ->get(route('company.assessments.index'))
            ->assertOk()
            ->assertSee('التقييمات', false)
            ->assertSee('إنشاء تقييم', false);
    }
}

<?php

namespace Tests\Feature;

use App\Http\Middleware\SetFrontendLocale;
use App\Support\Frontend\AiScreeningPresentation;
use App\Support\Frontend\MockCandidateData;
use App\Support\Frontend\MockCompanyData;
use Tests\TestCase;

class PhaseF9cAiAssessmentScreensTest extends TestCase
{
    public function test_ai_screening_contract_scenarios(): void
    {
        $high = AiScreeningPresentation::forApplicant('maya-chen');
        $this->assertSame(AiScreeningPresentation::RUN_COMPLETED, $high['run_status']);
        $this->assertSame('high', $high['match_band']);
        $this->assertFalse($high['below_threshold']);
        $this->assertTrue($high['is_sample']);

        $low = AiScreeningPresentation::forApplicant('ethan-cole');
        $this->assertSame('low', $low['match_band']);
        $this->assertTrue($low['below_threshold']);
        $this->assertSame(AiScreeningPresentation::HR_REQUIRED, $low['hr_review_status']);

        $failed = AiScreeningPresentation::forApplicant('noah-williams');
        $this->assertSame(AiScreeningPresentation::RUN_FAILED, $failed['run_status']);
        $this->assertSame(AiScreeningPresentation::HR_REQUIRED, $failed['hr_review_status']);
        $this->assertNull($failed['score']);

        $override = AiScreeningPresentation::forApplicant('ella-davis');
        $this->assertSame(AiScreeningPresentation::HR_COMPLETED, $override['hr_review_status']);
        $this->assertSame('override_advance', $override['human_decision']);
        $this->assertNotEmpty($override['override_reason']);
    }

    public function test_company_applicant_high_match_ai_panel(): void
    {
        $this->get(route('company.applicants.show', 'maya-chen'))
            ->assertOk()
            ->assertSee('data-ai-screening-panel', false)
            ->assertSee('data-run-status="completed"', false)
            ->assertSee('data-match-band="high"', false)
            ->assertSee('92%', false)
            ->assertSee('Sample match', false)
            ->assertDontSee('Job published successfully', false);
    }

    public function test_company_applicant_low_match_shows_manual_review_and_override_preview(): void
    {
        $this->get(route('company.applicants.show', 'ethan-cole'))
            ->assertOk()
            ->assertSee('data-match-band="low"', false)
            ->assertSee('data-ai-low-match', false)
            ->assertSee('data-open-modal="ai-override-modal"', false)
            ->assertSee('ai-override-modal', false)
            ->assertSee('Preview only', false)
            ->assertDontSee('automatically rejected', false);
    }

    public function test_company_applicant_ai_failed_is_not_rejection(): void
    {
        $this->get(route('company.applicants.show', 'noah-williams'))
            ->assertOk()
            ->assertSee('data-ai-failed', false)
            ->assertSee('data-run-status="failed"', false)
            ->assertSee('not a candidate rejection', false)
            ->assertSee('data-open-modal="ai-override-modal"', false)
            ->assertDontSee('Application rejected', false);
    }

    public function test_candidate_assessment_ready_and_attempt_summary(): void
    {
        $this->get(route('candidate.assessments.index'))
            ->assertOk()
            ->assertSee('Design systems practical', false)
            ->assertSee('exam-ns204-systems', false)
            ->assertSee('Attempt 1 of 2', false);

        $this->get(route('candidate.assessments.show', 'exam-ns204-systems'))
            ->assertOk()
            ->assertSee('data-assessment-intro', false)
            ->assertSee('data-assessment-camera-consent', false)
            ->assertSee('data-assessment-start', false)
            ->assertSee('presentation-only', false)
            ->assertDontSee('getUserMedia', false);
    }

    public function test_candidate_assessment_exhausted_blocks_start(): void
    {
        $this->get(route('candidate.assessments.show', ['assessment' => 'exam-ns204-systems', 'attempts' => 'exhausted']))
            ->assertOk()
            ->assertSee('data-attempts-exhausted', false)
            ->assertSee('data-start-blocked', false)
            ->assertDontSee('data-assessment-start', false)
            ->assertSee('No attempts remaining', false);
    }

    public function test_candidate_proctoring_consent_declined_presentation(): void
    {
        $this->get(route('candidate.assessments.show', ['assessment' => 'exam-ns204-systems', 'consent' => 'declined']))
            ->assertOk()
            ->assertSee('data-consent-declined', false)
            ->assertSee('data-start-blocked', false)
            ->assertSee('Proctoring consent declined', false)
            ->assertDontSee('getUserMedia', false);
    }

    public function test_candidate_flagged_attempt_is_not_cheating_verdict(): void
    {
        $this->get(route('candidate.assessments.show', 'exam-ns204-flagged'))
            ->assertOk()
            ->assertSee('data-proctoring-flagged', false)
            ->assertSee('not an automatic fail', false)
            ->assertDontSee('cheating detected', false)
            ->assertDontSee('You failed for cheating', false);
    }

    public function test_company_assessment_proctoring_queue_and_ai_approval_gate(): void
    {
        $this->get(route('company.assessments.show', 'exam-ns204-systems'))
            ->assertOk()
            ->assertSee('Proctoring review queue', false)
            ->assertSee('data-proctoring-flag="flag-ns204-1"', false)
            ->assertSee('Human review pending', false)
            ->assertSee('not automatic failures', false);

        $this->get(route('company.assessments.create'))
            ->assertOk()
            ->assertSee('data-question-source', false)
            ->assertSee('AI-assisted draft questions', false)
            ->assertSee('data-submit-for-approval', false)
            ->assertSee('require reviewer approval', false);
    }

    public function test_cross_role_sample_ids_stay_aligned(): void
    {
        $candidate = MockCandidateData::assessment('exam-ns204-systems');
        $this->assertNotNull($candidate);
        $this->assertSame('hb-ns204-mc-1012', $candidate['application_id']);
        $this->assertSame('ns-204', $candidate['job_id']);
        $this->assertSame(2, $candidate['attempt_limit']);

        $company = MockCompanyData::assessment('exam-ns204-systems');
        $this->assertNotNull($company);
        $this->assertSame('ns-204', $company['job_id']);
        $this->assertSame(2, $company['attempt_limit']);
        $this->assertSame(45, $company['time_limit_min']);
        $this->assertNotEmpty($company['proctoring_queue']);
        $this->assertSame('attempt-ns204-maya-flagged', $company['proctoring_queue'][0]['attempt_id']);

        $application = collect(MockCandidateData::applications())->firstWhere('id', 'hb-ns204-mc-1012');
        $this->assertSame('exam-ns204-systems', $application['assessment_id']);
        $this->assertSame(2, $application['assessment_attempt_limit']);

        $this->get(route('candidate.applications.show', 'hb-ns204-mc-1012'))
            ->assertOk()
            ->assertSee('data-linked-assessment', false)
            ->assertSee(route('candidate.assessments.show', 'exam-ns204-systems'), false);
    }

    public function test_arabic_locale_ai_and_assessment_strings(): void
    {
        $this->withUnencryptedCookie(SetFrontendLocale::COOKIE, 'ar')
            ->get(route('company.applicants.show', 'ethan-cole'))
            ->assertOk()
            ->assertSee('فرز السيرة بالذكاء الاصطناعي', false)
            ->assertSee('معاينة تجاوز الموارد البشرية', false);

        $this->withUnencryptedCookie(SetFrontendLocale::COOKIE, 'ar')
            ->get(route('candidate.assessments.show', 'exam-ns204-systems'))
            ->assertOk()
            ->assertSee('المراقبة والهوية', false)
            ->assertSee('موافقة الكاميرا', false);
    }

    public function test_f9a_f9b_regression_smoke(): void
    {
        $this->get(route('candidate.membership'))->assertOk();
        $this->get(route('jobs.index'))
            ->assertOk()
            ->assertDontSee('NS-208', false)
            ->assertDontSee('NS-209', false);
        $this->get(route('candidate.applications.apply', 'ns-204'))
            ->assertOk()
            ->assertSee('NS-204', false);
    }
}

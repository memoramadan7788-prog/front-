<?php

namespace Tests\Feature;

use App\Http\Middleware\SetFrontendLocale;
use App\Support\Frontend\JobAccessPresentation;
use App\Support\Frontend\MockCandidateData;
use App\Support\Frontend\MockPublicData;
use Tests\TestCase;

class PhaseF9bJobAccessScreensTest extends TestCase
{
    public function test_public_catalog_excludes_private_jobs(): void
    {
        $publicIds = array_column(MockPublicData::publicCatalog(), 'id');
        $this->assertContains('ns-204', $publicIds);
        $this->assertNotContains('ns-208', $publicIds);
        $this->assertNotContains('ns-209', $publicIds);

        $this->get(route('jobs.index'))
            ->assertOk()
            ->assertSee('NS-204', false)
            ->assertDontSee('NS-208', false)
            ->assertDontSee('NS-209', false)
            ->assertDontSee('Internal Design Systems Lead', false)
            ->assertDontSee('Nominated Product Designer', false);

        $this->get(route('jobs.show', 'ns-208'))->assertNotFound();
        $this->get(route('jobs.show', 'ns-209'))->assertNotFound();
    }

    public function test_public_ns204_apply_and_save_controls_remain(): void
    {
        $this->get(route('jobs.show', 'ns-204'))
            ->assertOk()
            ->assertSee('Senior Product Designer', false)
            ->assertSee('data-save-job="ns-204"', false)
            ->assertSee(route('candidate.applications.apply', 'ns-204'), false);
    }

    public function test_company_wizard_exposes_visibility_stages_and_requirements(): void
    {
        $this->get(route('company.jobs.create'))
            ->assertOk()
            ->assertSee('Visibility &amp; eligibility', false)
            ->assertSee('name="visibility"', false)
            ->assertSee('value="public"', false)
            ->assertSee('value="private"', false)
            ->assertSee('private_apply_mode', false)
            ->assertSee('nominated_only', false)
            ->assertSee('Recruitment stages', false)
            ->assertSee('stage_exam', false)
            ->assertSee('CV / resume required', false)
            ->assertSee('AI processing consent required', false)
            ->assertSee('Cover note required', false)
            ->assertSee('Category', false)
            ->assertSee('Openings', false)
            ->assertSee('Required', false)
            ->assertSee('data-review="visibility"', false)
            ->assertDontSee('Job published successfully', false);
    }

    public function test_company_job_show_presents_visibility_and_closed_reason(): void
    {
        $this->get(route('company.jobs.show', 'ns-204'))
            ->assertOk()
            ->assertSee('Public', false)
            ->assertDontSee('Nomination only', false);

        $this->get(route('company.jobs.show', 'ns-208'))
            ->assertOk()
            ->assertSee('Private', false)
            ->assertSee('Employees only', false)
            ->assertDontSee('View public job', false);

        $this->get(route('company.jobs.show', 'ns-209'))
            ->assertOk()
            ->assertSee('Nomination only', false);

        $this->get(route('company.jobs.show', 'ns-198'))
            ->assertOk()
            ->assertSee('Openings filled', false);
    }

    public function test_candidate_discover_eligibility_scenarios(): void
    {
        $this->get(route('candidate.jobs.index'))
            ->assertOk()
            ->assertSee('Eligibility demo scenarios', false)
            ->assertSee('NS-208', false)
            ->assertSee('NS-209', false)
            ->assertSee('Open membership', false);

        $this->get(route('candidate.jobs.index', ['membership' => 'approved']))
            ->assertOk()
            ->assertSee('data-job-id="ns-208"', false)
            ->assertSee('data-can-apply="1"', false)
            ->assertSee('nomination-only', false);

        $this->get(route('candidate.jobs.index', ['membership' => 'pending']))
            ->assertOk()
            ->assertSee('still pending review', false)
            ->assertSee(route('candidate.membership'), false);

        $this->get(route('candidate.jobs.index', ['membership' => 'approved', 'nomination' => 'yes']))
            ->assertOk()
            ->assertSee('data-job-id="ns-209"', false)
            ->assertSee(route('candidate.applications.apply', 'ns-209'), false);
    }

    public function test_private_apply_gates_and_nomination_context(): void
    {
        $this->get(route('candidate.applications.apply', 'ns-208'))
            ->assertNotFound();

        $this->get(route('candidate.applications.apply', ['job' => 'ns-208', 'membership' => 'approved']))
            ->assertOk()
            ->assertSee('Internal Design Systems Lead', false)
            ->assertSee('NS-208', false)
            ->assertSee('Private', false)
            ->assertSee('Employees only', false);

        $this->get(route('candidate.applications.apply', ['job' => 'ns-209', 'membership' => 'approved']))
            ->assertOk()
            ->assertSee('Application not available', false)
            ->assertSee('nomination-only', false)
            ->assertSee('You cannot continue this application', false);

        $this->get(route('candidate.applications.apply', [
            'job' => 'ns-209',
            'membership' => 'approved',
            'nomination' => 'yes',
        ]))
            ->assertOk()
            ->assertSee('Nominated Product Designer', false)
            ->assertSee('Cover note', false)
            ->assertSee('AI-assisted screening', false)
            ->assertDontSee('You cannot continue this application', false);
    }

    public function test_employee_cannot_apply_own_company_public_job_in_preview(): void
    {
        $this->get(route('candidate.applications.apply', [
            'job' => 'ns-204',
            'membership' => 'approved',
        ]))
            ->assertOk()
            ->assertSee('cannot apply to their own', false)
            ->assertSee('You cannot continue this application', false);
    }

    public function test_application_show_attempt_and_withdraw_states(): void
    {
        $this->get(route('candidate.applications.show', 'hb-ns204-mc-1012'))
            ->assertOk()
            ->assertSee('Application attempt', false)
            ->assertSee('1 of 1', false)
            ->assertSee('Reapplication', false)
            ->assertSee('Withdrawal is unavailable after the hiring team has started review', false);

        $this->get(route('candidate.applications.show', 'hb-ah312-mc-1018'))
            ->assertOk()
            ->assertSee('1 of 2', false)
            ->assertSee('preview withdrawal', false);
    }

    public function test_job_access_presentation_contract_helpers(): void
    {
        $public = JobAccessPresentation::normalizeJob([
            'id' => 'demo',
            'visibility' => 'public',
            'status_key' => 'open',
        ]);
        $this->assertSame('public', $public['visibility']);
        $this->assertSame('open', $public['application_mode']);

        $private = JobAccessPresentation::normalizeJob([
            'id' => 'demo-private',
            'organization_id' => 'org-ns-001',
            'visibility' => 'private',
            'private_apply_mode' => 'nominated_only',
            'status_key' => 'open',
        ]);
        $this->assertSame('nominated_only', $private['application_mode']);

        $approved = JobAccessPresentation::candidateContext('approved', 'no');
        $elig = JobAccessPresentation::eligibility($private, $approved);
        $this->assertTrue($elig['can_view']);
        $this->assertFalse($elig['can_apply']);
        $this->assertSame('nomination_required', $elig['reason_key']);

        $nominated = JobAccessPresentation::candidateContext('approved', 'yes');
        $eligYes = JobAccessPresentation::eligibility($private, $nominated);
        $this->assertTrue($eligYes['can_apply']);
    }

    public function test_arabic_locale_job_access_strings(): void
    {
        // Locale is cookie-based (SetFrontendLocale::COOKIE). Session key was a prior
        // F9B defect that caused this test to error / appear skipped in suite runs.
        $this->withUnencryptedCookie(SetFrontendLocale::COOKIE, 'ar')
            ->get(route('candidate.jobs.index'))
            ->assertOk()
            ->assertSee('سيناريوهات أهلية تجريبية', false)
            ->assertSee('خاصة', false);

        $this->withUnencryptedCookie(SetFrontendLocale::COOKIE, 'ar')
            ->get(route('company.jobs.create'))
            ->assertOk()
            ->assertSee('الظهور والأهلية', false)
            ->assertSee('مراحل التوظيف', false);
    }

    public function test_f9a_membership_regression_and_ns204_apply(): void
    {
        $this->get(route('candidate.membership'))->assertOk();
        $this->get(route('candidate.applications.apply', 'ns-204'))
            ->assertOk()
            ->assertSee('Northstar Labs', false)
            ->assertSee('NS-204', false);

        $this->assertNotEmpty(MockCandidateData::defaultSavedJobIds());
    }
}

<?php

namespace Tests\Feature;

use App\Http\Middleware\SetFrontendLocale;
use Tests\TestCase;

class PhaseF9aIdentityMembershipScreensTest extends TestCase
{
    public function test_candidate_membership_screen_and_demo_states(): void
    {
        $this->get(route('candidate.membership'))
            ->assertOk()
            ->assertSee('Company membership request', false)
            ->assertSee('local preview only', false)
            ->assertSee('Northstar Labs', false)
            ->assertSee('Do you currently work at a company', false)
            ->assertDontSee('Frontend UI only', false);

        $this->get(route('candidate.membership', ['membership' => 'pending']))
            ->assertOk()
            ->assertSee('Pending company review', false)
            ->assertSee('one company membership request at a time', false);

        $this->get(route('candidate.membership', ['membership' => 'rejected']))
            ->assertOk()
            ->assertSee('Rejection reason', false)
            ->assertSee('People Operations already has an assigned HR admin', false);

        $this->get(route('candidate.membership', ['membership' => 'approved']))
            ->assertOk()
            ->assertSee('Approved · Employee', false);
    }

    public function test_candidate_profile_and_onboarding_link_to_membership(): void
    {
        $this->get(route('candidate.profile'))
            ->assertOk()
            ->assertSee('Employee membership', false)
            ->assertSee(route('candidate.membership'), false);

        $this->get(route('onboarding.candidate'))
            ->assertOk()
            ->assertSee('Optional: company membership', false)
            ->assertSee(route('candidate.membership'), false);
    }

    public function test_membership_route_is_get_only(): void
    {
        $this->get(route('candidate.membership'))->assertOk();
        $this->post('/candidate/membership')->assertMethodNotAllowed();
    }

    public function test_pending_organization_gates_create_job_presentation(): void
    {
        $this->get(route('company.dashboard', ['org_status' => 'pending']))
            ->assertOk()
            ->assertSee('Organization pending Super Admin review', false)
            ->assertSee('Unavailable until approved', false)
            ->assertSee('aria-disabled="true"', false);

        $this->get(route('company.jobs.index', ['org_status' => 'pending']))
            ->assertOk()
            ->assertSee('Organization pending Super Admin review', false)
            ->assertSee('Creating jobs requires an approved organization', false);

        $this->get(route('company.jobs.create', ['org_status' => 'pending']))
            ->assertOk()
            ->assertSee('Publishing jobs requires an approved organization', false)
            ->assertSee('data-org-can-publish="0"', false);
    }

    public function test_approved_organization_keeps_create_job_available(): void
    {
        $this->get(route('company.dashboard'))
            ->assertOk()
            ->assertDontSee('Organization pending Super Admin review', false)
            ->assertSee(route('company.jobs.create'), false);
    }

    public function test_company_status_variants_are_honest_presentation(): void
    {
        $this->get(route('onboarding.company.status', ['status' => 'pending']))
            ->assertOk()
            ->assertSee('Pending review', false)
            ->assertSee('presentation mock only', false);

        $this->get(route('onboarding.company.status', ['status' => 'approved']))
            ->assertOk()
            ->assertSee('Organization approved', false)
            ->assertSee('Open company workspace', false);

        $this->get(route('onboarding.company.status', ['status' => 'rejected']))
            ->assertOk()
            ->assertSee('Organization rejected', false)
            ->assertSee('Business registration document could not be verified', false);

        $this->get(route('onboarding.company.status', ['status' => 'suspended']))
            ->assertOk()
            ->assertSee('Organization suspended', false);
    }

    public function test_verify_email_failed_and_resent_states(): void
    {
        $this->get(route('verification.notice', ['verify_state' => 'failed']))
            ->assertOk()
            ->assertSee('Verification could not complete', false)
            ->assertSee('email is not verified', false);

        $this->get(route('verification.notice', ['verify_state' => 'resent']))
            ->assertOk()
            ->assertSee('Resend previewed', false)
            ->assertSee('No email was delivered', false);
    }

    public function test_join_requests_include_maya_and_role_assignment_fields(): void
    {
        $this->get(route('company.employees.requests'))
            ->assertOk()
            ->assertSee('Maya Chen', false)
            ->assertSee('maya.chen@example.com', false)
            ->assertSee('Assign workspace role', false)
            ->assertSee('HR', false)
            ->assertSee('Interviewer', false)
            ->assertSee('Employee', false)
            ->assertSee('frontend previews only', false);
    }

    public function test_arabic_membership_and_org_banner_render(): void
    {
        $this->withUnencryptedCookie(SetFrontendLocale::COOKIE, 'ar')
            ->get(route('candidate.membership'))
            ->assertOk()
            ->assertSee('طلب عضوية الشركة', false)
            ->assertSee('معاينة محلية فقط', false);

        $this->withUnencryptedCookie(SetFrontendLocale::COOKIE, 'ar')
            ->get(route('company.dashboard', ['org_status' => 'pending']))
            ->assertOk()
            ->assertSee('المؤسسة بانتظار مراجعة المسؤول الأعلى', false);
    }

    public function test_admin_organization_directory_still_review_only(): void
    {
        $this->get(route('admin.organizations.index'))
            ->assertOk()
            ->assertSee('Organizations', false);

        $this->get(route('admin.organizations.show', 'org-ns-001'))
            ->assertOk()
            ->assertSee('Northstar Labs', false)
            ->assertSee('Administrative review', false);
    }
}

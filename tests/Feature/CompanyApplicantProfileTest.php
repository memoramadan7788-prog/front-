<?php

namespace Tests\Feature;

use App\Frontend\Auth\ApiSession;
use App\Models\CompanyAPI;
use Mockery;
use Tests\TestCase;

class CompanyApplicantProfileTest extends TestCase
{
    private $companyApi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->companyApi = Mockery::mock(CompanyAPI::class);
        $this->app->instance(CompanyAPI::class, $this->companyApi);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_guest_is_redirected_from_applicant_profile(): void
    {
        $this->get(route('frontend.company.applications.show', ['applicationId' => 31]))
            ->assertRedirect(route('frontend.account.login'));
    }

    public function test_company_sees_supported_application_fields_and_profile_tabs(): void
    {
        $this->actingAsCompany();
        $this->companyApi->shouldReceive('getApplication')->once()->with(31)->andReturn($this->applicationResponse());

        $html = $this->get(route('frontend.company.applications.show', ['applicationId' => 31]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Jordan Applicant', $html);
        $this->assertStringContainsString('Product Engineer', $html);
        $this->assertStringContainsString(__('company.applicant_profile.tabs.profile'), $html);
        $this->assertStringContainsString(__('company.applicant_profile.tabs.interview_plan'), $html);
        $this->assertStringContainsString(e(__('company.applicant_profile.tabs.notes_activity')), $html);
        $this->assertStringContainsString(e(__('company.applicant_profile.tabs.contact_consent')), $html);
    }

    public function test_interview_plan_uses_existing_scheduler(): void
    {
        $this->actingAsCompany();
        $this->companyApi->shouldReceive('getApplication')->once()->with(31)->andReturn($this->applicationResponse());

        $html = $this->get(route('frontend.company.applications.show', [
            'applicationId' => 31,
            'tab' => 'interview_plan',
        ]))->assertOk()->getContent();

        $this->assertStringContainsString(
            route('frontend.company.interviews.schedule', ['application_id' => 31], false),
            $html
        );
    }

    public function test_unsupported_notes_and_contact_tabs_state_the_contract_gap(): void
    {
        $this->actingAsCompany();
        $this->companyApi->shouldReceive('getApplication')->twice()->with(31)->andReturn($this->applicationResponse());

        $this->get(route('frontend.company.applications.show', [
            'applicationId' => 31,
            'tab' => 'notes_activity',
        ]))->assertOk()->assertSee(__('company.applicant_profile.notes_unavailable'));

        $this->get(route('frontend.company.applications.show', [
            'applicationId' => 31,
            'tab' => 'contact_consent',
        ]))->assertOk()->assertSee(__('company.applicant_profile.contact_consent_unavailable'));
    }

    public function test_invalid_profile_tab_is_rejected(): void
    {
        $this->actingAsCompany();

        $this->get(route('frontend.company.applications.show', [
            'applicationId' => 31,
            'tab' => 'consent_editor',
        ]))->assertSessionHasErrors('tab');
    }

    public function test_forbidden_profile_response_is_rendered(): void
    {
        $this->actingAsCompany();
        $this->companyApi->shouldReceive('getApplication')->once()->with(31)->andReturn([
            'statusCode' => 403,
            'message' => 'Forbidden',
            'data' => null,
        ]);

        $this->get(route('frontend.company.applications.show', ['applicationId' => 31]))
            ->assertOk()
            ->assertSee(__('company.applicant_profile.forbidden'));
    }

    public function test_backend_error_and_missing_application_are_handled(): void
    {
        $this->actingAsCompany();
        $this->companyApi->shouldReceive('getApplication')->once()->with(31)->andReturn([
            'statusCode' => 503,
            'message' => 'Unavailable',
            'data' => null,
        ]);

        $this->get(route('frontend.company.applications.show', ['applicationId' => 31]))
            ->assertOk()
            ->assertSee(__('company.applicant_profile.load_error'));

        $this->companyApi->shouldReceive('getApplication')->once()->with(404)->andReturn([
            'statusCode' => 404,
            'message' => 'Not found',
            'data' => null,
        ]);

        $this->get(route('frontend.company.applications.show', ['applicationId' => 404]))
            ->assertNotFound();
    }

    public function test_session_expiry_redirects_to_login(): void
    {
        $this->actingAsCompany();
        $this->companyApi->shouldReceive('getApplication')->once()->with(31)->andReturn([
            'statusCode' => 401,
            'message' => 'Unauthenticated',
            'data' => null,
        ]);

        $this->get(route('frontend.company.applications.show', ['applicationId' => 31]))
            ->assertRedirect(route('frontend.account.login'));
        $this->assertNull(ApiSession::token());
    }

    public function test_company_applications_list_links_to_profile(): void
    {
        $this->actingAsCompany();
        $this->companyApi->shouldReceive('getApplications')->once()->andReturn([
            'statusCode' => 200,
            'message' => 'OK',
            'data' => [[
                'id' => 31,
                'status' => 'screening',
                'candidate_name' => 'Jordan Applicant',
            ]],
        ]);

        $html = $this->get(route('frontend.company.vacancies.applications', ['vacancyId' => 8]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(
            route('frontend.company.applications.show', ['applicationId' => 31], false),
            $html
        );
    }

    public function test_company_talent_pool_route_explains_missing_contract_without_fake_data(): void
    {
        $this->actingAsCompany();

        $html = $this->get(route('frontend.company.talent-pool.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(__('company.talent_pool.unavailable_title'), $html);
        $this->assertStringContainsString(__('company.talent_pool.missing_prospect_api'), $html);
        $this->assertStringContainsString(__('company.talent_pool.missing_tags_api'), $html);
        $this->assertStringContainsString(__('company.talent_pool.missing_consent_fields'), $html);
        $this->assertStringNotContainsString('data-prospect-id', $html);
    }

    public function test_talent_pool_requires_company_authentication(): void
    {
        $this->get(route('frontend.company.talent-pool.index'))
            ->assertRedirect(route('frontend.account.login'));
    }

    public function test_applicant_profile_renders_arabic_labels(): void
    {
        app()->setLocale('ar');
        $this->actingAsCompany();
        $this->companyApi->shouldReceive('getApplication')->once()->with(31)->andReturn($this->applicationResponse());

        $this->get(route('frontend.company.applications.show', ['applicationId' => 31]))
            ->assertOk()
            ->assertSee(__('company.applicant_profile.tabs.profile'))
            ->assertSee(__('company.applicant_profile.tabs.contact_consent'));
    }

    private function actingAsCompany(): void
    {
        ApiSession::store('company-token', [
            'id' => 20,
            'email' => 'hr@example.test',
            'name' => 'HR User',
            'account_type' => 'company',
            'roles' => ['company_admin'],
            'company' => ['id' => 5],
        ]);
    }

    /** @return array{statusCode: int, message: string, data: array<string, mixed>} */
    private function applicationResponse(): array
    {
        return [
            'statusCode' => 200,
            'message' => 'OK',
            'data' => [
                'id' => 31,
                'status' => 'screening',
                'candidate_name' => 'Jordan Applicant',
                'job_title' => 'Product Engineer',
            ],
        ];
    }
}

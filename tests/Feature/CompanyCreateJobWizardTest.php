<?php

namespace Tests\Feature;

use App\Frontend\Auth\ApiSession;
use App\Frontend\Company\CompanyCreateJobController;
use App\Models\CompanyAPI;
use App\Models\LookupAPI;
use Mockery;
use Tests\TestCase;

class CompanyCreateJobWizardTest extends TestCase
{
    private $companyApi;

    private $lookupApi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->companyApi = Mockery::mock(CompanyAPI::class);
        $this->lookupApi = Mockery::mock(LookupAPI::class);
        $this->lookupApi->shouldReceive('getCategories')->andReturn([
            'statusCode' => 200,
            'message' => 'OK',
            'data' => [
                ['id' => 3, 'name' => 'Engineering'],
            ],
        ])->byDefault();

        $this->app->instance(CompanyAPI::class, $this->companyApi);
        $this->app->instance(LookupAPI::class, $this->lookupApi);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_guest_is_redirected_from_create_job(): void
    {
        $this->get(route('frontend.company.vacancies.create'))
            ->assertRedirect(route('frontend.account.login'));
    }

    public function test_candidate_cannot_open_create_job(): void
    {
        $this->actingAsCandidate();

        $this->get(route('frontend.company.vacancies.create'))->assertRedirect();
    }

    public function test_company_user_sees_wizard_step_one(): void
    {
        $this->actingAsCompany();

        $html = $this->get(route('frontend.company.vacancies.create'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('data-create-job-wizard', $html);
        $this->assertStringContainsString(__('company.jobs.role_details'), $html);
        $this->assertStringContainsString('Engineering', $html);
        $this->assertStringContainsString('name="title"', $html);
        $this->assertStringContainsString('data-step="1"', $html);
    }

    public function test_step_navigation_persists_role_fields_in_session(): void
    {
        $this->actingAsCompany();

        $this->from(route('frontend.company.vacancies.create', ['step' => 1]))
            ->post(route('frontend.company.vacancies.create.submit'), [
                'step' => 1,
                'wizard_action' => 'next',
                'title' => 'Senior Laravel Engineer',
                'category_id' => 3,
                'employment_type' => 'full_time',
                'work_mode' => 'hybrid',
                'location' => 'Cairo',
                'openings_count' => 2,
                'visibility' => 'public',
                'hiring_model' => 'direct',
            ])
            ->assertRedirect(route('frontend.company.vacancies.create', ['step' => 2]));

        $state = session(CompanyCreateJobController::SESSION_KEY);
        $this->assertSame('Senior Laravel Engineer', $state['fields']['title'] ?? null);
        $this->assertSame(2, (int) ($state['step'] ?? 0));
    }

    public function test_role_step_rejects_invalid_work_mode(): void
    {
        $this->actingAsCompany();

        $this->from(route('frontend.company.vacancies.create', ['step' => 1]))
            ->post(route('frontend.company.vacancies.create.submit'), [
                'step' => 1,
                'wizard_action' => 'next',
                'title' => 'Engineer',
                'employment_type' => 'full_time',
                'work_mode' => 'spaceship',
                'visibility' => 'public',
            ])
            ->assertSessionHasErrors('work_mode');

        $this->companyApi->shouldNotReceive('createVacancy');
    }

    public function test_description_step_requires_minimum_length(): void
    {
        $this->actingAsCompany();
        $this->seedWizardSession(['title' => 'Engineer', 'employment_type' => 'full_time', 'work_mode' => 'remote', 'visibility' => 'public']);

        $this->from(route('frontend.company.vacancies.create', ['step' => 2]))
            ->post(route('frontend.company.vacancies.create.submit'), [
                'step' => 2,
                'wizard_action' => 'next',
                'description' => 'Too short',
            ])
            ->assertSessionHasErrors('description');
    }

    public function test_hiring_step_shows_blocked_owner_controls_not_fake_selects(): void
    {
        $this->actingAsCompany();
        $this->seedWizardSession([
            'title' => 'Engineer',
            'employment_type' => 'full_time',
            'work_mode' => 'remote',
            'visibility' => 'public',
            'description' => str_repeat('Quality description text. ', 4),
        ]);

        $html = $this->get(route('frontend.company.vacancies.create', ['step' => 3]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('data-schema-blocked="ownership"', $html);
        $this->assertStringContainsString(e(__('company.jobs.ownership_blocked_title')), $html);
        $this->assertStringNotContainsString('name="requisition_owner_id"', $html);
        $this->assertStringNotContainsString('name="allow_agency_submissions"', $html);
        $this->assertStringContainsString('name="hiring_model"', $html);
    }

    public function test_preview_does_not_call_create_api(): void
    {
        $this->actingAsCompany();
        $this->seedWizardSession([
            'title' => 'Engineer',
            'category_id' => 3,
            'employment_type' => 'full_time',
            'work_mode' => 'remote',
            'visibility' => 'public',
            'description' => str_repeat('Quality description text. ', 4),
            'hiring_model' => 'direct',
        ]);

        $this->companyApi->shouldNotReceive('createVacancy');

        $html = $this->get(route('frontend.company.vacancies.create.preview'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('data-job-preview', $html);
        $this->assertStringContainsString('Engineer', $html);
        $this->assertStringContainsString(__('company.jobs.preview_no_mutation'), $html);
        $this->assertStringNotContainsString('{!!', $html);
    }

    public function test_save_draft_posts_persistable_payload_with_status_draft(): void
    {
        $this->actingAsCompany();
        $this->seedWizardSession($this->completeFields());

        $this->companyApi->shouldReceive('createVacancy')->once()->withArgs(function (array $payload) {
            $this->assertSame('draft', $payload['status']);
            $this->assertSame('Platform Engineer', $payload['title']);
            $this->assertSame('hybrid', $payload['work_mode']);
            $this->assertSame('direct', $payload['hiring_model']);
            $this->assertArrayNotHasKey('requisition_owner_id', $payload);
            $this->assertArrayNotHasKey('allow_agency_submissions', $payload);
            $this->assertArrayNotHasKey('publish_to_search', $payload);
            $this->assertStringContainsString('Build resilient APIs', $payload['description']);
            $this->assertStringContainsString('Requirements', $payload['description']);

            return true;
        })->andReturn(['statusCode' => 200, 'message' => 'OK', 'data' => ['id' => 99]]);

        $this->post(route('frontend.company.vacancies.create.submit'), array_merge($this->completeFields(), [
            'step' => 4,
            'wizard_action' => 'save_draft',
        ]))
            ->assertRedirect(route('frontend.company.vacancies.index'))
            ->assertSessionHas('success_key', 'company.jobs.flash_draft_saved');

        $this->assertNull(session(CompanyCreateJobController::SESSION_KEY));
    }

    public function test_publish_requires_checklist_and_posts_published_status(): void
    {
        $this->actingAsCompany();

        $this->post(route('frontend.company.vacancies.create.submit'), [
            'step' => 4,
            'wizard_action' => 'publish',
            'title' => 'Incomplete',
            'employment_type' => 'full_time',
            'work_mode' => 'remote',
            'visibility' => 'public',
            'description' => str_repeat('Enough text for description field. ', 3),
            'hiring_model' => 'direct',
            // missing category_id
        ])->assertRedirect(route('frontend.company.vacancies.create', ['step' => 4]))
            ->assertSessionHasErrors();

        $this->seedWizardSession($this->completeFields());
        $this->companyApi->shouldReceive('createVacancy')->once()->withArgs(function (array $payload) {
            return ($payload['status'] ?? null) === 'published';
        })->andReturn(['statusCode' => 200, 'message' => 'OK', 'data' => ['id' => 100]]);

        $this->post(route('frontend.company.vacancies.create.submit'), array_merge($this->completeFields(), [
            'step' => 4,
            'wizard_action' => 'publish',
        ]))->assertRedirect(route('frontend.company.vacancies.index'))
            ->assertSessionHas('success_key', 'company.jobs.flash_published');
    }

    public function test_backend_error_does_not_flash_success(): void
    {
        $this->actingAsCompany();
        $this->seedWizardSession($this->completeFields());

        $this->companyApi->shouldReceive('createVacancy')->once()->andReturn([
            'statusCode' => 500,
            'message' => 'Upstream failed',
            'data' => null,
            'errors' => null,
        ]);

        $this->post(route('frontend.company.vacancies.create.submit'), array_merge($this->completeFields(), [
            'step' => 4,
            'wizard_action' => 'save_draft',
        ]))->assertRedirect(route('frontend.company.vacancies.create', ['step' => 4]))
            ->assertSessionHasErrors()
            ->assertSessionMissing('success_key');
    }

    public function test_duplicate_submit_guard_attribute_present(): void
    {
        $this->actingAsCompany();

        $html = $this->get(route('frontend.company.vacancies.create'))->assertOk()->getContent();
        $this->assertStringContainsString('data-submit-once', $html);
    }

    public function test_unauthorized_create_clears_session_on_401(): void
    {
        $this->actingAsCompany();
        $this->seedWizardSession($this->completeFields());

        $this->companyApi->shouldReceive('createVacancy')->once()->andReturn([
            'statusCode' => 401,
            'message' => 'Unauthenticated',
            'data' => null,
        ]);

        $this->post(route('frontend.company.vacancies.create.submit'), array_merge($this->completeFields(), [
            'step' => 4,
            'wizard_action' => 'save_draft',
        ]))->assertRedirect(route('frontend.account.login'));

        $this->assertFalse(ApiSession::check());
    }

    public function test_arabic_rtl_wizard_labels(): void
    {
        $this->actingAsCompany();

        $html = $this->withUnencryptedCookie('hb_locale', 'ar')
            ->get(route('frontend.company.vacancies.create'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(__('company.jobs.create_title', [], 'ar'), $html);
        $this->assertMatchesRegularExpression('/\bdir=["\']rtl["\']/', $html);
    }

    public function test_review_step_lists_checklist_gaps(): void
    {
        $this->actingAsCompany();
        $this->seedWizardSession([
            'title' => 'Engineer',
            'employment_type' => 'full_time',
            'work_mode' => 'remote',
            'visibility' => 'public',
            'description' => str_repeat('Quality description text. ', 4),
            'hiring_model' => 'direct',
        ], step: 4);

        $html = $this->get(route('frontend.company.vacancies.create', ['step' => 4]))->assertOk()->getContent();
        $this->assertStringContainsString('data-publication-checklist', $html);
        $this->assertStringContainsString(__('company.jobs.publish_checklist_incomplete'), $html);
    }

    private function actingAsCompany(): void
    {
        ApiSession::store('co-token', [
            'id' => 20,
            'email' => 'hr@example.com',
            'name' => 'HR User',
            'account_type' => 'company',
            'roles' => ['company_admin'],
            'company' => ['id' => 5, 'name' => 'Acme'],
        ]);
    }

    private function actingAsCandidate(): void
    {
        ApiSession::store('cand-token', [
            'id' => 11,
            'email' => 'candidate@example.com',
            'name' => 'Candidate User',
            'account_type' => 'candidate',
            'roles' => ['candidate'],
        ]);
    }

    /** @param  array<string, mixed>  $fields */
    private function seedWizardSession(array $fields, int $step = 1): void
    {
        session([
            CompanyCreateJobController::SESSION_KEY => [
                'step' => $step,
                'fields' => $fields,
            ],
        ]);
    }

    /** @return array<string, mixed> */
    private function completeFields(): array
    {
        return [
            'title' => 'Platform Engineer',
            'category_id' => 3,
            'department' => 'Product',
            'employment_type' => 'full_time',
            'work_mode' => 'hybrid',
            'location' => 'Cairo',
            'openings_count' => 1,
            'visibility' => 'public',
            'description' => 'Build resilient APIs and hiring workflows for TalentBridge customers.',
            'requirements_text' => 'Laravel, MySQL, API design.',
            'benefits_text' => 'Health cover, remote stipend.',
            'hiring_model' => 'direct',
        ];
    }
}

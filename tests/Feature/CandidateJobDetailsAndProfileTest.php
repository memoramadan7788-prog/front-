<?php

namespace Tests\Feature;

use App\Frontend\Auth\ApiSession;
use App\Models\CandidateAPI;
use App\Models\JobAPI;
use Mockery;
use Tests\TestCase;

class CandidateJobDetailsAndProfileTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_public_job_details_remain_on_public_layout_for_guests(): void
    {
        $jobs = Mockery::mock(JobAPI::class);
        $jobs->shouldReceive('getJob')->once()->with('42')->andReturn([
            'message' => 'OK',
            'data' => [
                'id' => 42,
                'title' => 'Frontend Engineer',
                'location' => 'Remote',
                'work_mode' => 'remote',
                'employment_type' => 'full_time',
                'description' => 'Build great UIs.',
                'salary' => ['min' => 80000, 'max' => 100000, 'currency' => 'EGP', 'period' => 'year'],
                'company_overview' => 'A product studio building accessible tools.',
                'company' => [
                    'name' => 'Acme',
                    'industry' => 'Technology',
                ],
                'responsibilities' => ['Build accessible interfaces'],
                'requirements' => ['Experience with React'],
                'benefits' => ['Flexible work'],
                'skills' => ['React', 'Accessibility'],
                'hiring_contact' => [
                    'name' => 'Sam Recruiter',
                    'title' => 'Talent Partner',
                    'email' => 'hiring@example.com',
                ],
            ],
            'errors' => [],
            'statusCode' => 200,
        ]);
        $this->app->instance(JobAPI::class, $jobs);

        $response = $this->get(route('frontend.jobs.show', ['jobId' => 42]));

        $response->assertOk()
            ->assertSee('Frontend Engineer')
            ->assertSee(__('All jobs'))
            ->assertSee(__('Sign in to apply'))
            ->assertSee(__('jobs.details.salary_title'))
            ->assertSee('100,000 EGP / year')
            ->assertSee('A product studio building accessible tools.')
            ->assertSee('Build accessible interfaces')
            ->assertSee('Sam Recruiter')
            ->assertSee('hiring@example.com')
            ->assertSee(__('jobs.details.journey_hint'))
            ->assertSee('data-save-job', false)
            ->assertDontSee('data-sidebar-nav-toggle', false);

        $this->assertStringContainsString(
            'frontend.layouts.public',
            file_get_contents(resource_path('views/frontend/job/job-details.blade.php'))
        );
    }

    public function test_candidate_job_details_require_consent_before_opening_apply_form(): void
    {
        $this->actingAsCandidate();

        $jobs = Mockery::mock(JobAPI::class);
        $jobs->shouldReceive('getJob')->once()->with('42')->andReturn([
            'message' => 'OK',
            'data' => [
                'id' => 42,
                'title' => 'Frontend Engineer',
                'company_name' => 'Acme',
            ],
            'errors' => [],
            'statusCode' => 200,
        ]);
        $this->app->instance(JobAPI::class, $jobs);

        $response = $this->get(route('frontend.candidate.vacancies.show', ['vacancyId' => 42]));

        $response->assertOk()
            ->assertSee(__('jobs.details.consent_notice'))
            ->assertSee('name="consent"', false)
            ->assertSee('required', false)
            ->assertSee(route('frontend.candidate.vacancies.apply', ['vacancyId' => 42]), false);
    }

    public function test_candidate_job_details_use_workspace_shell_and_apply_link(): void
    {
        $this->actingAsCandidate();

        $jobs = Mockery::mock(JobAPI::class);
        $jobs->shouldReceive('getJob')->once()->with('42')->andReturn([
            'message' => 'OK',
            'data' => [
                'id' => 42,
                'title' => 'Frontend Engineer',
                'company' => ['name' => 'Acme'],
                'location' => 'Remote',
                'work_mode' => 'remote',
                'employment_type' => 'full_time',
                'description' => 'Build great UIs.',
            ],
            'errors' => [],
            'statusCode' => 200,
        ]);
        $this->app->instance(JobAPI::class, $jobs);

        $response = $this->get(route('frontend.candidate.vacancies.show', ['vacancyId' => 42]));

        $response->assertOk()
            ->assertSee('Frontend Engineer')
            ->assertSee(__('Discover jobs'))
            ->assertSee(__('Apply now'))
            ->assertDontSee(__('All jobs'))
            ->assertSee('data-sidebar-nav-toggle', false);

        $html = $response->getContent();
        $this->assertStringContainsString(
            route('frontend.candidate.vacancies.apply', ['vacancyId' => 42], false),
            $html
        );
        $this->assertStringContainsString(__('navigation.candidate.discover_jobs'), $html);
    }

    public function test_guest_cannot_open_candidate_job_details(): void
    {
        $this->get(route('frontend.candidate.vacancies.show', ['vacancyId' => 42]))
            ->assertRedirect(route('frontend.account.login'));
    }

    public function test_discover_jobs_view_links_to_candidate_job_details(): void
    {
        $this->actingAsCandidate();

        $api = Mockery::mock(CandidateAPI::class);
        $api->shouldReceive('getVacancies')->once()->andReturn([
            'message' => 'OK',
            'data' => [
                [
                    'id' => 7,
                    'title' => 'Designer',
                    'company' => ['name' => 'Studio'],
                    'location' => 'Cairo',
                    'work_mode' => 'hybrid',
                    'deadline' => '2026-12-01',
                ],
            ],
            'errors' => [],
            'statusCode' => 200,
        ]);
        $api->shouldReceive('getProfile')->once()->andReturn(['data' => [], 'statusCode' => 200]);
        $api->shouldReceive('getSkills')->once()->andReturn(['data' => [], 'statusCode' => 200]);
        $api->shouldReceive('getApplications')->once()->andReturn(['data' => [], 'statusCode' => 200]);
        $this->app->instance(CandidateAPI::class, $api);

        $html = $this->get(route('frontend.candidate.vacancies.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(
            route('frontend.candidate.vacancies.show', ['vacancyId' => 7], false),
            $html
        );
        $this->assertStringNotContainsString(
            route('frontend.jobs.show', ['jobId' => 7], false),
            $html
        );
        $this->assertStringContainsString(
            route('frontend.candidate.vacancies.apply', ['vacancyId' => 7], false),
            $html
        );
        $this->assertStringContainsString(__('Your preferences'), $html);
        $this->assertStringContainsString(__('Core skills'), $html);
        $this->assertStringContainsString(__('Why it fits'), $html);
        $this->assertStringContainsString(__('Save this search'), $html);
        $this->assertStringContainsString('data-hide-job', $html);
    }

    public function test_discover_jobs_uses_profile_skills_when_skills_api_route_is_missing(): void
    {
        $this->actingAsCandidate();

        $api = Mockery::mock(CandidateAPI::class);
        $api->shouldReceive('getVacancies')->once()->andReturn([
            'message' => 'OK',
            'data' => [
                [
                    'id' => 7,
                    'title' => 'PHP Engineer',
                    'location' => 'Remote',
                    'work_mode' => 'remote',
                    'skills' => [['id' => 10, 'name' => 'PHP']],
                ],
            ],
            'statusCode' => 200,
        ]);
        $api->shouldReceive('getProfile')->once()->andReturn([
            'message' => 'OK',
            'data' => ['skills' => [['id' => 10, 'name' => 'PHP']]],
            'statusCode' => 200,
        ]);
        $api->shouldReceive('getSkills')->once()->andReturn([
            'message' => 'Error: The route api/candidate/skills could not be found.',
            'data' => [],
            'statusCode' => 404,
        ]);
        $api->shouldReceive('getApplications')->once()->andReturn([
            'message' => 'OK',
            'data' => [],
            'statusCode' => 200,
        ]);
        $this->app->instance(CandidateAPI::class, $api);

        $this->get(route('frontend.candidate.vacancies.index'))
            ->assertOk()
            ->assertSee('PHP Engineer')
            ->assertSee('Skills are read from your profile because the dedicated skills endpoint is unavailable.')
            ->assertSee('Your profile matches 1 listed skill(s): PHP');
    }

    public function test_discover_jobs_always_uses_the_api_and_never_shows_sample_jobs(): void
    {
        $this->actingAsCandidate();
        $this->app['env'] = 'local';

        $api = Mockery::mock(CandidateAPI::class);
        $api->shouldReceive('getVacancies')->once()->andReturn([
            'message' => 'OK',
            'data' => [[
                'id' => 7,
                'title' => 'API Vacancy',
                'company_name' => 'Live Company',
                'location' => 'Cairo',
                'work_mode' => 'onsite',
                'deadline' => '2026-12-01',
            ]],
            'errors' => [],
            'statusCode' => 200,
        ]);
        $api->shouldReceive('getProfile')->once()->andReturn(['data' => [], 'statusCode' => 200]);
        $api->shouldReceive('getSkills')->once()->andReturn(['data' => [], 'statusCode' => 200]);
        $api->shouldReceive('getApplications')->once()->andReturn(['data' => [], 'statusCode' => 200]);
        $this->app->instance(CandidateAPI::class, $api);

        $response = $this->get(route('frontend.candidate.vacancies.index'));

        $response->assertOk()
            ->assertSee('API Vacancy')
            ->assertSee('Live Company')
            ->assertDontSee('Senior Full-Stack Developer')
            ->assertDontSee('Frontend Vue.js Developer')
            ->assertDontSee('Tech Solutions')
            ->assertDontSee('Digital Agency');
    }

    public function test_profile_hides_change_password_and_internal_ids(): void
    {
        $this->actingAsCandidate();

        $api = Mockery::mock(CandidateAPI::class);
        $api->shouldReceive('getProfile')->once()->andReturn([
            'message' => 'OK',
            'data' => [
                'id' => 99,
                'user_id' => 88,
                'photo_path' => '/secret/storage/photo.jpg',
                'name' => 'Sara Candidate',
                'email' => 'sara@example.com',
                'phone' => '+201000000000',
                'location' => 'Cairo',
                'headline' => 'Product designer',
                'bio' => 'I design products.',
                'years_experience' => 4,
                'has_no_experience' => false,
                'experiences' => [
                    [
                        'id' => 1,
                        'company_name' => 'Acme',
                        'job_title' => 'Designer',
                        'start_date' => '2022-01-01',
                        'end_date' => null,
                        'is_current' => true,
                        'description' => 'Shipped design systems.',
                    ],
                ],
                'educations' => [],
                'skills' => [],
                'cvs' => [],
            ],
            'errors' => [],
            'statusCode' => 200,
        ]);
        $this->app->instance(CandidateAPI::class, $api);

        $html = $this->get(route('frontend.candidate.profile'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(__('candidate.profile.section_personal'), $html);
        $this->assertStringContainsString(__('candidate.profile.section_summary'), $html);
        $this->assertStringContainsString(__('candidate.profile.section_experience'), $html);
        $this->assertStringContainsString(__('candidate.profile.section_education'), $html);
        $this->assertStringContainsString(__('candidate.profile.section_skills'), $html);
        $this->assertStringContainsString(__('candidate.profile.section_resume'), $html);
        $this->assertStringContainsString('Sara Candidate', $html);
        $this->assertStringContainsString('Product designer', $html);
        $this->assertStringContainsString('Acme', $html);
        $this->assertStringContainsString(__('candidate.profile.empty_education'), $html);
        $this->assertStringContainsString(__('candidate.profile.empty_skills'), $html);
        $this->assertStringContainsString(__('candidate.profile.empty_resume'), $html);
        $this->assertStringContainsString(__('common.account_security'), $html); // account dropdown
        $this->assertStringContainsString(route('frontend.account.change-password', [], false), $html);

        $this->assertStringNotContainsString(route('frontend.candidate.change-password', [], false), $html);
        $this->assertStringNotContainsString('/secret/storage/photo.jpg', $html);
        $this->assertStringNotContainsString('User Id', $html);
        $this->assertStringNotContainsString('Photo Path', $html);
    }

    private function actingAsCandidate(): void
    {
        ApiSession::store('cand-token', [
            'id' => 11,
            'email' => 'candidate@example.com',
            'name' => 'Candidate',
            'account_type' => 'candidate',
            'roles' => ['candidate'],
        ]);
    }
}

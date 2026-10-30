<?php

namespace Tests\Feature;

use App\Frontend\Auth\ApiSession;
use App\Models\CandidateAPI;
use App\Models\LookupAPI;
use Mockery;
use Tests\TestCase;

class CandidateSkillsTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_manage_page_shows_empty_assigned_and_catalog_options(): void
    {
        $this->actingAsCandidate();

        $candidates = Mockery::mock(CandidateAPI::class);
        $candidates->shouldReceive('getSkills')->once()->andReturn([
            'message' => 'OK',
            'data' => [],
            'errors' => null,
            'statusCode' => 200,
        ]);
        $this->app->instance(CandidateAPI::class, $candidates);

        $lookups = Mockery::mock(LookupAPI::class);
        $lookups->shouldReceive('getSkills')->once()->with('')->andReturn([
            'message' => 'OK',
            'data' => [
                ['id' => 10, 'name' => 'PHP', 'type' => 'technical'],
                ['id' => 11, 'name' => 'Laravel', 'type' => 'technical'],
            ],
            'errors' => [],
            'statusCode' => 200,
        ]);
        $this->app->instance(LookupAPI::class, $lookups);

        $html = $this->get(route('frontend.candidate.skills.add'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('data-skills-assigned-empty', $html);
        $this->assertStringContainsString(__('candidate.skills.empty_title'), $html);
        $this->assertStringContainsString('PHP', $html);
        $this->assertStringContainsString('Laravel', $html);
        $this->assertStringContainsString('data-local-skill-form', $html);
        $this->assertStringContainsString('role="combobox"', $html);
        $this->assertStringContainsString('aria-expanded="false"', $html);
        $this->assertStringContainsString('id="skill-options" role="listbox" class="absolute z-20 mt-1 hidden', $html);
    }

    public function test_manage_page_lists_existing_skills_and_filters_catalog(): void
    {
        $this->actingAsCandidate();

        $candidates = Mockery::mock(CandidateAPI::class);
        $candidates->shouldReceive('getSkills')->once()->andReturn([
            'message' => 'OK',
            'data' => [
                ['id' => 10, 'name' => 'PHP', 'type' => 'technical', 'level' => 'expert'],
            ],
            'errors' => null,
            'statusCode' => 200,
        ]);
        $this->app->instance(CandidateAPI::class, $candidates);

        $lookups = Mockery::mock(LookupAPI::class);
        $lookups->shouldReceive('getSkills')->once()->with('php')->andReturn([
            'message' => 'OK',
            'data' => [
                ['id' => 10, 'name' => 'PHP', 'type' => 'technical'],
                ['id' => 12, 'name' => 'PHPUnit', 'type' => 'technical'],
            ],
            'errors' => [],
            'statusCode' => 200,
        ]);
        $this->app->instance(LookupAPI::class, $lookups);

        $html = $this->get(route('frontend.candidate.skills.add', ['q' => 'php']))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('data-skills-assigned-list', $html);
        $this->assertStringContainsString('data-skill-id="10"', $html);
        $this->assertStringContainsString(__('candidate.skills.levels.expert'), $html);
        $this->assertStringContainsString('PHPUnit', $html);
        // Already assigned PHP should not appear as an add option value again.
        $this->assertStringNotContainsString('value="10"', $html);
        $this->assertStringContainsString('data-skill-id="12"', $html);
    }

    public function test_missing_skills_route_allows_local_add_when_profile_omits_skills(): void
    {
        $this->actingAsCandidate();

        $candidates = Mockery::mock(CandidateAPI::class);
        $candidates->shouldReceive('getSkills')->once()->andReturn([
            'message' => 'Not found',
            'data' => [],
            'errors' => null,
            'statusCode' => 404,
        ]);
        $candidates->shouldReceive('getProfile')->once()->andReturn([
            'message' => 'OK',
            'data' => ['name' => 'Skills Candidate'],
            'statusCode' => 200,
        ]);
        $this->app->instance(CandidateAPI::class, $candidates);

        $lookups = Mockery::mock(LookupAPI::class);
        $lookups->shouldReceive('getSkills')->once()->with('')->andReturn([
            'message' => 'OK',
            'data' => [['id' => 12, 'name' => 'PHPUnit', 'type' => 'technical']],
            'errors' => [],
            'statusCode' => 200,
        ]);
        $this->app->instance(LookupAPI::class, $lookups);

        $html = $this->get(route('frontend.candidate.skills.add'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('data-local-skill-form', $html);
        $this->assertStringContainsString('Adding skills here saves them in this browser only.', $html);
        $this->assertStringContainsString('data-skill-combobox', $html);
        $this->assertStringContainsString('data-add-custom-skill', $html);
        $this->assertStringContainsString('React', $html);
        $this->assertStringContainsString('Laravel', $html);
        $this->assertStringContainsString('data-skill-id="12"', $html);
        $this->assertStringNotContainsString('data-skills-add-disabled', $html);
    }

    public function test_missing_skills_and_profile_routes_still_allow_local_add(): void
    {
        $this->actingAsCandidate();

        $candidates = Mockery::mock(CandidateAPI::class);
        $candidates->shouldReceive('getSkills')->once()->andReturn([
            'message' => 'Not found',
            'data' => [],
            'errors' => null,
            'statusCode' => 404,
        ]);
        $candidates->shouldReceive('getProfile')->once()->andReturn([
            'message' => 'Service unavailable',
            'data' => null,
            'statusCode' => 503,
        ]);
        $this->app->instance(CandidateAPI::class, $candidates);

        $lookups = Mockery::mock(LookupAPI::class);
        $lookups->shouldReceive('getSkills')->once()->with('')->andReturn([
            'message' => 'OK',
            'data' => [['id' => 12, 'name' => 'PHPUnit', 'type' => 'technical']],
            'errors' => [],
            'statusCode' => 200,
        ]);
        $this->app->instance(LookupAPI::class, $lookups);

        $html = $this->get(route('frontend.candidate.skills.add'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('data-skills-local-fallback', $html);
        $this->assertStringContainsString('Your assigned skills could not be loaded.', $html);
        $this->assertStringContainsString('data-local-skill-form', $html);
        $this->assertStringContainsString('data-skill-id="12"', $html);
        $this->assertStringNotContainsString('data-skills-assigned-error', $html);
    }

    public function test_missing_assigned_skills_route_falls_back_to_profile_as_read_only(): void
    {
        $this->actingAsCandidate();

        $candidates = Mockery::mock(CandidateAPI::class);
        $candidates->shouldReceive('getSkills')->once()->andReturn([
            'message' => 'Error: The route api/candidate/skills could not be found.',
            'data' => [],
            'errors' => null,
            'statusCode' => 404,
        ]);
        $candidates->shouldReceive('getProfile')->once()->andReturn([
            'message' => 'OK',
            'data' => [
                'skills' => [
                    ['id' => 10, 'name' => 'PHP', 'type' => 'technical', 'level' => 'expert'],
                ],
            ],
            'statusCode' => 200,
        ]);
        $this->app->instance(CandidateAPI::class, $candidates);

        $lookups = Mockery::mock(LookupAPI::class);
        $lookups->shouldReceive('getSkills')->once()->with('')->andReturn([
            'message' => 'OK',
            'data' => [['id' => 12, 'name' => 'PHPUnit', 'type' => 'technical']],
            'errors' => [],
            'statusCode' => 200,
        ]);
        $this->app->instance(LookupAPI::class, $lookups);

        $html = $this->get(route('frontend.candidate.skills.add'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('PHP', $html);
        $this->assertStringContainsString('data-skills-profile-fallback', $html);
        $this->assertStringContainsString('data-local-skill-form', $html);
        $this->assertStringContainsString('Adding skills here saves them in this browser only.', $html);
        $this->assertStringContainsString('candidate-local-skills-v1', $html);
        $this->assertStringNotContainsString('data-skills-assigned-error', $html);
        $this->assertStringNotContainsString('data-skills-remove-form', $html);
        $this->assertStringNotContainsString('api/candidate/skills could not be found', $html);
    }

    public function test_add_skill_merges_via_sync_without_wiping_existing(): void
    {
        $this->actingAsCandidate();

        $candidates = Mockery::mock(CandidateAPI::class);
        $candidates->shouldReceive('getSkills')->once()->andReturn([
            'message' => 'OK',
            'data' => [
                ['id' => 10, 'name' => 'PHP', 'level' => 'expert'],
            ],
            'errors' => null,
            'statusCode' => 200,
        ]);
        $candidates->shouldReceive('syncSkills')
            ->once()
            ->with(Mockery::on(function (array $skills): bool {
                $ids = array_column($skills, 'skill_id');
                sort($ids);

                return $ids === [10, 12]
                    && collect($skills)->firstWhere('skill_id', 12)['level'] === 'intermediate'
                    && collect($skills)->firstWhere('skill_id', 10)['level'] === 'expert';
            }))
            ->andReturn([
                'message' => 'Candidate skills updated successfully.',
                'data' => [
                    ['id' => 10, 'name' => 'PHP', 'level' => 'expert'],
                    ['id' => 12, 'name' => 'PHPUnit', 'level' => 'intermediate'],
                ],
                'errors' => null,
                'statusCode' => 200,
            ]);
        $this->app->instance(CandidateAPI::class, $candidates);

        $this->from(route('frontend.candidate.skills.add'))
            ->post(route('frontend.candidate.skills.add.submit'), [
                'skill_id' => 12,
                'level' => 'intermediate',
            ])
            ->assertRedirect(route('frontend.candidate.skills.add'))
            ->assertSessionHas('success_key', 'flash.candidate.skill_added');
    }

    public function test_add_skill_syncs_multiple_catalog_skills_in_one_request(): void
    {
        $this->actingAsCandidate();

        $candidates = Mockery::mock(CandidateAPI::class);
        $candidates->shouldReceive('getSkills')->once()->andReturn([
            'message' => 'OK',
            'data' => [['id' => 10, 'name' => 'PHP', 'level' => 'expert']],
            'statusCode' => 200,
        ]);
        $candidates->shouldReceive('syncSkills')
            ->once()
            ->with(Mockery::on(function (array $skills): bool {
                $ids = array_column($skills, 'skill_id');
                sort($ids);

                return $ids === [10, 12, 13]
                    && collect($skills)->firstWhere('skill_id', 12)['level'] === 'advanced'
                    && collect($skills)->firstWhere('skill_id', 13)['level'] === 'advanced'
                    && collect($skills)->firstWhere('skill_id', 10)['level'] === 'expert';
            }))
            ->andReturn([
                'message' => 'Candidate skills updated successfully.',
                'data' => [],
                'statusCode' => 200,
            ]);
        $this->app->instance(CandidateAPI::class, $candidates);

        $this->post(route('frontend.candidate.skills.add.submit'), [
            'skill_ids' => [12, 13],
            'level' => 'advanced',
        ])
            ->assertRedirect(route('frontend.candidate.skills.add'))
            ->assertSessionHas('success_key', 'flash.candidate.skill_added');
    }

    public function test_remove_skill_syncs_remaining_list(): void
    {
        $this->actingAsCandidate();

        $candidates = Mockery::mock(CandidateAPI::class);
        $candidates->shouldReceive('getSkills')->once()->andReturn([
            'message' => 'OK',
            'data' => [
                ['id' => 10, 'name' => 'PHP', 'level' => 'expert'],
                ['id' => 12, 'name' => 'PHPUnit', 'level' => 'beginner'],
            ],
            'errors' => null,
            'statusCode' => 200,
        ]);
        $candidates->shouldReceive('syncSkills')
            ->once()
            ->with([['skill_id' => 12, 'level' => 'beginner']])
            ->andReturn([
                'message' => 'Candidate skills updated successfully.',
                'data' => [['id' => 12, 'name' => 'PHPUnit', 'level' => 'beginner']],
                'errors' => null,
                'statusCode' => 200,
            ]);
        $this->app->instance(CandidateAPI::class, $candidates);

        $this->post(route('frontend.candidate.skills.remove', ['skillId' => 10]))
            ->assertRedirect(route('frontend.candidate.skills.add'))
            ->assertSessionHas('success_key', 'flash.candidate.skill_removed');
    }

    public function test_add_requires_skill_id(): void
    {
        $this->actingAsCandidate();

        $candidates = Mockery::mock(CandidateAPI::class);
        $candidates->shouldNotReceive('getSkills');
        $candidates->shouldNotReceive('syncSkills');
        $this->app->instance(CandidateAPI::class, $candidates);

        $this->from(route('frontend.candidate.skills.add'))
            ->post(route('frontend.candidate.skills.add.submit'), [])
            ->assertRedirect(route('frontend.candidate.skills.add'))
            ->assertSessionHasErrors(['skill_id']);
    }

    public function test_add_maps_backend_422_errors(): void
    {
        $this->actingAsCandidate();

        $candidates = Mockery::mock(CandidateAPI::class);
        $candidates->shouldReceive('getSkills')->once()->andReturn([
            'message' => 'OK',
            'data' => [],
            'errors' => null,
            'statusCode' => 200,
        ]);
        $candidates->shouldReceive('syncSkills')->once()->andReturn([
            'message' => 'The given data was invalid.',
            'data' => null,
            'errors' => ['skills.0.skill_id' => ['The selected skill id is invalid.']],
            'statusCode' => 422,
        ]);
        $this->app->instance(CandidateAPI::class, $candidates);

        $this->from(route('frontend.candidate.skills.add'))
            ->post(route('frontend.candidate.skills.add.submit'), [
                'skill_id' => 999,
                'level' => 'expert',
            ])
            ->assertRedirect(route('frontend.candidate.skills.add'))
            ->assertSessionHasErrors();
    }

    public function test_skills_401_clears_session(): void
    {
        $this->actingAsCandidate();

        $candidates = Mockery::mock(CandidateAPI::class);
        $candidates->shouldReceive('getSkills')->once()->andReturn([
            'message' => 'Unauthenticated.',
            'data' => [],
            'errors' => null,
            'statusCode' => 401,
        ]);
        $this->app->instance(CandidateAPI::class, $candidates);

        $this->get(route('frontend.candidate.skills.add'))
            ->assertRedirect(route('frontend.account.login'));
        $this->assertFalse(ApiSession::check());
    }

    public function test_skills_403_keeps_session_and_redirects_workspace(): void
    {
        $this->actingAsCandidate();

        $candidates = Mockery::mock(CandidateAPI::class);
        $candidates->shouldReceive('getSkills')->once()->andReturn([
            'message' => 'Forbidden.',
            'data' => [],
            'errors' => null,
            'statusCode' => 403,
        ]);
        $this->app->instance(CandidateAPI::class, $candidates);

        $this->get(route('frontend.candidate.skills.add'))
            ->assertRedirect(route('frontend.workspace'));
        $this->assertTrue(ApiSession::check());
        $this->assertSame('cand-token', ApiSession::token());
    }

    public function test_company_cannot_access_candidate_skills_routes(): void
    {
        ApiSession::store('co-token', [
            'id' => 2,
            'email' => 'hr@example.com',
            'name' => 'HR',
            'account_type' => 'company',
        ]);

        $candidates = Mockery::mock(CandidateAPI::class);
        $candidates->shouldNotReceive('getSkills');
        $candidates->shouldNotReceive('syncSkills');
        $this->app->instance(CandidateAPI::class, $candidates);

        $this->get(route('frontend.candidate.skills.add'))
            ->assertRedirect();
    }

    public function test_profile_renders_skills_from_backend_payload(): void
    {
        $this->actingAsCandidate();

        $api = Mockery::mock(CandidateAPI::class);
        $api->shouldReceive('getProfile')->once()->andReturn([
            'message' => 'OK',
            'data' => [
                'id' => 1,
                'name' => 'Sara Candidate',
                'email' => 'sara@example.com',
                'headline' => 'Engineer',
                'bio' => 'Builds APIs',
                'phone' => '+20100',
                'location' => 'Cairo',
                'years_experience' => 3,
                'has_no_experience' => false,
                'experiences' => [],
                'educations' => [],
                'skills' => [
                    ['id' => 10, 'name' => 'PHP', 'type' => 'technical', 'level' => 'expert'],
                    ['id' => 11, 'name' => 'Laravel', 'type' => 'technical', 'level' => 'advanced'],
                ],
                'cvs' => [],
            ],
            'errors' => [],
            'statusCode' => 200,
        ]);
        $this->app->instance(CandidateAPI::class, $api);

        $html = $this->get(route('frontend.candidate.profile'))->assertOk()->getContent();

        $this->assertStringContainsString('data-profile-skills-list', $html);
        $this->assertStringContainsString('PHP', $html);
        $this->assertStringContainsString('Laravel', $html);
        $this->assertStringContainsString('expert', $html);
        $this->assertStringContainsString(__('candidate.profile.manage_skills'), $html);
        $this->assertStringNotContainsString('data-profile-skills-empty', $html);
    }

    public function test_arabic_skills_manage_copy(): void
    {
        $this->actingAsCandidate();

        $candidates = Mockery::mock(CandidateAPI::class);
        $candidates->shouldReceive('getSkills')->once()->andReturn([
            'message' => 'OK',
            'data' => [],
            'errors' => null,
            'statusCode' => 200,
        ]);
        $this->app->instance(CandidateAPI::class, $candidates);

        $lookups = Mockery::mock(LookupAPI::class);
        $lookups->shouldReceive('getSkills')->once()->andReturn([
            'message' => 'OK',
            'data' => [],
            'errors' => [],
            'statusCode' => 200,
        ]);
        $this->app->instance(LookupAPI::class, $lookups);

        $html = $this->withUnencryptedCookie('hb_locale', 'ar')
            ->get(route('frontend.candidate.skills.add'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(__('candidate.skills.manage_title', [], 'ar'), $html);
        $this->assertStringContainsString('dir="rtl"', $html);
    }

    private function actingAsCandidate(): void
    {
        ApiSession::store('cand-token', [
            'id' => 41,
            'email' => 'skills@example.com',
            'name' => 'Skills Candidate',
            'account_type' => 'candidate',
            'roles' => ['candidate'],
        ]);
    }
}

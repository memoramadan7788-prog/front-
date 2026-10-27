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
        $this->assertStringContainsString('data-skills-add-form', $html);
        $this->assertStringNotContainsString('data-skills-assigned-list', $html);
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
        $this->assertStringContainsString('value="12"', $html);
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

<?php

namespace Tests\Feature;

use App\Frontend\Auth\ApiSession;
use App\Models\CandidateAPI;
use App\Support\Frontend\CandidateSettings\CandidateSettingsGateway;
use Mockery;
use Tests\TestCase;

class CandidateOnboardingTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_onboarding_requires_candidate_authentication_and_renders_english_and_arabic(): void
    {
        $this->get(route('frontend.candidate.onboarding'))->assertRedirect(route('frontend.account.login'));
        $this->actingAsCandidate();
        $this->mockOnboardingReads();

        $this->get(route('frontend.candidate.onboarding'))->assertOk()->assertSee(__('onboarding.candidate.stepper.basics'));
        $this->withUnencryptedCookie('hb_locale', 'ar')->get(route('frontend.candidate.onboarding', ['step' => 3]))
            ->assertOk()->assertSee(__('onboarding.candidate.stepper.skills_preferences', [], 'ar'));
    }

    public function test_onboarding_validates_and_saves_basics_through_candidate_api(): void
    {
        $this->actingAsCandidate();
        $api = Mockery::mock(CandidateAPI::class);
        $api->shouldReceive('updateProfile')->once()->with(Mockery::on(fn (array $payload): bool => $payload['headline'] === 'Designer'))->andReturn(['statusCode' => 200]);
        $this->app->instance(CandidateAPI::class, $api);
        $this->post(route('frontend.candidate.onboarding.save'), ['step' => 1, 'headline' => 'Designer', 'years_experience' => -2])->assertSessionHasErrors('years_experience');
        $this->post(route('frontend.candidate.onboarding.save'), ['step' => 1, 'headline' => 'Designer'])->assertRedirect(route('frontend.candidate.onboarding', ['step' => 2]));
    }

    public function test_onboarding_backend_failure_returns_validation_error_without_success_flash(): void
    {
        $this->actingAsCandidate();
        $api = Mockery::mock(CandidateAPI::class);
        $api->shouldReceive('updateProfile')->once()->andReturn(['statusCode' => 503, 'errors' => ['error' => 'Unavailable']]);
        $this->app->instance(CandidateAPI::class, $api);

        $this->post(route('frontend.candidate.onboarding.save'), ['step' => 1, 'headline' => 'Designer'])
            ->assertRedirect()->assertSessionHasErrors()->assertSessionMissing('success_key');
    }

    private function mockOnboardingReads(): void
    {
        $api = Mockery::mock(CandidateAPI::class);
        $api->shouldReceive('getProfile')->andReturn(['statusCode' => 200, 'data' => ['headline' => 'Engineer', 'cvs' => [['id' => 1]]]]);
        $api->shouldReceive('getSkills')->andReturn(['statusCode' => 200, 'data' => []]);
        $this->app->instance(CandidateAPI::class, $api);
        $settings = Mockery::mock(CandidateSettingsGateway::class);
        $settings->shouldReceive('getPreferences')->andReturn(['statusCode' => 200, 'data' => ['desired_titles' => []]]);
        $this->app->instance(CandidateSettingsGateway::class, $settings);
    }

    private function actingAsCandidate(): void
    {
        ApiSession::store('candidate-token', ['id' => 14, 'email' => 'candidate@example.test', 'name' => 'Candidate', 'account_type' => 'candidate', 'roles' => ['candidate']]);
    }
}

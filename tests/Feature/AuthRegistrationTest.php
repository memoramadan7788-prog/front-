<?php

namespace Tests\Feature;

use App\Frontend\Auth\ApiSession;
use App\Models\AccountAPI;
use Mockery;
use Tests\TestCase;

class AuthRegistrationTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_registration_form_shows_account_choices_password_feedback_and_consent(): void
    {
        $this->get(route('frontend.account.register'))
            ->assertOk()
            ->assertSee(__('auth.register.looking_job'))
            ->assertSee(__('auth.register.hiring'))
            ->assertSee(__('auth.register.password_strength'))
            ->assertSee(__('auth.register.terms_of_service'))
            ->assertSee(__('auth.register.marketing'))
            ->assertSee('lg:grid-cols-2', false)
            ->assertSee('lg:h-svh', false)
            ->assertSee(__('auth.brand_headline'))
            ->assertSee('class="hidden overflow-hidden space-y-4', false)
            ->assertSee('aria-hidden="true"', false)
            ->assertSee('class="mt-5 space-y-4"', false)
            ->assertSee(__('auth.register.submit_candidate'))
            ->assertSee('transition-[max-height,opacity]', false);
    }

    public function test_company_registration_type_renders_company_fields_and_submit_label(): void
    {
        $this->get(route('frontend.account.register', ['type' => 'company']))
            ->assertOk()
            ->assertSee('class="overflow-hidden space-y-4 rounded-2xl', false)
            ->assertSee('id="company_name" name="company_name"', false)
            ->assertSee('autocomplete="organization" required="required"', false)
            ->assertSee('aria-hidden="false"', false)
            ->assertSee('data-expanded="true"', false)
            ->assertSee('data-submit-company="'.__('auth.register.submit_company').'"', false)
            ->assertSee(__('auth.register.submit_company'));
    }

    public function test_terms_acceptance_is_required_before_registration_api_is_called(): void
    {
        $accounts = Mockery::mock(AccountAPI::class);
        $accounts->shouldNotReceive('register');
        $this->app->instance(AccountAPI::class, $accounts);

        $this->from(route('frontend.account.register'))
            ->post(route('frontend.account.register.submit'), $this->validCandidateInput())
            ->assertRedirect(route('frontend.account.register'))
            ->assertSessionHasErrors([
                'terms_accepted' => __('auth.register.terms_required'),
            ]);
    }

    public function test_mismatched_passwords_do_not_reach_the_registration_api(): void
    {
        $accounts = Mockery::mock(AccountAPI::class);
        $accounts->shouldNotReceive('register');
        $this->app->instance(AccountAPI::class, $accounts);

        $this->from(route('frontend.account.register'))
            ->post(route('frontend.account.register.submit'), [
                ...$this->validCandidateInput(),
                'password_confirmation' => 'DifferentPass1!',
                'terms_accepted' => '1',
            ])
            ->assertRedirect(route('frontend.account.register'))
            ->assertSessionHasErrors('password');
    }

    public function test_company_registration_sends_consent_and_company_fields_to_api(): void
    {
        $accounts = Mockery::mock(AccountAPI::class);
        $accounts->shouldReceive('register')
            ->once()
            ->with(Mockery::on(function (array $payload): bool {
                return ($payload['account_type'] ?? null) === 'company'
                    && ($payload['terms_accepted'] ?? null) === true
                    && ($payload['marketing_opt_in'] ?? null) === true
                    && ($payload['company_name'] ?? null) === 'Acme Labs'
                    && ($payload['industry'] ?? null) === 'Technology'
                    && ($payload['website'] ?? null) === 'https://acme.example'
                    && ($payload['email'] ?? null) === 'hiring@acme.example';
            }))
            ->andReturn([
                'message' => 'Company registration submitted.',
                'data' => [
                    'user' => ['account_type' => 'company'],
                    'company' => ['id' => 12, 'name' => 'Acme Labs', 'status' => 'pending'],
                    'token' => 'company-token',
                ],
                'errors' => null,
                'statusCode' => 200,
            ]);
        $this->app->instance(AccountAPI::class, $accounts);

        $this->post(route('frontend.account.register.submit'), [
            ...$this->validCandidateInput(),
            'account_type' => 'company',
            'email' => 'hiring@acme.example',
            'company_name' => 'Acme Labs',
            'industry' => 'Technology',
            'website' => 'https://acme.example',
            'terms_accepted' => '1',
            'marketing_opt_in' => '1',
        ])
            ->assertRedirect(route('frontend.company.dashboard'))
            ->assertSessionHas('success_key', 'auth.flash.register_company_pending');
    }

    public function test_api_verification_requirement_opens_honest_pending_state_without_signing_user_in(): void
    {
        $accounts = Mockery::mock(AccountAPI::class);
        $accounts->shouldReceive('register')
            ->once()
            ->with(Mockery::on(function (array $payload): bool {
                return ($payload['account_type'] ?? null) === 'candidate'
                    && ($payload['terms_accepted'] ?? null) === true
                    && ($payload['marketing_opt_in'] ?? null) === false;
            }))
            ->andReturn([
                'message' => 'Registration submitted.',
                'data' => ['verification_required' => true],
                'errors' => null,
                'statusCode' => 200,
            ]);
        $this->app->instance(AccountAPI::class, $accounts);

        $this->post(route('frontend.account.register.submit'), [
            ...$this->validCandidateInput(),
            'terms_accepted' => '1',
        ])
            ->assertRedirect(route('frontend.account.verification'))
            ->assertSessionHas('pending_verification_email', 'candidate@example.test');

        $this->assertFalse(ApiSession::check());

        $this->get(route('frontend.account.verification'))
            ->assertOk()
            ->assertSee('candidate@example.test')
            ->assertSee(__('auth.register.verification_instructions'))
            ->assertDontSee(__('auth.verify.entry_title'));
    }

    public function test_candidate_registration_without_verification_flag_keeps_dashboard_redirect(): void
    {
        $accounts = Mockery::mock(AccountAPI::class);
        $accounts->shouldReceive('register')
            ->once()
            ->andReturn([
                'message' => 'Registration successful.',
                'data' => [
                    'user' => ['account_type' => 'candidate'],
                    'token' => 'candidate-token',
                ],
                'errors' => null,
                'statusCode' => 200,
            ]);
        $this->app->instance(AccountAPI::class, $accounts);

        $this->post(route('frontend.account.register.submit'), [
            ...$this->validCandidateInput(),
            'terms_accepted' => '1',
        ])
            ->assertRedirect(route('frontend.candidate.dashboard'))
            ->assertSessionHas('success_key', 'auth.flash.register_candidate');

        $this->assertSame('candidate-token', ApiSession::token());
    }

    public function test_verification_email_is_html_escaped(): void
    {
        $html = $this->withSession([
            'pending_verification_email' => "<script>alert('xss')</script>",
        ])->get(route('frontend.account.verification'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringNotContainsString("<script>alert('xss')</script>", $html);
    }

    public function test_registration_api_failure_does_not_flash_passwords_back_to_session(): void
    {
        $accounts = Mockery::mock(AccountAPI::class);
        $accounts->shouldReceive('register')->once()->andReturn([
            'message' => 'Registration failed.',
            'data' => null,
            'errors' => ['email' => 'That email is already in use.'],
            'statusCode' => 422,
        ]);
        $this->app->instance(AccountAPI::class, $accounts);

        $this->from(route('frontend.account.register'))
            ->post(route('frontend.account.register.submit'), [
                ...$this->validCandidateInput(),
                'terms_accepted' => '1',
            ])
            ->assertRedirect(route('frontend.account.register'))
            ->assertSessionHasErrors('email')
            ->assertSessionHas('_old_input', function (array $input): bool {
                return ($input['name'] ?? null) === 'Candidate Example'
                    && ! array_key_exists('password', $input)
                    && ! array_key_exists('password_confirmation', $input);
            });
    }

    private function validCandidateInput(): array
    {
        return [
            'account_type' => 'candidate',
            'name' => 'Candidate Example',
            'email' => 'candidate@example.test',
            'password' => 'CandidatePass1!',
            'password_confirmation' => 'CandidatePass1!',
        ];
    }
}

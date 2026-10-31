<?php

namespace Tests\Feature;

use App\Frontend\Auth\ApiSession;
use App\Models\ContactAPI;
use Illuminate\Support\Facades\App;
use Mockery;
use Tests\TestCase;

class PublicContactWorkflowsTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_guest_contact_page_has_only_general_contact_fields(): void
    {
        $this->get(route('frontend.contact'))
            ->assertOk()
            ->assertSee('name="name"', false)
            ->assertSee('name="email"', false)
            ->assertSee('name="subject"', false)
            ->assertSee('name="description"', false)
            ->assertSee('data-submit-loading', false)
            ->assertSee('button.disabled = true', false)
            ->assertDontSee('name="hiring_model"', false)
            ->assertSee(route('frontend.consultation'));
    }

    public function test_authenticated_contact_page_displays_session_identity_without_identity_inputs(): void
    {
        $this->loginAsUser();

        $this->get(route('frontend.contact'))
            ->assertOk()
            ->assertSee('Taylor Example')
            ->assertSee('taylor@example.test')
            ->assertDontSee('name="name"', false)
            ->assertDontSee('name="email"', false)
            ->assertSee('name="subject"', false)
            ->assertSee('name="description"', false);
    }

    public function test_general_contact_validation_and_guest_success_state(): void
    {
        $api = Mockery::mock(ContactAPI::class);
        $api->shouldReceive('submitContactMessage')->once()->with([
            'name' => 'Guest Example',
            'email' => 'guest@example.test',
            'title' => 'Account question',
            'description' => 'A general question',
        ], false)->andReturn(['statusCode' => 200, 'message' => 'Sent', 'errors' => []]);
        $this->app->instance(ContactAPI::class, $api);

        $this->from(route('frontend.contact'))
            ->post(route('frontend.contact.submit'), ['name' => '', 'email' => 'invalid', 'subject' => '', 'description' => ''])
            ->assertRedirect(route('frontend.contact'))
            ->assertSessionHasErrors(['name', 'email', 'subject', 'description'])
            ->assertSessionHas('_old_input.email', 'invalid');

        $response = $this->followingRedirects()
            ->from(route('frontend.contact'))
            ->post(route('frontend.contact.submit'), [
                'name' => 'Guest Example',
                'email' => 'guest@example.test',
                'subject' => 'Account question',
                'description' => 'A general question',
            ])->assertOk()
            ->assertSee(__('public.contact.success'));
        $this->assertSame(1, substr_count($response->getContent(), __('public.contact.success')));
    }

    public function test_authenticated_contact_uses_session_identity_not_submitted_identity(): void
    {
        $this->loginAsUser();
        $api = Mockery::mock(ContactAPI::class);
        $api->shouldReceive('submitContactMessage')->once()->with([
            'name' => 'Taylor Example',
            'email' => 'taylor@example.test',
            'title' => 'Profile question',
            'description' => 'A question from my account',
        ], true)->andReturn(['statusCode' => 200, 'message' => 'Sent', 'errors' => []]);
        $this->app->instance(ContactAPI::class, $api);

        $this->from(route('frontend.contact'))->post(route('frontend.contact.submit'), [
            'name' => 'Spoofed Name',
            'email' => 'spoof@example.test',
            'subject' => 'Profile question',
            'description' => 'A question from my account',
        ])->assertRedirect(route('frontend.contact'))
            ->assertSessionHas('contact_submitted', true)
            ->assertSessionMissing('_old_input.name')
            ->assertSessionMissing('_old_input.email');
    }

    public function test_authenticated_contact_requires_account_identity_from_session(): void
    {
        ApiSession::store('session-token', ['account_type' => 'candidate']);
        $api = Mockery::mock(ContactAPI::class);
        $api->shouldNotReceive('submitContactMessage');
        $this->app->instance(ContactAPI::class, $api);

        $this->from(route('frontend.contact'))->post(route('frontend.contact.submit'), [
            'subject' => 'Profile question',
            'description' => 'A question from my account',
        ])->assertRedirect(route('frontend.contact'))
            ->assertSessionHasErrors('error');
    }

    public function test_backend_validation_and_failure_are_reported_without_exposing_sensitive_errors(): void
    {
        $api = Mockery::mock(ContactAPI::class);
        $api->shouldReceive('submitContactMessage')->twice()->andReturn(...[
            [
                'statusCode' => 422,
                'message' => 'Validation failed.',
                'errors' => ['title' => ['Subject was rejected.'], 'description' => ['Description was rejected.']],
            ],
            [
                'statusCode' => 500,
                'message' => 'SQLSTATE[23000] private database failure',
                'errors' => [],
            ],
        ]);
        $this->app->instance(ContactAPI::class, $api);

        $this->from(route('frontend.contact'))->post(route('frontend.contact.submit'), [
            'name' => 'Guest Example',
            'email' => 'guest@example.test',
            'subject' => 'A subject',
            'description' => 'A message body',
        ])->assertRedirect(route('frontend.contact'))
            ->assertSessionHasErrors(['subject', 'description']);

        $response = $this->followingRedirects()->from(route('frontend.contact'))
            ->post(route('frontend.contact.submit'), [
                'name' => 'Guest Example',
                'email' => 'guest@example.test',
                'subject' => 'A subject',
                'description' => 'A message body',
            ])->assertOk()->assertSee(__('public.contact.unavailable'));

        $this->assertStringNotContainsString('SQLSTATE', $response->getContent());
    }

    public function test_consultation_is_separate_and_about_calls_to_action_link_to_it(): void
    {
        $this->get(route('frontend.consultation'))
            ->assertOk()
            ->assertSee('name="role_title"', false)
            ->assertSee('name="hiring_model"', false)
            ->assertSee('name="expected_hires"', false)
            ->assertSee('name="preferred_contact"', false)
            ->assertSee('name="privacy_consent"', false)
            ->assertSee(__('public.consultation.next_title'))
            ->assertSee(route('frontend.contact'));

        $this->get(route('frontend.about'))
            ->assertOk()
            ->assertSee(route('frontend.consultation'));

        $this->loginAsUser();
        $this->get(route('frontend.consultation'))
            ->assertOk()
            ->assertSee('Taylor Example')
            ->assertSee('taylor@example.test')
            ->assertDontSee('name="full_name"', false)
            ->assertDontSee('name="work_email"', false);
    }

    public function test_consultation_validation_and_valid_submit_show_unavailable_state(): void
    {
        $this->from(route('frontend.consultation'))
            ->post(route('frontend.consultation.submit'), [])
            ->assertRedirect(route('frontend.consultation'))
            ->assertSessionHasErrors(['full_name', 'work_email', 'company', 'role_title', 'hiring_model', 'needs', 'privacy_consent']);

        $this->from(route('frontend.consultation'))
            ->post(route('frontend.consultation.submit'), [
                'full_name' => 'Guest Example',
                'work_email' => 'guest@example.test',
                'company' => 'Example Co',
                'role_title' => 'Recruiter',
                'hiring_model' => 'direct_only',
                'expected_hires' => '1_3',
                'needs' => 'Hiring requirements',
                'preferred_contact' => 'email',
                'privacy_consent' => '1',
            ])->assertRedirect(route('frontend.consultation'))
            ->assertSessionHas('submission_unavailable', __('public.consultation.unavailable'))
            ->assertSessionHas('consultation_blocked', true);

        $this->from(route('frontend.consultation'))
            ->post(route('frontend.consultation.submit'), [
                'full_name' => 'Guest Example',
                'work_email' => 'guest@example.test',
                'company' => 'Example Co',
                'role_title' => 'Recruiter',
                'hiring_model' => 'direct_only',
                'needs' => 'Hiring requirements',
                'privacy_consent' => '1',
            ])->assertRedirect(route('frontend.consultation'))
            ->assertSessionHas('submission_unavailable', __('public.consultation.unavailable'));

        $this->from(route('frontend.consultation'))
            ->post(route('frontend.consultation.submit'), [
                'full_name' => 'Guest Example',
                'work_email' => 'guest@example.test',
                'company' => 'Example Co',
                'role_title' => 'Recruiter',
                'hiring_model' => 'direct_only',
                'expected_hires' => 'unlisted value',
                'needs' => 'Hiring requirements',
                'privacy_consent' => '1',
            ])->assertRedirect(route('frontend.consultation'))
            ->assertSessionHasErrors(['expected_hires']);

        $response = $this->followingRedirects()
            ->from(route('frontend.consultation'))
            ->post(route('frontend.consultation.submit'), [
                'full_name' => 'Guest Example',
                'work_email' => 'guest@example.test',
                'company' => 'Example Co',
                'role_title' => 'Recruiter',
                'hiring_model' => 'direct_only',
                'needs' => 'Hiring requirements',
                'privacy_consent' => '1',
            ])->assertOk()
            ->assertSee(__('public.consultation.unavailable'));
        $this->assertSame(1, substr_count($response->getContent(), __('public.consultation.unavailable')));
    }

    public function test_english_and_arabic_pages_render_their_translations(): void
    {
        App::setLocale('en');
        $this->get(route('frontend.contact'))->assertOk()->assertSee(__('public.contact.general_form_title'))->assertSee(__('public.contact.subject'));
        $this->get(route('frontend.consultation'))->assertOk()->assertSee(__('public.consultation.form_title'));

        App::setLocale('ar');
        $this->get(route('frontend.contact'))->assertOk()->assertSee(__('public.contact.general_form_title'))->assertSee(__('public.contact.subject'));
        $this->get(route('frontend.consultation'))->assertOk()->assertSee(__('public.consultation.form_title'));
    }

    private function loginAsUser(): void
    {
        ApiSession::store('session-token', [
            'id' => 12,
            'email' => 'taylor@example.test',
            'name' => 'Taylor Example',
            'account_type' => 'company',
        ]);
    }
}

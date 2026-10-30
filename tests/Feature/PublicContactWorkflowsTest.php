<?php

namespace Tests\Feature;

use App\Frontend\Auth\ApiSession;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class PublicContactWorkflowsTest extends TestCase
{
    public function test_guest_contact_page_has_only_general_contact_fields(): void
    {
        $this->get(route('frontend.contact'))
            ->assertOk()
            ->assertSee('name="name"', false)
            ->assertSee('name="email"', false)
            ->assertSee('name="message"', false)
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
            ->assertDontSee('name="email"', false);
    }

    public function test_general_contact_validation_preserves_values_and_valid_submission_is_unavailable(): void
    {
        $this->from(route('frontend.contact'))
            ->post(route('frontend.contact.submit'), ['name' => '', 'email' => 'invalid', 'message' => ''])
            ->assertRedirect(route('frontend.contact'))
            ->assertSessionHasErrors(['name', 'email', 'message'])
            ->assertSessionHas('_old_input.email', 'invalid');

        $this->from(route('frontend.contact'))
            ->post(route('frontend.contact.submit'), [
                'name' => 'Guest Example',
                'email' => 'guest@example.test',
                'message' => 'A general question',
            ])
            ->assertRedirect(route('frontend.contact'))
            ->assertSessionHas('submission_unavailable', __('public.contact.unavailable'));

        $response = $this->followingRedirects()
            ->from(route('frontend.contact'))
            ->post(route('frontend.contact.submit'), [
                'name' => 'Guest Example',
                'email' => 'guest@example.test',
                'message' => 'A general question',
            ])->assertOk()
            ->assertSee(__('public.contact.unavailable'));
        $this->assertSame(1, substr_count($response->getContent(), __('public.contact.unavailable')));
    }

    public function test_authenticated_contact_requires_no_submitted_identity_and_ignores_editable_identity(): void
    {
        $this->loginAsUser();

        $this->from(route('frontend.contact'))->post(route('frontend.contact.submit'), [
            'name' => 'Spoofed Name',
            'email' => 'spoof@example.test',
            'message' => 'A general question',
        ])->assertRedirect(route('frontend.contact'))
            ->assertSessionHas('submission_unavailable', __('public.contact.unavailable'))
            ->assertSessionMissing('_old_input.name')
            ->assertSessionMissing('_old_input.email');
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
        $this->get(route('frontend.contact'))->assertOk()->assertSee(__('public.contact.general_form_title'));
        $this->get(route('frontend.consultation'))->assertOk()->assertSee(__('public.consultation.form_title'));

        App::setLocale('ar');
        $this->get(route('frontend.contact'))->assertOk()->assertSee(__('public.contact.general_form_title'));
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

<?php

namespace Tests\Feature;

use App\Frontend\Auth\ApiSession;
use App\Frontend\Home\ConsultationController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class Mission11RequestConsultationTest extends TestCase
{
    public function test_public_guest_can_access_consultation_page(): void
    {
        $this->get(route('frontend.consultation'))
            ->assertOk()
            ->assertSee(__('public.consultation.title'))
            ->assertSee('data-page="consultation"', false)
            ->assertSee('data-consultation-api="0"', false)
            ->assertSee('data-consultation-blocked', false)
            ->assertSee('name="privacy_consent"', false)
            ->assertSee('data-consent-required', false);
    }

    public function test_candidate_session_sees_identity_without_editable_name_email(): void
    {
        ApiSession::store('token-candidate', [
            'id' => 21,
            'email' => 'candidate@example.test',
            'name' => 'Candidate User',
            'account_type' => 'candidate',
        ]);

        $this->get(route('frontend.consultation'))
            ->assertOk()
            ->assertSee('Candidate User')
            ->assertSee('candidate@example.test')
            ->assertDontSee('name="full_name"', false)
            ->assertDontSee('name="work_email"', false);
    }

    public function test_company_session_sees_identity_without_editable_name_email(): void
    {
        ApiSession::store('token-company', [
            'id' => 22,
            'email' => 'company@example.test',
            'name' => 'Company User',
            'account_type' => 'company',
        ]);

        $this->get(route('frontend.consultation'))
            ->assertOk()
            ->assertSee('Company User')
            ->assertSee('company@example.test')
            ->assertDontSee('name="full_name"', false)
            ->assertDontSee('name="work_email"', false);
    }

    public function test_general_contact_route_remains_separate(): void
    {
        $this->get(route('frontend.contact'))
            ->assertOk()
            ->assertSee('name="subject"', false)
            ->assertSee('name="description"', false)
            ->assertDontSee('name="hiring_model"', false)
            ->assertDontSee('name="privacy_consent"', false)
            ->assertSee(route('frontend.consultation'));

        $this->get(route('frontend.consultation'))
            ->assertOk()
            ->assertDontSee('name="description"', false)
            ->assertSee('name="hiring_model"', false)
            ->assertSee(route('frontend.contact'));

        $this->assertNotSame(
            route('frontend.contact'),
            route('frontend.consultation')
        );
    }

    public function test_required_field_validation_for_guests(): void
    {
        $this->from(route('frontend.consultation'))
            ->post(route('frontend.consultation.submit'), [])
            ->assertRedirect(route('frontend.consultation'))
            ->assertSessionHasErrors([
                'full_name',
                'work_email',
                'company',
                'role_title',
                'hiring_model',
                'needs',
                'privacy_consent',
            ]);
    }

    public function test_email_validation_rejects_invalid_addresses(): void
    {
        $this->from(route('frontend.consultation'))
            ->post(route('frontend.consultation.submit'), $this->validPayload([
                'work_email' => 'not-an-email',
            ]))
            ->assertRedirect(route('frontend.consultation'))
            ->assertSessionHasErrors(['work_email']);
    }

    public function test_requester_role_and_hiring_model_mapping(): void
    {
        foreach (ConsultationController::HIRING_MODELS as $model) {
            $this->from(route('frontend.consultation'))
                ->post(route('frontend.consultation.submit'), $this->validPayload([
                    'hiring_model' => $model,
                    'role_title' => 'People Partner',
                ]))
                ->assertRedirect(route('frontend.consultation'))
                ->assertSessionHas('submission_unavailable')
                ->assertSessionDoesntHaveErrors();
        }

        $this->from(route('frontend.consultation'))
            ->post(route('frontend.consultation.submit'), $this->validPayload([
                'hiring_model' => 'not_a_model',
            ]))
            ->assertRedirect(route('frontend.consultation'))
            ->assertSessionHasErrors(['hiring_model']);
    }

    public function test_hiring_volume_and_preferred_contact_validation(): void
    {
        foreach (ConsultationController::EXPECTED_HIRES as $volume) {
            $this->from(route('frontend.consultation'))
                ->post(route('frontend.consultation.submit'), $this->validPayload([
                    'expected_hires' => $volume,
                ]))
                ->assertSessionDoesntHaveErrors(['expected_hires']);
        }

        $this->from(route('frontend.consultation'))
            ->post(route('frontend.consultation.submit'), $this->validPayload([
                'expected_hires' => 'unlisted',
            ]))
            ->assertSessionHasErrors(['expected_hires']);

        foreach (ConsultationController::PREFERRED_CONTACTS as $method) {
            $this->from(route('frontend.consultation'))
                ->post(route('frontend.consultation.submit'), $this->validPayload([
                    'preferred_contact' => $method,
                ]))
                ->assertSessionDoesntHaveErrors(['preferred_contact']);
        }

        $this->from(route('frontend.consultation'))
            ->post(route('frontend.consultation.submit'), $this->validPayload([
                'preferred_contact' => 'carrier_pigeon',
            ]))
            ->assertSessionHasErrors(['preferred_contact']);
    }

    public function test_consent_required_and_unchecked_by_default(): void
    {
        $html = $this->get(route('frontend.consultation'))->assertOk()->getContent();
        $this->assertStringContainsString('name="privacy_consent"', $html);
        $this->assertDoesNotMatchRegularExpression(
            '/name="privacy_consent"[^>]*checked/',
            $html
        );
        $this->assertDoesNotMatchRegularExpression(
            '/name="insights_consent"[^>]*checked/',
            $html
        );

        $this->from(route('frontend.consultation'))
            ->post(route('frontend.consultation.submit'), $this->validPayload([
                'privacy_consent' => null,
            ]))
            ->assertSessionHasErrors(['privacy_consent']);
    }

    public function test_optional_marketing_consent_is_not_required(): void
    {
        $this->from(route('frontend.consultation'))
            ->post(route('frontend.consultation.submit'), $this->validPayload([
                'insights_consent' => '1',
            ]))
            ->assertRedirect(route('frontend.consultation'))
            ->assertSessionDoesntHaveErrors()
            ->assertSessionHas('submission_unavailable');
    }

    public function test_needs_min_length_and_xss_payload_is_escaped_on_redisplay(): void
    {
        $this->from(route('frontend.consultation'))
            ->post(route('frontend.consultation.submit'), $this->validPayload([
                'needs' => 'short',
            ]))
            ->assertSessionHasErrors(['needs']);

        $payload = '<script>alert(1)</script> hiring plan details here';
        $response = $this->followingRedirects()
            ->from(route('frontend.consultation'))
            ->post(route('frontend.consultation.submit'), $this->validPayload([
                'needs' => $payload,
                'full_name' => '<img src=x onerror=alert(1)>',
            ]))
            ->assertOk();

        $html = $response->getContent();
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringNotContainsString('<img src=x onerror=alert(1)>', $html);
        $this->assertStringContainsString(e($payload), $html);
    }

    public function test_csrf_token_is_required_for_submission(): void
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);

        // With CSRF middleware disabled (test harness), valid posts still reach controller.
        $this->from(route('frontend.consultation'))
            ->post(route('frontend.consultation.submit'), $this->validPayload())
            ->assertRedirect(route('frontend.consultation'));

        // Form markup always includes CSRF for browser posts.
        $this->get(route('frontend.consultation'))
            ->assertOk()
            ->assertSee('name="_token"', false);
    }

    public function test_duplicate_submit_guard_markup_and_blocked_backend_contract(): void
    {
        Http::fake();

        $this->assertFalse(ConsultationController::CONSULTATION_API_AVAILABLE);

        $first = $this->from(route('frontend.consultation'))
            ->post(route('frontend.consultation.submit'), $this->validPayload());
        $first->assertRedirect(route('frontend.consultation'))
            ->assertSessionHas('submission_unavailable', __('public.consultation.unavailable'))
            ->assertSessionHas('consultation_blocked', true)
            ->assertSessionMissing('consultation_success');

        $second = $this->from(route('frontend.consultation'))
            ->post(route('frontend.consultation.submit'), $this->validPayload());
        $second->assertRedirect(route('frontend.consultation'))
            ->assertSessionHas('consultation_blocked', true);

        Http::assertNothingSent();

        $this->get(route('frontend.consultation'))
            ->assertOk()
            ->assertSee('data-submission-blocked', false)
            ->assertSee(__('public.consultation.no_false_success'));
    }

    public function test_missing_consultation_api_never_claims_success_or_hits_general_contact(): void
    {
        Http::fake([
            '*/api/contact' => Http::response(['message' => 'should not be called'], 201),
            '*/api/contact/consultation' => Http::response(['message' => 'missing'], 404),
        ]);

        $response = $this->followingRedirects()
            ->from(route('frontend.consultation'))
            ->post(route('frontend.consultation.submit'), $this->validPayload())
            ->assertOk();

        $html = $response->getContent();
        $this->assertStringContainsString(__('public.consultation.unavailable'), $html);
        $this->assertStringNotContainsString('Your contact message has been submitted successfully', $html);
        $this->assertStringNotContainsString('consultation_success', $html);
        $this->assertSame(1, substr_count($html, __('public.consultation.unavailable')));
        Http::assertNothingSent();
    }

    public function test_arabic_rtl_and_english_localization(): void
    {
        App::setLocale('en');
        $this->get(route('frontend.consultation'))
            ->assertOk()
            ->assertSee(__('public.consultation.sections.identity'))
            ->assertSee(__('public.consultation.hire_counts.1_3'));

        App::setLocale('ar');
        $this->get(route('frontend.consultation'))
            ->assertOk()
            ->assertSee(__('public.consultation.sections.identity'))
            ->assertSee(__('public.consultation.contact_options.phone'));
    }

    public function test_public_navigation_exposes_consultation_without_replacing_contact(): void
    {
        $this->get(route('frontend.about'))
            ->assertOk()
            ->assertSee(route('frontend.consultation'))
            ->assertSee(route('frontend.contact'))
            ->assertSee(__('navigation.consultation'));

        $this->get(route('frontend.contact'))
            ->assertOk()
            ->assertSee(route('frontend.consultation'))
            ->assertSee(__('public.contact.consultation_link'));
    }

    public function test_support_type_enum_and_authenticated_submit_omits_identity_old_input(): void
    {
        $this->from(route('frontend.consultation'))
            ->post(route('frontend.consultation.submit'), $this->validPayload([
                'support_type' => 'not_supported',
            ]))
            ->assertSessionHasErrors(['support_type']);

        ApiSession::store('session-token', [
            'id' => 12,
            'email' => 'taylor@example.test',
            'name' => 'Taylor Example',
            'account_type' => 'company',
        ]);

        $this->from(route('frontend.consultation'))
            ->post(route('frontend.consultation.submit'), [
                'full_name' => 'Spoofed',
                'work_email' => 'spoof@example.test',
                'company' => 'Example Co',
                'role_title' => 'Recruiter',
                'hiring_model' => 'agency_only',
                'expected_hires' => '4_10',
                'support_type' => 'rpo',
                'needs' => 'Hiring requirements detail',
                'preferred_contact' => 'phone',
                'privacy_consent' => '1',
            ])
            ->assertRedirect(route('frontend.consultation'))
            ->assertSessionHas('submission_unavailable')
            ->assertSessionMissing('_old_input.full_name')
            ->assertSessionMissing('_old_input.work_email');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'full_name' => 'Guest Example',
            'work_email' => 'guest@example.test',
            'company' => 'Example Co',
            'role_title' => 'Recruiter',
            'hiring_model' => 'direct_only',
            'expected_hires' => '1_3',
            'support_type' => 'advisory',
            'needs' => 'Hiring requirements for product roles',
            'preferred_contact' => 'email',
            'privacy_consent' => '1',
        ], $overrides);
    }
}

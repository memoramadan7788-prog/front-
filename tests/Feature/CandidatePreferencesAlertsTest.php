<?php

namespace Tests\Feature;

use App\Frontend\Auth\ApiSession;
use App\Support\Frontend\CandidateDashboard\CandidateDashboardKpiGateway;
use App\Support\Frontend\CandidateSettings\CandidateSettingsGateway;
use Tests\Support\FakeCandidateDashboardKpiGateway;
use Tests\Support\FakeCandidateSettingsGateway;
use Tests\TestCase;

class CandidatePreferencesAlertsTest extends TestCase
{
    private FakeCandidateSettingsGateway $gateway;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gateway = new FakeCandidateSettingsGateway;
        $this->app->instance(CandidateSettingsGateway::class, $this->gateway);
        $this->app->instance(CandidateDashboardKpiGateway::class, new FakeCandidateDashboardKpiGateway);
    }

    public function test_guest_is_redirected_from_preferences(): void
    {
        $this->get(route('frontend.candidate.preferences'))
            ->assertRedirect(route('frontend.account.login'));
    }

    public function test_company_role_cannot_open_preferences(): void
    {
        ApiSession::store('co-token', [
            'id' => 9,
            'email' => 'hr@example.com',
            'name' => 'HR',
            'account_type' => 'company',
            'roles' => ['company_admin'],
        ]);

        $this->get(route('frontend.candidate.preferences'))->assertRedirect();
    }

    public function test_candidate_loads_persisted_settings_sections(): void
    {
        $this->actingAsCandidate();

        $html = $this->get(route('frontend.candidate.preferences'))->assertOk()->getContent();

        $this->assertStringContainsString(e(__('candidate.preferences.title')), $html);
        $this->assertStringContainsString('Backend Engineer', $html);
        $this->assertStringContainsString('Cairo', $html);
        $this->assertStringContainsString('name="weekly_digest"', $html);
        $this->assertStringContainsString('name="visibility"', $html);
        $this->assertStringContainsString(e(__('candidate.preferences.alerts_delivery_note')), $html);
        $this->assertStringContainsString(route('frontend.candidate.preferences.job', [], false), $html);
        $this->assertStringContainsString(e(__('navigation.candidate.preferences')), $html);
    }

    public function test_job_preferences_update_posts_validated_payload(): void
    {
        $this->actingAsCandidate();

        $response = $this->from(route('frontend.candidate.preferences'))
            ->post(route('frontend.candidate.preferences.job'), [
                'desired_titles' => 'Laravel Developer, API Engineer',
                'location' => 'Giza',
                'employment_type' => 'full_time',
                'min_salary' => 20000,
                'work_model' => 'remote',
                'open_to_relocation' => '1',
                'include_agency' => '0',
            ]);

        $response->assertRedirect()
            ->assertSessionHas('success_key', 'candidate.preferences.flash_preferences_saved')
            ->assertSessionMissing('errors');
        $this->assertStringStartsWith(route('frontend.candidate.preferences'), $response->headers->get('Location'));

        $this->assertSame(1, $this->gateway->updatePreferencesCalls);
        $this->assertSame(['Laravel Developer', 'API Engineer'], $this->gateway->lastPreferencesPayload['desired_titles']);
        $this->assertSame('Giza', $this->gateway->lastPreferencesPayload['location']);
        $this->assertSame('remote', $this->gateway->lastPreferencesPayload['work_model']);
        $this->assertTrue($this->gateway->lastPreferencesPayload['open_to_relocation']);
        $this->assertFalse($this->gateway->lastPreferencesPayload['include_agency']);
    }

    public function test_job_preferences_validation_failure_does_not_call_backend(): void
    {
        $this->actingAsCandidate();

        $this->from(route('frontend.candidate.preferences'))
            ->post(route('frontend.candidate.preferences.job'), [
                'desired_titles' => 'Engineer',
                'work_model' => 'spaceship',
            ])
            ->assertRedirect(route('frontend.candidate.preferences'))
            ->assertSessionHasErrors('work_model');

        $this->assertSame(0, $this->gateway->updatePreferencesCalls);
    }

    public function test_backend_error_on_preferences_update_does_not_flash_success(): void
    {
        $this->actingAsCandidate();
        $this->gateway->updatePreferencesResponse = $this->gateway->fail(500, 'Upstream failed');

        $this->from(route('frontend.candidate.preferences'))
            ->post(route('frontend.candidate.preferences.job'), [
                'desired_titles' => 'Engineer',
                'location' => 'Cairo',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors()
            ->assertSessionMissing('success_key');
    }

    public function test_notification_toggles_persist_via_gateway(): void
    {
        $this->actingAsCandidate();

        $this->from(route('frontend.candidate.preferences'))
            ->post(route('frontend.candidate.preferences.notifications'), [
                'weekly_digest' => '1',
                'status_updates' => '0',
                'interview_reminders' => '1',
                'messages' => '1',
                'marketing' => '0',
            ])
            ->assertRedirect()
            ->assertSessionHas('success_key', 'candidate.preferences.flash_notifications_saved');

        $this->assertSame(1, $this->gateway->updateNotificationsCalls);
        $this->assertTrue($this->gateway->lastNotificationsPayload['weekly_digest']);
        $this->assertFalse($this->gateway->lastNotificationsPayload['status_updates']);
        $this->assertFalse($this->gateway->lastNotificationsPayload['marketing']);
    }

    public function test_privacy_settings_update_uses_documented_visibility(): void
    {
        $this->actingAsCandidate();

        $this->from(route('frontend.candidate.preferences'))
            ->post(route('frontend.candidate.preferences.privacy'), [
                'visibility' => 'private',
                'allow_agency_recruiters' => '1',
                'show_in_public_search' => '0',
            ])
            ->assertRedirect()
            ->assertSessionHas('success_key', 'candidate.preferences.flash_privacy_saved');

        $this->assertSame('private', $this->gateway->lastPrivacyPayload['visibility']);
        $this->assertTrue($this->gateway->lastPrivacyPayload['allow_agency_recruiters']);
        $this->assertFalse($this->gateway->lastPrivacyPayload['show_in_public_search']);
    }

    public function test_unsupported_privacy_visibility_is_rejected(): void
    {
        $this->actingAsCandidate();

        $this->from(route('frontend.candidate.preferences'))
            ->post(route('frontend.candidate.preferences.privacy'), [
                'visibility' => 'friends_only',
            ])
            ->assertSessionHasErrors('visibility');

        $this->assertNull($this->gateway->lastPrivacyPayload);
    }

    public function test_saved_searches_empty_state(): void
    {
        $this->actingAsCandidate();
        $this->gateway->savedSearchesResponse = $this->gateway->ok([]);

        $html = $this->get(route('frontend.candidate.preferences'))->assertOk()->getContent();

        $this->assertStringContainsString(__('candidate.preferences.saved_searches_empty_title'), $html);
        $this->assertStringContainsString('data-saved-searches-empty', $html);
        $this->assertStringContainsString('data-prefs-form="saved-search-create"', $html);
        $this->assertStringContainsString(route('frontend.candidate.vacancies.index', [], false), $html);
    }

    public function test_saved_search_listing_and_alert_controls(): void
    {
        $this->actingAsCandidate();
        $this->gateway->savedSearchesResponse = $this->gateway->ok([[
            'id' => 7,
            'name' => 'Remote Laravel',
            'filters' => ['q' => 'Laravel', 'work_mode' => ['remote']],
            'weekly_alerts' => true,
            'is_paused' => false,
            'last_alerted_at' => null,
            'updated_at' => '2026-10-30T12:00:00+00:00',
        ]]);

        $html = $this->get(route('frontend.candidate.preferences'))->assertOk()->getContent();

        $this->assertStringContainsString('Remote Laravel', $html);
        $this->assertStringContainsString('data-saved-search-id="7"', $html);
        $this->assertStringContainsString(__('candidate.preferences.disable_alerts'), $html);
        $this->assertStringContainsString(__('candidate.preferences.pause_alerts'), $html);
        $this->assertStringContainsString(
            route('frontend.candidate.preferences.saved-searches.destroy', ['searchId' => 7], false),
            $html
        );
    }

    public function test_create_saved_search_maps_mission01_filters(): void
    {
        $this->actingAsCandidate();

        $this->from(route('frontend.candidate.preferences'))
            ->post(route('frontend.candidate.preferences.saved-searches.store'), [
                'name' => 'Cairo remote',
                'search_q' => 'Laravel',
                'search_location' => 'Cairo',
                'search_employment_type' => 'full_time',
                'search_work_mode' => 'remote',
                'weekly_alerts' => '1',
            ])
            ->assertRedirect()
            ->assertSessionHas('success_key', 'candidate.preferences.flash_saved_search_created');

        $this->assertSame('Cairo remote', $this->gateway->lastCreateSearchPayload['name']);
        $this->assertSame('Laravel', $this->gateway->lastCreateSearchPayload['filters']['q']);
        $this->assertSame(['remote'], $this->gateway->lastCreateSearchPayload['filters']['work_mode']);
        $this->assertTrue($this->gateway->lastCreateSearchPayload['weekly_alerts']);
    }

    public function test_create_saved_search_requires_at_least_one_filter(): void
    {
        $this->actingAsCandidate();

        $this->from(route('frontend.candidate.preferences'))
            ->post(route('frontend.candidate.preferences.saved-searches.store'), [
                'name' => 'Empty filters',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('filters');

        $this->assertNull($this->gateway->lastCreateSearchPayload);
    }

    public function test_update_and_delete_saved_search_use_mocked_gateway(): void
    {
        $this->actingAsCandidate();

        $this->post(route('frontend.candidate.preferences.saved-searches.update', ['searchId' => 3]), [
            'is_paused' => '1',
        ])->assertRedirect()
            ->assertSessionHas('success_key', 'candidate.preferences.flash_saved_search_updated');

        $this->post(route('frontend.candidate.preferences.saved-searches.destroy', ['searchId' => 3]))
            ->assertRedirect()
            ->assertSessionHas('success_key', 'candidate.preferences.flash_saved_search_deleted');
    }

    public function test_section_load_failure_shows_error_not_fake_data(): void
    {
        $this->actingAsCandidate();
        $this->gateway->preferencesResponse = $this->gateway->fail(503, 'Unavailable');

        $html = $this->get(route('frontend.candidate.preferences'))->assertOk()->getContent();

        $this->assertStringContainsString('Unavailable', $html);
        $this->assertStringContainsString('data-prefs-error="job"', $html);
        $this->assertStringContainsString('data-prefs-retry="job"', $html);
        $this->assertStringNotContainsString('value="Cairo"', $html);
        $this->assertStringNotContainsString('data-prefs-form="job"', $html);
        $this->assertStringNotContainsString(__('candidate.preferences.save_job'), $html);
        // Unaffected sections still load.
        $this->assertStringContainsString('data-prefs-form="notifications"', $html);
        $this->assertStringContainsString('name="weekly_digest"', $html);
    }

    public function test_notification_get_failure_disables_save_and_keeps_other_sections(): void
    {
        $this->actingAsCandidate();
        $this->gateway->notificationsResponse = $this->gateway->fail(500, 'Notif unavailable');

        $html = $this->get(route('frontend.candidate.preferences'))->assertOk()->getContent();

        $this->assertStringContainsString('data-prefs-error="notifications"', $html);
        $this->assertStringContainsString('data-prefs-retry="notifications"', $html);
        $this->assertStringNotContainsString('data-prefs-form="notifications"', $html);
        $this->assertStringNotContainsString(__('candidate.preferences.save_notifications'), $html);
        $this->assertStringContainsString('data-prefs-form="job"', $html);
        $this->assertStringContainsString('Backend Engineer', $html);
    }

    public function test_privacy_get_failure_does_not_show_default_visibility_as_persisted(): void
    {
        $this->actingAsCandidate();
        $this->gateway->privacyResponse = $this->gateway->fail(500, 'Privacy unavailable');

        $html = $this->get(route('frontend.candidate.preferences'))->assertOk()->getContent();

        $this->assertStringContainsString('data-prefs-error="privacy"', $html);
        $this->assertStringContainsString('data-prefs-retry="privacy"', $html);
        $this->assertStringNotContainsString('data-prefs-form="privacy"', $html);
        $this->assertStringNotContainsString('name="visibility"', $html);
        $this->assertStringNotContainsString(__('candidate.preferences.save_privacy'), $html);
    }

    public function test_partial_page_failure_isolates_sections(): void
    {
        $this->actingAsCandidate();
        $this->gateway->preferencesResponse = $this->gateway->fail(500, 'Prefs down');
        $this->gateway->privacyResponse = $this->gateway->fail(500, 'Privacy down');

        $html = $this->get(route('frontend.candidate.preferences'))->assertOk()->getContent();

        $this->assertStringContainsString('data-prefs-loaded="0"', $html);
        $this->assertStringContainsString('data-prefs-error="job"', $html);
        $this->assertStringContainsString('data-prefs-error="privacy"', $html);
        $this->assertStringContainsString('data-prefs-form="notifications"', $html);
        $this->assertStringContainsString('data-saved-searches-empty', $html);
        $this->assertSame(0, $this->gateway->updatePreferencesCalls);
        $this->assertSame(0, $this->gateway->updateNotificationsCalls);
    }

    public function test_failed_init_has_no_save_controls_and_retry_links(): void
    {
        $this->actingAsCandidate();
        $this->gateway->preferencesResponse = $this->gateway->fail(500, 'A');
        $this->gateway->notificationsResponse = $this->gateway->fail(500, 'B');
        $this->gateway->privacyResponse = $this->gateway->fail(500, 'C');

        $html = $this->get(route('frontend.candidate.preferences'))->assertOk()->getContent();

        $this->assertStringContainsString(__('candidate.preferences.retry'), $html);
        $this->assertStringContainsString(__('candidate.preferences.section_unavailable_title'), $html);
        $this->assertStringNotContainsString(__('candidate.preferences.save_job'), $html);
        $this->assertStringNotContainsString(__('candidate.preferences.save_notifications'), $html);
        $this->assertStringNotContainsString(__('candidate.preferences.save_privacy'), $html);
        $this->assertStringContainsString('data-prefs-retry="job"', $html);
        $this->assertStringContainsString('data-prefs-retry="notifications"', $html);
        $this->assertStringContainsString('data-prefs-retry="privacy"', $html);
    }

    public function test_saved_searches_error_is_not_shown_as_empty_state(): void
    {
        $this->actingAsCandidate();
        $this->gateway->savedSearchesResponse = $this->gateway->fail(500, 'Searches unavailable');

        $html = $this->get(route('frontend.candidate.preferences'))->assertOk()->getContent();

        $this->assertStringContainsString('data-prefs-error="saved-searches"', $html);
        $this->assertStringContainsString('Searches unavailable', $html);
        $this->assertStringNotContainsString('data-saved-searches-empty', $html);
        $this->assertStringNotContainsString(__('candidate.preferences.saved_searches_empty_title'), $html);
        $this->assertStringNotContainsString('data-prefs-form="saved-search-create"', $html);
    }

    public function test_arabic_rtl_error_state_for_failed_section(): void
    {
        $this->actingAsCandidate();
        $this->gateway->preferencesResponse = $this->gateway->fail(500, 'Prefs unavailable');

        $html = $this->withUnencryptedCookie('hb_locale', 'ar')
            ->get(route('frontend.candidate.preferences'))
            ->assertOk()
            ->getContent();

        $this->assertMatchesRegularExpression('/\bdir=["\']rtl["\']/', $html);
        $this->assertStringContainsString(__('candidate.preferences.section_unavailable_title', [], 'ar'), $html);
        $this->assertStringContainsString(__('candidate.preferences.retry', [], 'ar'), $html);
        $this->assertStringContainsString('data-prefs-error="job"', $html);
    }

    public function test_unauthorized_api_clears_session(): void
    {
        $this->actingAsCandidate();
        $this->gateway->preferencesResponse = $this->gateway->fail(401, 'Unauthenticated');

        $this->get(route('frontend.candidate.preferences'))
            ->assertRedirect(route('frontend.account.login'));

        $this->assertFalse(ApiSession::check());
    }

    public function test_arabic_rtl_labels(): void
    {
        $this->actingAsCandidate();

        $html = $this->withUnencryptedCookie('hb_locale', 'ar')
            ->get(route('frontend.candidate.preferences'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(__('candidate.preferences.title', [], 'ar'), $html);
        $this->assertStringContainsString(__('candidate.preferences.notifications_heading', [], 'ar'), $html);
        $this->assertMatchesRegularExpression('/\bdir=["\']rtl["\']/', $html);
    }

    public function test_no_cross_candidate_identifiers_leaked_in_markup(): void
    {
        $this->actingAsCandidate();

        $html = $this->get(route('frontend.candidate.preferences'))->assertOk()->getContent();

        $this->assertStringNotContainsString('candidate_id', $html);
        $this->assertStringNotContainsString('Bearer ', $html);
    }

    public function test_dashboard_still_loads_after_preferences_route_exists(): void
    {
        $this->actingAsCandidate();

        $this->get(route('frontend.candidate.dashboard'))->assertOk();
        $this->get(route('frontend.candidate.applications.index'))->assertOk();
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
}

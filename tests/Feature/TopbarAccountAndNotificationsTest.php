<?php

namespace Tests\Feature;

use App\Frontend\Auth\ApiSession;
use App\Models\CandidateAPI;
use App\Support\Frontend\Notifications\NotificationsGateway;
use Mockery;
use Tests\TestCase;

class TopbarAccountAndNotificationsTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_topbar_shows_real_name_email_and_role_not_generic_workspace_account(): void
    {
        $this->actingAsCandidate();

        $html = $this->get(route('frontend.candidate.dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString('Candidate User', $html);
        $this->assertStringContainsString('candidate@example.com', $html);
        $this->assertStringContainsString(__('common.role_labels.candidate'), $html);
        $this->assertStringContainsString(__('common.account_security'), $html);
        $this->assertStringContainsString(__('common.notification_preferences'), $html);
        $this->assertStringContainsString(__('common.pending_feature'), $html);
        $this->assertStringContainsString(__('common.sign_out'), $html);
        $this->assertStringContainsString(route('frontend.account.change-password', [], false), $html);
        $this->assertStringContainsString(route('frontend.account.logout', [], false), $html);
        $this->assertStringNotContainsString(__('common.workspace_account'), $html);
    }

    public function test_notifications_bell_shows_empty_state_without_fabricating_items(): void
    {
        $this->actingAsCandidate();

        $html = $this->get(route('frontend.candidate.dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString('data-notifications-panel', $html);
        $this->assertStringContainsString('data-notifications-empty', $html);
        $this->assertStringContainsString(__('common.notifications.caught_up_title'), $html);
        $this->assertStringNotContainsString('Interview agenda', $html);
        $this->assertStringNotContainsString(__('common.notifications.unread', ['count' => 3]), $html);
        $this->assertStringNotContainsString(__('common.notifications.unavailable_title'), $html);
    }

    public function test_notifications_list_renders_backend_items_when_gateway_available(): void
    {
        $this->actingAsCandidate();

        $gateway = Mockery::mock(NotificationsGateway::class);
        $gateway->shouldReceive('isAvailable')->andReturn(true);
        $gateway->shouldReceive('list')->once()->with(1, 15)->andReturn([
            'available' => true,
            'statusCode' => 200,
            'message' => 'OK',
            'data' => [[
                'id' => 9,
                'title' => 'Join request approved',
                'body' => 'Your membership was approved.',
                'created_at' => '2026-10-20T12:00:00+00:00',
                'read_at' => null,
                'action_url' => '/candidate/join-company',
            ]],
            'meta' => [
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => 15,
                'total' => 1,
                'unread_count' => 1,
            ],
            'errors' => null,
        ]);
        $this->app->instance(NotificationsGateway::class, $gateway);

        $html = $this->get(route('frontend.candidate.dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString('Join request approved', $html);
        $this->assertStringContainsString('Your membership was approved.', $html);
        $this->assertStringContainsString(__('common.notifications.unread', ['count' => 1]), $html);
        $this->assertStringContainsString('/candidate/join-company', $html);
        $this->assertStringNotContainsString(__('common.notifications.unavailable_title'), $html);
    }

    public function test_profile_avatar_section_is_preview_only_without_upload_api(): void
    {
        $this->actingAsCandidate();

        $api = Mockery::mock(CandidateAPI::class);
        $api->shouldReceive('getProfile')->once()->andReturn([
            'statusCode' => 200,
            'message' => 'OK',
            'data' => [
                'name' => 'Candidate User',
                'email' => 'candidate@example.com',
                'photo_path' => 'photos/secret.jpg',
            ],
            'errors' => null,
        ]);
        $this->app->instance(CandidateAPI::class, $api);

        $html = $this->get(route('frontend.candidate.profile'))->assertOk()->getContent();

        $this->assertStringContainsString('data-profile-avatar', $html);
        $this->assertStringContainsString(__('common.avatar.title'), $html);
        $this->assertStringContainsString(__('common.avatar.preview_only_badge'), $html);
        $this->assertStringContainsString('data-avatar-unavailable', $html);
        $this->assertStringNotContainsString('photos/secret.jpg', $html);
        $this->assertStringNotContainsString('data:image', $html);
    }

    public function test_arabic_topbar_role_and_notifications_copy(): void
    {
        $this->actingAsCandidate();

        $html = $this->withUnencryptedCookie('hb_locale', 'ar')
            ->get(route('frontend.candidate.dashboard'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(__('common.role_labels.candidate', [], 'ar'), $html);
        $this->assertStringContainsString(__('common.notifications.label', [], 'ar'), $html);
        $this->assertStringContainsString(__('common.notifications.caught_up_title', [], 'ar'), $html);
        $this->assertStringContainsString('dir="rtl"', $html);
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

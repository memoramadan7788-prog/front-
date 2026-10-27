<?php

namespace Tests\Feature;

use App\Frontend\Auth\ApiSession;
use App\Support\Frontend\Notifications\HttpNotificationsGateway;
use App\Support\Frontend\Notifications\NotificationDestination;
use App\Support\Frontend\Notifications\NotificationsGateway;
use Tests\Support\FakeNotificationsGateway;
use Tests\TestCase;

class CandidateNotificationsTest extends TestCase
{
    private FakeNotificationsGateway $gateway;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gateway = new FakeNotificationsGateway;
        $this->app->instance(NotificationsGateway::class, $this->gateway);
    }

    public function test_candidate_bell_lists_notifications_and_unread_from_meta(): void
    {
        $this->actingAsCandidate();
        $this->gateway->items = [[
            'id' => 5,
            'type' => 'interview.scheduled',
            'title' => 'Interview Scheduled',
            'body' => 'Round 1 is booked.',
            'created_at' => '2026-10-27T09:00:00+00:00',
            'read_at' => null,
        ]];
        $this->gateway->unread = 2;
        $this->gateway->meta = [
            'current_page' => 1,
            'last_page' => 1,
            'per_page' => 15,
            'total' => 1,
            'unread_count' => 2,
        ];

        $html = $this->get(route('frontend.candidate.dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString('Interview Scheduled', $html);
        $this->assertStringContainsString('Round 1 is booked.', $html);
        $this->assertStringContainsString(__('common.notifications.unread', ['count' => 2]), $html);
        $this->assertStringContainsString('data-notifications-unread-badge', $html);
        $this->assertStringContainsString(route('frontend.candidate.interviews.index', [], false), $html);
        $this->assertStringContainsString(__('common.notifications.mark_read'), $html);
        $this->assertStringNotContainsString(__('common.notifications.unavailable_title'), $html);
    }

    public function test_empty_list_shows_caught_up_not_fabricated_items(): void
    {
        $this->actingAsCandidate();
        $this->gateway->items = [];
        $this->gateway->unread = 0;

        $html = $this->get(route('frontend.candidate.dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString('data-notifications-empty', $html);
        $this->assertStringContainsString(__('common.notifications.caught_up_title'), $html);
        $this->assertStringContainsString(__('common.notifications.unread', ['count' => 0]), $html);
        $this->assertStringNotContainsString('Interview Scheduled', $html);
    }

    public function test_pagination_meta_is_surfaced_when_multiple_pages(): void
    {
        $this->actingAsCandidate();
        $this->gateway->items = [[
            'id' => 1,
            'title' => 'Page one item',
            'body' => 'Body',
            'created_at' => '2026-10-27T09:00:00+00:00',
            'read_at' => '2026-10-27T10:00:00+00:00',
        ]];
        $this->gateway->unread = 0;
        $this->gateway->meta = [
            'current_page' => 1,
            'last_page' => 3,
            'per_page' => 15,
            'total' => 40,
            'unread_count' => 0,
        ];

        $html = $this->get(route('frontend.candidate.dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString('data-notifications-pagination', $html);
        $this->assertStringContainsString(__('common.notifications.page_of', [
            'page' => 1,
            'pages' => 3,
            'total' => 40,
        ]), $html);
    }

    public function test_api_error_and_forbidden_states(): void
    {
        $this->actingAsCandidate();
        $this->gateway->listStatus = 503;
        $this->gateway->listMessage = 'Service unavailable';

        $html = $this->get(route('frontend.candidate.dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString('data-notifications-error', $html);
        $this->assertStringContainsString('Service unavailable', $html);

        $this->gateway->listStatus = 403;
        $this->gateway->listMessage = 'Forbidden';
        $html = $this->get(route('frontend.candidate.dashboard'))->assertOk()->getContent();
        $this->assertStringContainsString(__('common.notifications.forbidden'), $html);
    }

    public function test_mark_as_read_posts_to_frontend_and_uses_mocked_gateway_patch(): void
    {
        $this->actingAsCandidate();
        $this->gateway->markReadStatus = 200;

        $this->from(route('frontend.candidate.dashboard'))
            ->post(route('frontend.candidate.notifications.read', ['id' => 9]), [
                'redirect' => '/candidate/dashboard',
            ])
            ->assertRedirect('/candidate/dashboard');

        $this->assertSame([9], $this->gateway->markedReadIds);
        $this->assertTrue(session()->has('success_key'));
    }

    public function test_mark_as_read_401_clears_session(): void
    {
        $this->actingAsCandidate();
        $this->gateway->markReadStatus = 401;
        $this->gateway->markReadMessage = 'Unauthenticated';

        $this->post(route('frontend.candidate.notifications.read', ['id' => 3]))
            ->assertRedirect(route('frontend.account.login'));
        $this->assertFalse(ApiSession::check());
    }

    public function test_mark_as_read_403_keeps_session_and_flashes_forbidden(): void
    {
        $this->actingAsCandidate();
        $this->gateway->markReadStatus = 403;
        $this->gateway->markReadMessage = 'Forbidden';

        $this->from(route('frontend.candidate.dashboard'))
            ->post(route('frontend.candidate.notifications.read', ['id' => 4]))
            ->assertRedirect(route('frontend.candidate.dashboard'))
            ->assertSessionHasErrors('error');

        $this->assertTrue(ApiSession::check());
        $this->assertSame([4], $this->gateway->markedReadIds);
    }

    public function test_mark_all_as_read_uses_mocked_gateway(): void
    {
        $this->actingAsCandidate();
        $this->gateway->items = [[
            'id' => 1,
            'title' => 'A',
            'read_at' => null,
        ]];
        $this->gateway->markAllStatus = 200;

        $this->from(route('frontend.candidate.dashboard'))
            ->post(route('frontend.candidate.notifications.read-all'), [
                'redirect' => '/candidate/dashboard',
            ])
            ->assertRedirect('/candidate/dashboard');

        $this->assertSame(1, $this->gateway->markAllCalls);
        $this->assertTrue(session()->has('success_key'));
    }

    public function test_company_and_admin_do_not_call_candidate_notification_apis(): void
    {
        // Use real HTTP gateway for role isolation checks (no live Backend calls for non-candidates).
        $gateway = new HttpNotificationsGateway;
        $this->app->instance(NotificationsGateway::class, $gateway);

        ApiSession::store('co-token', [
            'id' => 2,
            'email' => 'hr@example.com',
            'name' => 'HR User',
            'account_type' => 'company',
            'roles' => ['hr'],
        ]);

        $this->assertFalse($gateway->isAvailable());
        $list = $gateway->list();
        $this->assertFalse($list['available']);
        $this->assertSame([], $list['data']);

        $html = $this->get(route('frontend.company.dashboard'))->assertOk()->getContent();
        $this->assertStringContainsString('data-notifications-unavailable', $html);
        $this->assertStringContainsString(__('common.notifications.unavailable_title'), $html);
        $this->assertStringNotContainsString('data-notifications-list', $html);

        ApiSession::store('admin-token', [
            'id' => 3,
            'email' => 'admin@example.com',
            'name' => 'Admin',
            'account_type' => 'admin',
            'roles' => ['super_admin'],
        ]);

        $this->assertFalse($gateway->isAvailable());
        $this->assertFalse($gateway->list()['available']);
        $this->assertNull($gateway->unreadCount()['unread']);
    }

    public function test_destination_mapper_rejects_external_urls_and_maps_known_types(): void
    {
        $this->assertNull(NotificationDestination::href([
            'action_url' => 'https://evil.example/phish',
        ]));
        $this->assertNull(NotificationDestination::href([
            'action_url' => '//evil.example/phish',
        ]));
        $this->assertSame('/candidate/join-company', NotificationDestination::href([
            'action_url' => '/candidate/join-company',
        ]));
        $this->assertSame(
            route('frontend.candidate.interviews.index', [], false),
            NotificationDestination::href(['type' => 'interview.scheduled'])
        );
    }

    public function test_arabic_candidate_notifications_copy(): void
    {
        $this->actingAsCandidate();
        $this->gateway->items = [];
        $this->gateway->unread = 0;

        $html = $this->withUnencryptedCookie('hb_locale', 'ar')
            ->get(route('frontend.candidate.dashboard'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(__('common.notifications.label', [], 'ar'), $html);
        $this->assertStringContainsString(__('common.notifications.caught_up_title', [], 'ar'), $html);
        $this->assertStringContainsString('dir="rtl"', $html);
    }

    private function actingAsCandidate(): void
    {
        ApiSession::store('cand-token', [
            'id' => 41,
            'email' => 'notify@example.com',
            'name' => 'Notify Candidate',
            'account_type' => 'candidate',
            'roles' => ['candidate'],
        ]);
    }
}

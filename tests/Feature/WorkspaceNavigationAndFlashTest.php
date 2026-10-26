<?php

namespace Tests\Feature;

use App\Frontend\Auth\ApiSession;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class WorkspaceNavigationAndFlashTest extends TestCase
{
    public function test_candidate_dashboard_shows_recruitment_nav_and_not_admin_nav(): void
    {
        ApiSession::store('cand-token', [
            'id' => 1,
            'email' => 'c@example.com',
            'name' => 'Cand',
            'account_type' => 'candidate',
            'roles' => ['candidate'],
        ]);

        $html = $this->get(route('frontend.candidate.dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString(__('navigation.candidate.discover_jobs'), $html);
        $this->assertStringContainsString(__('navigation.candidate.applications'), $html);
        $this->assertStringContainsString(__('navigation.candidate.membership'), $html);
        $this->assertStringNotContainsString(__('navigation.admin.users'), $html);
        $this->assertStringNotContainsString(__('navigation.admin.roles'), $html);
        $this->assertStringContainsString('data-sidebar-nav-toggle', $html);
    }

    public function test_admin_shell_preserves_admin_navigation(): void
    {
        ApiSession::store('admin-token', [
            'id' => 2,
            'email' => 'a@example.com',
            'name' => 'Admin',
            'account_type' => 'admin',
            'roles' => ['super_admin'],
        ]);

        $this->get(route('frontend.account.change-password'))
            ->assertOk()
            ->assertSee('Super Admin')
            ->assertSee('Users & access')
            ->assertDontSee('Discover jobs');
    }

    public function test_query_role_cannot_force_admin_sidebar_for_candidate(): void
    {
        ApiSession::store('cand-token', [
            'id' => 3,
            'email' => 'c2@example.com',
            'name' => 'Cand',
            'account_type' => 'candidate',
        ]);

        $html = $this->get(route('frontend.candidate.dashboard', ['role' => 'admin']))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(__('navigation.candidate.overview'), $html);
        $this->assertStringNotContainsString(__('navigation.admin.organizations'), $html);
    }

    public function test_registration_flash_localizes_to_english(): void
    {
        ApiSession::store('cand-token', [
            'id' => 4,
            'email' => 'c3@example.com',
            'name' => 'Cand',
            'account_type' => 'candidate',
        ]);

        App::setLocale('en');

        $html = $this->withSession(['success_key' => 'auth.flash.register_candidate'])
            ->get(route('frontend.candidate.dashboard'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Your account was created successfully.', $html);
        $this->assertStringNotContainsString('تم تسجيل حسابك بنجاح', $html);
    }

    public function test_registration_flash_localizes_to_arabic(): void
    {
        ApiSession::store('cand-token', [
            'id' => 5,
            'email' => 'c4@example.com',
            'name' => 'Cand',
            'account_type' => 'candidate',
        ]);

        App::setLocale('ar');

        $html = $this->withSession(['success_key' => 'auth.flash.register_candidate'])
            ->get(route('frontend.candidate.dashboard'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('تم تسجيل حسابك بنجاح', $html);
        $this->assertStringNotContainsString('Your account was created successfully.', $html);
    }

    public function test_registration_flash_is_single_use(): void
    {
        ApiSession::store('cand-token', [
            'id' => 6,
            'email' => 'c5@example.com',
            'name' => 'Cand',
            'account_type' => 'candidate',
        ]);

        App::setLocale('en');

        // Place key in flash "old" bag (what the next HTTP request sees after redirect()->with()).
        $this->withSession([
            'success_key' => 'auth.flash.register_candidate',
            '_flash' => ['new' => [], 'old' => ['success_key']],
        ])->get(route('frontend.candidate.dashboard'))
            ->assertOk()
            ->assertSee('Your account was created successfully.');

        // Flash is single-use: subsequent request must not keep the banner.
        $this->get(route('frontend.candidate.dashboard'))
            ->assertOk()
            ->assertDontSee('Your account was created successfully.');
    }

    public function test_guest_is_redirected_from_candidate_dashboard(): void
    {
        $this->get(route('frontend.candidate.dashboard'))
            ->assertRedirect(route('frontend.account.login'));
    }

    public function test_login_flash_is_not_registration_message(): void
    {
        ApiSession::store('cand-token', [
            'id' => 7,
            'email' => 'c6@example.com',
            'name' => 'Cand',
            'account_type' => 'candidate',
        ]);

        App::setLocale('en');

        $html = $this->withSession(['success_key' => 'auth.flash.login_success'])
            ->get(route('frontend.candidate.dashboard'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Signed in successfully.', $html);
        $this->assertStringNotContainsString('Your account was created successfully.', $html);
    }
}

<?php

namespace Tests\Unit;

use App\Support\Frontend\TopbarAccount;
use App\Support\Frontend\WorkspaceNavigator;
use Tests\TestCase;

class TopbarAccountTest extends TestCase
{
    public function test_role_label_uses_account_type_and_roles(): void
    {
        $this->assertSame(
            __('common.role_labels.candidate'),
            TopbarAccount::roleLabel(['account_type' => 'candidate', 'roles' => ['candidate']])
        );
        $this->assertSame(
            __('common.role_labels.legacy_admin'),
            TopbarAccount::roleLabel(['account_type' => 'admin', 'roles' => []])
        );
        $this->assertSame(
            __('common.role_labels.admin'),
            TopbarAccount::roleLabel([
                'account_type' => 'admin',
                'roles' => ['super_admin'],
                'identity' => \App\Frontend\Auth\ApiSession::REALM_PLATFORM_ADMIN,
            ])
        );
        $this->assertSame(
            __('common.role_labels.interviewer'),
            TopbarAccount::roleLabel(['account_type' => 'company', 'roles' => ['interviewer']])
        );
        $this->assertSame(
            __('common.role_labels.hr'),
            TopbarAccount::roleLabel(['account_type' => 'company', 'roles' => ['hr', 'interviewer']])
        );
        $this->assertSame(
            WorkspaceNavigator::SHELL_COMPANY,
            WorkspaceNavigator::resolveShell(['account_type' => 'company', 'roles' => ['hr', 'interviewer']])
        );
    }

    public function test_safe_avatar_url_rejects_storage_paths(): void
    {
        $this->assertNull(TopbarAccount::safeAvatarUrl('photos/user-1.jpg'));
        $this->assertNull(TopbarAccount::safeAvatarUrl('data:image/png;base64,abc'));
        $this->assertSame(
            'https://cdn.example.com/a.png',
            TopbarAccount::safeAvatarUrl('https://cdn.example.com/a.png')
        );
    }

    public function test_initials_from_name(): void
    {
        $this->assertSame('JD', TopbarAccount::initials('Jane Doe'));
        $this->assertSame('A', TopbarAccount::initials('Ada'));
    }
}

<?php

namespace Tests\Unit;

use App\Support\Frontend\WorkspaceNavigator;
use Tests\TestCase;

class WorkspaceNavigatorTest extends TestCase
{
    public function test_resolves_shell_from_account_type(): void
    {
        $this->assertSame('candidate', WorkspaceNavigator::resolveShell(['account_type' => 'candidate']));
        $this->assertSame('admin', WorkspaceNavigator::resolveShell(['account_type' => 'admin']));
        $this->assertSame('company', WorkspaceNavigator::resolveShell(['account_type' => 'company', 'roles' => ['company_admin']]));
    }

    public function test_interviewer_only_company_user_gets_interviewer_shell(): void
    {
        $shell = WorkspaceNavigator::resolveShell([
            'account_type' => 'company',
            'roles' => ['interviewer'],
        ]);

        $this->assertSame('interviewer', $shell);
        $this->assertSame('frontend.partials.sidebar-interviewer', WorkspaceNavigator::sidebarPartial([
            'account_type' => 'company',
            'roles' => [['name' => 'interviewer']],
        ]));
    }

    public function test_dual_role_company_admin_keeps_company_shell(): void
    {
        $shell = WorkspaceNavigator::resolveShell([
            'account_type' => 'company',
            'roles' => ['company_admin', 'interviewer'],
        ]);

        $this->assertSame('company', $shell);
    }

    public function test_does_not_use_url_or_query_for_shell(): void
    {
        $this->get('/candidate/dashboard?role=admin');

        $shell = WorkspaceNavigator::resolveShell([
            'account_type' => 'candidate',
            'roles' => [],
        ]);

        $this->assertSame('candidate', $shell);
        $this->assertSame('frontend.partials.sidebar-candidate', WorkspaceNavigator::sidebarPartial([
            'account_type' => 'candidate',
        ]));
    }
}

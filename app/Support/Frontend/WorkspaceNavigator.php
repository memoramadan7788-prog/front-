<?php

namespace App\Support\Frontend;

use App\Frontend\Auth\ApiSession;

/**
 * Resolves the workspace navigation shell from the authenticated ApiSession user.
 *
 * Shell selection is based on Backend-issued session identity (account_type + roles),
 * never on query-string `role` or other client-controlled fields. Hiding links is not
 * authorization — route middleware remains the access control.
 */
class WorkspaceNavigator
{
    public const SHELL_CANDIDATE = 'candidate';

    public const SHELL_COMPANY = 'company';

    public const SHELL_INTERVIEWER = 'interviewer';

    public const SHELL_ADMIN = 'admin';

    /** @return self::SHELL_* */
    public static function resolveShell(?array $user = null): string
    {
        $user ??= ApiSession::user() ?? [];
        $accountType = strtolower((string) ($user['account_type'] ?? ''));

        return match ($accountType) {
            'admin' => self::SHELL_ADMIN,
            'company' => self::resolveCompanyShell($user),
            'candidate' => self::SHELL_CANDIDATE,
            default => self::SHELL_CANDIDATE,
        };
    }

    public static function sidebarPartial(?array $user = null): string
    {
        return match (self::resolveShell($user)) {
            self::SHELL_ADMIN => 'frontend.partials.sidebar-admin',
            self::SHELL_COMPANY => 'frontend.partials.sidebar-company',
            self::SHELL_INTERVIEWER => 'frontend.partials.sidebar-interviewer',
            default => 'frontend.partials.sidebar-candidate',
        };
    }

    /**
     * Role names from the session user blob (strings or {name: ...} objects).
     *
     * @param  array<string, mixed>  $user
     * @return list<string>
     */
    public static function roleNames(array $user): array
    {
        $roles = $user['roles'] ?? [];
        if (! is_array($roles)) {
            return [];
        }

        $names = [];
        foreach ($roles as $role) {
            if (is_string($role) && $role !== '') {
                $names[] = strtolower($role);
            } elseif (is_array($role) && isset($role['name']) && is_string($role['name'])) {
                $names[] = strtolower($role['name']);
            }
        }

        return array_values(array_unique($names));
    }

    /**
     * Company accounts: interviewer-only → interviewer shell; otherwise company (HR/admin) shell.
     * Dual-role users (e.g. company_admin + interviewer) keep the company shell so hiring
     * navigation stays available. Interviewer routes remain reachable; Backend enforces access.
     *
     * @param  array<string, mixed>  $user
     */
    private static function resolveCompanyShell(array $user): string
    {
        $roles = self::roleNames($user);
        $hasInterviewer = in_array('interviewer', $roles, true);
        $hasCompanyOps = (bool) array_intersect($roles, ['company_admin', 'hr', 'employee']);

        if ($hasInterviewer && ! $hasCompanyOps) {
            return self::SHELL_INTERVIEWER;
        }

        return self::SHELL_COMPANY;
    }
}

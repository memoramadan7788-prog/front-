<?php

namespace App\Support\Frontend;

use App\Frontend\Auth\ApiSession;

/**
 * Builds authenticated topbar / account-menu presentation from ApiSession only.
 * Never invents names, emails, avatars, or unread counts.
 */
class TopbarAccount
{
    /**
     * @return array{
     *     name: string,
     *     email: string|null,
     *     initials: string,
     *     role_label: string,
     *     account_type: string|null,
     *     shell: string,
     *     avatar_url: string|null,
     *     profile_route: string|null,
     *     security_route: string
     * }
     */
    public static function fromSession(?array $user = null): array
    {
        $user ??= ApiSession::user() ?? [];
        $shell = WorkspaceNavigator::resolveShell($user);
        $name = trim((string) ($user['name'] ?? ''));
        if ($name === '') {
            $name = __('common.account_fallback_name');
        }

        $email = $user['email'] ?? null;
        $email = is_string($email) && trim($email) !== '' ? trim($email) : null;

        $avatarUrl = self::safeAvatarUrl(
            data_get($user, 'avatar_url')
                ?? data_get($user, 'candidate_profile.photo_url')
                ?? data_get($user, 'photo_url')
        );

        return [
            'name' => $name,
            'email' => $email,
            'initials' => self::initials($name),
            'role_label' => self::roleLabel($user, $shell),
            'account_type' => ApiSession::accountType(),
            'shell' => $shell,
            'avatar_url' => $avatarUrl,
            'profile_route' => self::profileRouteName($shell),
            'security_route' => 'frontend.account.change-password',
        ];
    }

    /**
     * @param  array<string, mixed>  $user
     */
    public static function roleLabel(array $user, ?string $shell = null): string
    {
        $shell ??= WorkspaceNavigator::resolveShell($user);
        $roles = WorkspaceNavigator::roleNames($user);

        if ($shell === WorkspaceNavigator::SHELL_ADMIN) {
            // Do not label legacy User admins as platform Super Admin — that UI claim is misleading.
            if (ApiSession::isPlatformAdmin()
                || ($user['identity'] ?? null) === ApiSession::REALM_PLATFORM_ADMIN) {
                return __('common.role_labels.admin');
            }

            return __('common.role_labels.legacy_admin');
        }

        if ($shell === WorkspaceNavigator::SHELL_INTERVIEWER) {
            return __('common.role_labels.interviewer');
        }

        if ($shell === WorkspaceNavigator::SHELL_COMPANY) {
            if (in_array('hr', $roles, true)) {
                return __('common.role_labels.hr');
            }
            if (in_array('company_admin', $roles, true)) {
                return __('common.role_labels.company_admin');
            }
            if (in_array('employee', $roles, true)) {
                return __('common.role_labels.employee');
            }

            return __('common.role_labels.company');
        }

        return __('common.role_labels.candidate');
    }

    public static function initials(string $name): string
    {
        $parts = preg_split('/\s+/u', trim($name)) ?: [];
        $parts = array_values(array_filter($parts, static fn ($p) => is_string($p) && $p !== ''));

        if ($parts === []) {
            return '·';
        }

        $letters = array_slice($parts, 0, 2);

        return implode('', array_map(
            static fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)),
            $letters
        ));
    }

    public static function profileRouteName(string $shell): ?string
    {
        $route = match ($shell) {
            WorkspaceNavigator::SHELL_COMPANY, WorkspaceNavigator::SHELL_INTERVIEWER => 'frontend.company.profile',
            WorkspaceNavigator::SHELL_ADMIN => 'frontend.admin.dashboard',
            default => 'frontend.candidate.profile',
        };

        return \Illuminate\Support\Facades\Route::has($route) ? $route : null;
    }

    /**
     * Only allow absolute http(s) URLs for <img src>. Storage paths are not safely renderable
     * without a Backend media endpoint.
     */
    public static function safeAvatarUrl(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $url = trim($value);
        if ($url === '') {
            return null;
        }

        if (! preg_match('#^https?://#i', $url)) {
            return null;
        }

        return $url;
    }
}

<?php

namespace App\Frontend\Auth;

/**
 * Keeps the backend token + the logged-in user inside Laravel's session.
 * (Native $_SESSION is NOT used: Laravel never starts it.)
 *
 * Auth realms prevent mixing User (Candidate/Company/legacy) tokens with
 * platform Admin tokens issued by POST /api/admin/login.
 */
class ApiSession
{
    private const TOKEN = 'api.token';

    private const USER = 'api.user';

    private const REALM = 'api.realm';

    /** Candidate / Company / legacy User tokens (POST /api/auth/login). */
    public const REALM_USER = 'user';

    /** Platform Admin tokens (POST /api/admin/login → Admin model + Sanctum ability admin). */
    public const REALM_PLATFORM_ADMIN = 'platform_admin';

    public static function store(string $token, array $user, string $realm = self::REALM_USER): void
    {
        $realm = self::normalizeRealm($realm);

        session([
            self::TOKEN => $token,
            self::USER => self::normalizeUser($user, $realm),
            self::REALM => $realm,
        ]);
    }

    /**
     * Replace the cached user blob after Backend identity refresh.
     * Token and realm are left unchanged. Does nothing when no token is present.
     */
    public static function refreshUser(array $user): void
    {
        if (self::token() === null) {
            return;
        }

        session([self::USER => self::normalizeUser($user, self::realm())]);
    }

    public static function token(): ?string
    {
        return session(self::TOKEN);
    }

    public static function user(): ?array
    {
        return session(self::USER);
    }

    public static function realm(): string
    {
        return self::normalizeRealm((string) session(self::REALM, self::REALM_USER));
    }

    public static function isPlatformAdmin(): bool
    {
        return self::realm() === self::REALM_PLATFORM_ADMIN;
    }

    public static function accountType(): ?string
    {
        $type = self::user()['account_type'] ?? null;

        return is_string($type) && $type !== '' ? $type : null;
    }

    public static function check(): bool
    {
        return self::token() !== null && self::user() !== null;
    }

    public static function forget(): void
    {
        session()->forget([self::TOKEN, self::USER, self::REALM]);
    }

    private static function normalizeRealm(string $realm): string
    {
        return $realm === self::REALM_PLATFORM_ADMIN
            ? self::REALM_PLATFORM_ADMIN
            : self::REALM_USER;
    }

    /** @param  array<string, mixed>  $user */
    private static function normalizeUser(array $user, string $realm = self::REALM_USER): array
    {
        $defaults = [
            'id' => $user['email'] ?? 0,
            'name' => '',
            'email' => '',
            'account_type' => $realm === self::REALM_PLATFORM_ADMIN ? 'admin' : 'candidate',
        ];

        $normalized = $user + $defaults;

        if ($realm === self::REALM_PLATFORM_ADMIN) {
            // Shell gates use account_type=admin; Backend /admin/me still revalidates.
            $normalized['account_type'] = 'admin';
            $normalized['identity'] = self::REALM_PLATFORM_ADMIN;
        }

        return $normalized;
    }
}

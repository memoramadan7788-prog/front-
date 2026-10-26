<?php

namespace App\Frontend\Auth;

/**
 * Keeps the backend token + the logged-in user inside Laravel's session.
 * (Native $_SESSION is NOT used: Laravel never starts it.)
 */
class ApiSession
{
    private const TOKEN = 'api.token';
    private const USER  = 'api.user';

    public static function store(string $token, array $user): void
    {
        session([self::TOKEN => $token, self::USER => self::normalizeUser($user)]);
    }

    /**
     * Replace the cached user blob after a Backend /auth/me (or equivalent) refresh.
     * Token is left unchanged. Does nothing when no token is present.
     */
    public static function refreshUser(array $user): void
    {
        if (self::token() === null) {
            return;
        }

        session([self::USER => self::normalizeUser($user)]);
    }

    public static function token(): ?string
    {
        return session(self::TOKEN);
    }

    public static function user(): ?array
    {
        return session(self::USER);
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
        session()->forget([self::TOKEN, self::USER]);
    }

    /** @param  array<string, mixed>  $user */
    private static function normalizeUser(array $user): array
    {
        return $user + ['id' => $user['email'] ?? 0, 'name' => '', 'email' => '', 'account_type' => 'candidate'];
    }
}

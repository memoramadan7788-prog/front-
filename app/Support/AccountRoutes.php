<?php

namespace App\Support;

use InvalidArgumentException;

class AccountRoutes
{
    public static function prefix(string $accountType): string
    {
        return match ($accountType) {
            'candidate' => 'candidate',
            'company'   => 'company',
            default => throw new InvalidArgumentException(
                "Unsupported account type: {$accountType}"
            ),
        };
    }

    public static function routeName(
        string $accountType,
        string $page
    ): string {
        $allowedPages = [
            'register',
            'register.store',
            'login',
            'login.store',
        ];

        if (! in_array($page, $allowedPages, true)) {
            throw new InvalidArgumentException(
                "Unsupported account page: {$page}"
            );
        }

        return self::prefix($accountType) . '.' . $page;
    }
}

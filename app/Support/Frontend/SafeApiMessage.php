<?php

namespace App\Support\Frontend;

use Illuminate\Support\Facades\Log;

/**
 * Prevents Backend APP_DEBUG / SQL exception payloads from reaching end users.
 */
final class SafeApiMessage
{
    /**
     * @param  array<string, mixed>|null  $context
     */
    public static function forUser(?string $message, string $fallback, ?array $context = null): string
    {
        $raw = trim((string) $message);

        if ($raw !== '' && self::looksSensitive($raw)) {
            Log::warning('Suppressed sensitive Backend API error from user flash.', array_filter([
                'context' => $context,
                'snippet' => mb_substr($raw, 0, 180),
            ]));

            return $fallback;
        }

        if ($raw === '' || mb_strlen($raw) > 240) {
            if ($raw !== '') {
                Log::warning('Suppressed oversized Backend API error from user flash.', array_filter([
                    'context' => $context,
                    'length' => mb_strlen($raw),
                ]));
            }

            return $fallback;
        }

        // Strip accidental "Error: " prefixes from AdminAPI wrappers.
        $clean = preg_replace('/^Error:\s*/i', '', $raw) ?? $raw;

        return trim($clean) !== '' ? trim($clean) : $fallback;
    }

    public static function looksSensitive(string $message): bool
    {
        return preg_match(
            '/SQLSTATE|Base table or view not found|Integrity constraint|Foreign key constraint'
            .'|Stack trace|Illuminate\\\\|Connection:\s*mysql|Host:\s*\d|Database:\s*\w+'
            .'|SQL:\s*select|SQL:\s*insert|SQL:\s*update|audit_logs_user_id_foreign/i',
            $message
        ) === 1;
    }
}

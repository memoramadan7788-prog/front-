<?php

namespace App\Support;

/**
 * Small helpers so the views do not depend on the exact envelope the API returns
 * (plain list, {data: [...]} or a Laravel paginator).
 */
class Rows
{
    /** @return array<int, mixed> */
    public static function from(mixed $payload): array
    {
        if (! is_array($payload)) {
            return [];
        }

        if (array_key_exists('data', $payload) && is_array($payload['data'])) {
            return array_is_list($payload['data']) ? $payload['data'] : [$payload['data']];
        }

        return array_is_list($payload) ? $payload : [$payload];
    }

    /** Pagination links block when the API returned a paginator. */
    public static function meta(mixed $payload): ?array
    {
        return is_array($payload) && isset($payload['current_page'], $payload['last_page']) ? $payload : null;
    }

    public static function get(mixed $row, string $key, mixed $default = null): mixed
    {
        return data_get($row, $key, $default);
    }

    public static function date(mixed $value, string $format = 'Y-m-d H:i'): string
    {
        if (empty($value)) {
            return '—';
        }

        try {
            return \Illuminate\Support\Carbon::parse($value)->format($format);
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    public static function money(mixed $amount, ?string $currency = null, ?string $period = null): string
    {
        if ($amount === null || $amount === '') {
            return '—';
        }

        return trim(number_format((float) $amount) . ' ' . ($currency ?? '') . ($period ? ' / ' . $period : ''));
    }

    public static function badgeVariant(?string $status): string
    {
        return match (strtolower((string) $status)) {
            'active', 'approved', 'accepted', 'open', 'published', 'hired', 'passed', 'booked', 'completed', 'available' => 'success',
            'pending', 'draft', 'screening', 'interview', 'offer', 'submitted', 'sent', 'scheduled' => 'pending',
            'rejected', 'banned', 'suspended', 'blocked', 'deactivated', 'deleted', 'failed', 'expired', 'cancelled', 'canceled', 'closed' => 'error',
            'removed' => 'neutral',
            'applied' => 'info',
            default => 'neutral',
        };
    }
}

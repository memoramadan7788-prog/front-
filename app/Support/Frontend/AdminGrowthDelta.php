<?php

namespace App\Support\Frontend;

/**
 * Safe period-over-period growth for Super Admin KPI cards.
 *
 * Never invents a 100% growth rate when the previous period is zero.
 */
final class AdminGrowthDelta
{
    /**
     * @return array{
     *     available: bool,
     *     current: ?int,
     *     previous: ?int,
     *     percent: ?int,
     *     direction: 'up'|'down'|'flat'|null,
     *     mode: 'percent'|'new_baseline'|'flat_zero'|'unavailable'
     * }
     */
    public static function from(?int $current, ?int $previous, bool $available = true): array
    {
        if (! $available || $current === null || $previous === null) {
            return [
                'available' => false,
                'current' => $current,
                'previous' => $previous,
                'percent' => null,
                'direction' => null,
                'mode' => 'unavailable',
            ];
        }

        if ($previous > 0) {
            $pct = (int) round((($current - $previous) / $previous) * 100);

            return [
                'available' => true,
                'current' => $current,
                'previous' => $previous,
                'percent' => $pct,
                'direction' => $pct > 0 ? 'up' : ($pct < 0 ? 'down' : 'flat'),
                'mode' => 'percent',
            ];
        }

        // previous == 0: percent change is undefined / misleading.
        if ($current > 0) {
            return [
                'available' => true,
                'current' => $current,
                'previous' => $previous,
                'percent' => null,
                'direction' => 'up',
                'mode' => 'new_baseline',
            ];
        }

        return [
            'available' => true,
            'current' => 0,
            'previous' => 0,
            'percent' => 0,
            'direction' => 'flat',
            'mode' => 'flat_zero',
        ];
    }
}

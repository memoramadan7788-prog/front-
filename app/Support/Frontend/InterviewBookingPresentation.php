<?php

namespace App\Support\Frontend;

use Carbon\CarbonImmutable;

/**
 * Frontend-only interview booking / reschedule presentation (F9D / WF6).
 * Proposed mock contract aligned to ERD interviews + interview_bookings + interviewer_availability.
 * Not a real reservation or calendar system.
 */
class InterviewBookingPresentation
{
    /** Presentation "now" for eligibility demos (matches workspace footer date). */
    public const DEMO_NOW = '2026-10-20T12:00:00-05:00';

    public const CUTOFF_HOURS = 24;

    public const BOOKING_AVAILABLE = 'available';

    public const BOOKING_SELECTED = 'selected';

    public const BOOKING_TAKEN = 'taken';

    public const BOOKING_EXPIRED = 'expired';

    public const BOOKING_PENDING = 'pending_confirmation';

    public const BOOKING_UNAVAILABLE = 'unavailable';

    public const BOOKING_CONFLICT = 'conflict';

    /**
     * Enrich a candidate/company interview row with scheduling presentation fields.
     *
     * @param  array<string, mixed>  $interview
     * @return array<string, mixed>
     */
    public static function enrichInterview(array $interview, ?string $demoOverride = null): array
    {
        $startsAt = self::resolveStartsAt($interview);
        $statusKey = $interview['status_key'] ?? 'confirmed';
        $override = $demoOverride ?? (string) request()->query('reschedule', '');

        $eligibility = self::rescheduleEligibility($startsAt, $statusKey, $override);

        $interview['starts_at'] = $startsAt?->toIso8601String();
        $interview['timezone'] = $interview['timezone'] ?? 'America/Chicago';
        $interview['timezone_label'] = $interview['timezone_label'] ?? 'Central Time (UTC−05:00)';
        $interview['round_no'] = $interview['round_no'] ?? 1;
        $interview['round_label'] = $interview['round_label'] ?? __('candidate.interviews.round_label', ['round' => $interview['round_no']]);
        $interview['meeting_type'] = $interview['meeting_type'] ?? ($interview['type'] ?? 'Remote');
        $interview['booking_status'] = $interview['booking_status'] ?? match ($statusKey) {
            'cancelled' => 'cancelled',
            'completed' => 'completed',
            'pending' => 'pending',
            default => 'confirmed',
        };
        $interview['can_reschedule'] = $eligibility['can_reschedule'];
        $interview['can_cancel'] = $eligibility['can_cancel'];
        $interview['reschedule_eligible'] = $eligibility['can_reschedule'];
        $interview['reschedule_reason_key'] = $eligibility['reason_key'];
        $interview['reschedule_reason'] = $eligibility['reason'];
        $interview['reschedule_cutoff_hours'] = self::CUTOFF_HOURS;
        $interview['hours_until_start'] = $eligibility['hours_until'];
        $interview['cutoff_note'] = __('candidate.interviews.cutoff_note', ['hours' => self::CUTOFF_HOURS]);
        $interview['authority_note'] = __('candidate.interviews.scheduling_authority');

        return $interview;
    }

    /**
     * @return array{can_reschedule: bool, can_cancel: bool, reason_key: string, reason: string, hours_until: ?float}
     */
    public static function rescheduleEligibility(?CarbonImmutable $startsAt, string $statusKey, string $override = ''): array
    {
        if (in_array($statusKey, ['completed', 'cancelled'], true)) {
            $key = $statusKey === 'cancelled' ? 'cancelled' : 'completed';

            return [
                'can_reschedule' => false,
                'can_cancel' => false,
                'reason_key' => $key,
                'reason' => __('candidate.interviews.reschedule_blocked_'.$key),
                'hours_until' => null,
            ];
        }

        if ($override === 'blocked' || $override === 'inside') {
            return [
                'can_reschedule' => false,
                'can_cancel' => false,
                'reason_key' => 'inside_cutoff',
                'reason' => __('candidate.interviews.reschedule_blocked_cutoff', ['hours' => self::CUTOFF_HOURS]),
                'hours_until' => 12.0,
            ];
        }

        if ($override === 'allowed' || $override === 'outside') {
            return [
                'can_reschedule' => true,
                'can_cancel' => true,
                'reason_key' => 'outside_cutoff',
                'reason' => __('candidate.interviews.reschedule_allowed', ['hours' => self::CUTOFF_HOURS]),
                'hours_until' => 48.0,
            ];
        }

        if ($startsAt === null) {
            return [
                'can_reschedule' => false,
                'can_cancel' => false,
                'reason_key' => 'unknown',
                'reason' => __('candidate.interviews.reschedule_blocked_unknown'),
                'hours_until' => null,
            ];
        }

        $now = CarbonImmutable::parse(self::DEMO_NOW);
        $hoursUntil = ($startsAt->getTimestamp() - $now->getTimestamp()) / 3600;
        $outside = $hoursUntil >= self::CUTOFF_HOURS;

        return [
            'can_reschedule' => $outside,
            'can_cancel' => $outside,
            'reason_key' => $outside ? 'outside_cutoff' : 'inside_cutoff',
            'reason' => $outside
                ? __('candidate.interviews.reschedule_allowed', ['hours' => self::CUTOFF_HOURS])
                : __('candidate.interviews.reschedule_blocked_cutoff', ['hours' => self::CUTOFF_HOURS]),
            'hours_until' => round($hoursUntil, 1),
        ];
    }

    /**
     * Normalize schedule slots with exclusive-booking presentation states.
     *
     * @param  list<array<string, mixed>>  $slots
     * @return list<array<string, mixed>>
     */
    public static function enrichSlots(array $slots): array
    {
        return array_values(array_map(static function (array $slot): array {
            $state = $slot['state'] ?? null;
            if ($state === null) {
                if (! empty($slot['conflict_on_confirm'])) {
                    $state = self::BOOKING_CONFLICT;
                } elseif (! empty($slot['unavailable'])) {
                    $state = self::BOOKING_TAKEN;
                } elseif (! empty($slot['expired'])) {
                    $state = self::BOOKING_EXPIRED;
                } else {
                    $state = self::BOOKING_AVAILABLE;
                }
            }

            $slot['state'] = $state;
            $slot['unavailable'] = in_array($state, [self::BOOKING_TAKEN, self::BOOKING_EXPIRED, self::BOOKING_UNAVAILABLE], true);
            $slot['state_label'] = match ($state) {
                self::BOOKING_TAKEN => __('company.interviews.slot_taken'),
                self::BOOKING_EXPIRED => __('company.interviews.slot_expired'),
                self::BOOKING_CONFLICT => __('company.interviews.slot_conflict_risk'),
                self::BOOKING_PENDING => __('company.interviews.slot_pending'),
                self::BOOKING_UNAVAILABLE => __('company.interviews.slot_unavailable'),
                default => __('company.interviews.slot_available'),
            };

            return $slot;
        }, $slots));
    }

    /**
     * @param  array<string, mixed>  $interview
     */
    private static function resolveStartsAt(array $interview): ?CarbonImmutable
    {
        if (! empty($interview['starts_at'])) {
            return CarbonImmutable::parse((string) $interview['starts_at']);
        }

        $day = $interview['day_key'] ?? null;
        if (! is_string($day) || $day === '') {
            return null;
        }

        // Default portfolio window used across NS-204 samples.
        $time = $interview['starts_time'] ?? '10:00:00';

        return CarbonImmutable::parse($day.'T'.$time, 'America/Chicago');
    }
}

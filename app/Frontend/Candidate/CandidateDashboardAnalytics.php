<?php

namespace App\Frontend\Candidate;

use Carbon\Carbon;
use Throwable;

/**
 * Builds Candidate Overview chart payloads from already-fetched Backend rows.
 * No HTTP here — keeps aggregation separate from presentation.
 */
class CandidateDashboardAnalytics
{
    /**
     * Verified Backend application.status values for pipeline distribution.
     *
     * @var list<string>
     */
    public const PIPELINE_STATUSES = [
        'submitted',
        'screening',
        'shortlisted',
        'interviewing',
        'offered',
        'hired',
        'rejected',
        'withdrawn',
    ];

    /** @var list<int> */
    public const ACTIVITY_PERIODS = [7, 30, 90];

    /**
     * @param  list<array<string, mixed>>  $applications
     * @return array<string, mixed>
     */
    public function activity(array $applications, bool $catalogComplete, ?Carbon $now = null): array
    {
        if (! $catalogComplete) {
            return [
                'available' => false,
                'empty' => false,
                'reason' => 'incomplete',
                'default_period' => 30,
                'periods' => new \stdClass,
            ];
        }

        foreach ($applications as $row) {
            $appliedAt = data_get($row, 'applied_at');
            if (! is_string($appliedAt) || trim($appliedAt) === '') {
                return [
                    'available' => false,
                    'empty' => false,
                    'reason' => 'missing_dates',
                    'default_period' => 30,
                    'periods' => new \stdClass,
                ];
            }

            try {
                Carbon::parse($appliedAt);
            } catch (Throwable) {
                return [
                    'available' => false,
                    'empty' => false,
                    'reason' => 'missing_dates',
                    'default_period' => 30,
                    'periods' => new \stdClass,
                ];
            }
        }

        $now ??= Carbon::now();
        $periods = [];
        $anyActivity = false;

        foreach (self::ACTIVITY_PERIODS as $days) {
            $series = $this->buildDailySeries($applications, $days, $now);
            $periods[(string) $days] = $series;
            if ($series['total'] > 0) {
                $anyActivity = true;
            }
        }

        return [
            'available' => true,
            'empty' => ! $anyActivity,
            'reason' => null,
            'default_period' => 30,
            'periods' => $periods,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $applications
     * @return array<string, mixed>
     */
    public function pipeline(array $applications, bool $catalogComplete, ?int $kpiTotal): array
    {
        if (! $catalogComplete) {
            return [
                'available' => false,
                'empty' => false,
                'reason' => 'incomplete',
                'segments' => [],
                'total' => null,
                'reconciled' => false,
                'unknown_statuses' => [],
            ];
        }

        $counts = array_fill_keys(self::PIPELINE_STATUSES, 0);
        $unknown = [];

        foreach ($applications as $row) {
            $status = strtolower(trim((string) data_get($row, 'status', '')));
            if ($status !== '' && array_key_exists($status, $counts)) {
                $counts[$status]++;
            } else {
                $key = $status !== '' ? $status : '(blank)';
                $unknown[$key] = ($unknown[$key] ?? 0) + 1;
            }
        }

        $segments = [];
        foreach ($counts as $key => $count) {
            if ($count > 0) {
                $segments[] = [
                    'key' => $key,
                    'count' => $count,
                    'known' => true,
                ];
            }
        }

        $unknownTotal = array_sum($unknown);
        if ($unknownTotal > 0) {
            $segments[] = [
                'key' => 'unknown',
                'count' => $unknownTotal,
                'known' => false,
                'statuses' => array_keys($unknown),
            ];
        }

        $total = count($applications);
        $reconciled = $kpiTotal === null || $kpiTotal === $total;

        return [
            'available' => true,
            'empty' => $total === 0,
            'reason' => null,
            'segments' => $segments,
            'total' => $total,
            'reconciled' => $reconciled,
            'unknown_statuses' => array_keys($unknown),
        ];
    }

    /**
     * @param  array<string, mixed>  $profileKpi
     * @return array<string, mixed>
     */
    public function profileRing(array $profileKpi): array
    {
        if (! ($profileKpi['available'] ?? false)) {
            return [
                'available' => false,
                'percent' => null,
                'filled' => null,
                'total_fields' => count(CandidateDashboardAdapter::PROFILE_COMPLETION_FIELDS),
                'checks' => [],
            ];
        }

        return [
            'available' => true,
            'percent' => (int) ($profileKpi['value'] ?? 0),
            'filled' => (int) ($profileKpi['filled'] ?? 0),
            'total_fields' => (int) ($profileKpi['total_fields'] ?? count(CandidateDashboardAdapter::PROFILE_COMPLETION_FIELDS)),
            'checks' => is_array($profileKpi['checks'] ?? null) ? $profileKpi['checks'] : [],
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $interviews
     * @return array<string, mixed>
     */
    public function interviewTimeline(array $interviews, bool $catalogComplete, ?Carbon $now = null): array
    {
        if (! $catalogComplete) {
            return [
                'available' => false,
                'empty' => false,
                'reason' => 'incomplete',
                'items' => [],
            ];
        }

        $now ??= Carbon::now();
        $items = [];

        foreach ($interviews as $row) {
            if (! $this->isUpcoming($row, $now)) {
                continue;
            }

            $startAt = (string) data_get($row, 'start_at');
            $items[] = [
                'id' => data_get($row, 'id'),
                'status' => strtolower(trim((string) data_get($row, 'status', ''))),
                'start_at' => $startAt,
                'end_at' => data_get($row, 'end_at'),
                'job_title' => data_get($row, 'application.job_title')
                    ?? data_get($row, 'job_title'),
                'company_name' => data_get($row, 'application.company_name')
                    ?? data_get($row, 'company_name'),
                'interview_type' => data_get($row, 'interview_type'),
                'is_online' => (bool) data_get($row, 'is_online', false),
            ];
        }

        usort($items, static function (array $a, array $b): int {
            try {
                $aTs = Carbon::parse((string) $a['start_at'])->getTimestamp();
            } catch (Throwable) {
                $aTs = PHP_INT_MAX;
            }
            try {
                $bTs = Carbon::parse((string) $b['start_at'])->getTimestamp();
            } catch (Throwable) {
                $bTs = PHP_INT_MAX;
            }

            return $aTs <=> $bTs;
        });

        return [
            'available' => true,
            'empty' => $items === [],
            'reason' => null,
            'items' => $items,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $applications
     * @return array{labels: list<string>, values: list<int>, total: int}
     */
    private function buildDailySeries(array $applications, int $days, Carbon $now): array
    {
        $end = $now->copy()->startOfDay();
        $start = $end->copy()->subDays($days - 1);

        $buckets = [];
        $labels = [];
        for ($i = 0; $i < $days; $i++) {
            $day = $start->copy()->addDays($i);
            $key = $day->toDateString();
            $buckets[$key] = 0;
            $labels[] = $key;
        }

        foreach ($applications as $row) {
            try {
                $applied = Carbon::parse((string) data_get($row, 'applied_at'))->startOfDay();
            } catch (Throwable) {
                continue;
            }

            $key = $applied->toDateString();
            if (array_key_exists($key, $buckets)) {
                $buckets[$key]++;
            }
        }

        $values = array_values($buckets);

        return [
            'labels' => $labels,
            'values' => $values,
            'total' => array_sum($values),
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function isUpcoming(array $row, Carbon $now): bool
    {
        $status = strtolower(trim((string) data_get($row, 'status', '')));
        if (! in_array($status, CandidateDashboardAdapter::INTERVIEW_UPCOMING_STATUSES, true)) {
            return false;
        }

        $startAt = data_get($row, 'start_at');
        if (! is_string($startAt) || trim($startAt) === '') {
            return false;
        }

        try {
            return Carbon::parse($startAt)->greaterThan($now);
        } catch (Throwable) {
            return false;
        }
    }
}

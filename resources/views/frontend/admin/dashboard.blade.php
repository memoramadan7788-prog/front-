@extends('frontend.layouts.app')

@section('title', __('Overview'))

@section('page_header')
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('Overview') }}</h1>
            @if (! empty($stats['generated_at']) || ! empty(data_get($analytics ?? null, 'generated_at')))
                <p class="mt-1 text-xs text-hb-muted">
                    {{ __('Updated') }}
                    {{ \App\Support\Rows::date(data_get($analytics ?? null, 'generated_at') ?? ($stats['generated_at'] ?? null)) }}
                </p>
            @endif
        </div>
        @if ($analyticsAvailable ?? false)
            <form method="GET" action="{{ route('frontend.dashboard') }}" class="flex flex-wrap items-end gap-2" data-admin-analytics-period>
                <div>
                    <label for="admin-analytics-months" class="mb-1 block text-xs font-semibold text-hb-muted">{{ __('admin.overview.period_label') }}</label>
                    <select
                        id="admin-analytics-months"
                        name="months"
                        class="min-h-10 rounded-md border border-hb-border bg-hb-surface px-3 text-sm text-hb-text focus:border-hb-blue focus:outline-none focus:ring-2 focus:ring-hb-blue/30"
                        onchange="this.form.submit()"
                    >
                        @foreach ([3, 6, 9, 12] as $opt)
                            <option value="{{ $opt }}" @selected((int) ($months ?? 6) === $opt)>{{ __('admin.overview.period_months', ['count' => $opt]) }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 max-w-[16rem] text-[11px] leading-snug text-hb-muted">{{ __('admin.overview.period_hint') }}</p>
                </div>
            </form>
        @endif
    </div>
@endsection

@section('content')
@php
    $s = is_array($stats ?? null) ? $stats : [];
    $a = is_array($analytics ?? null) ? $analytics : [];
    $analyticsAvailable = (bool) ($analyticsAvailable ?? false);
    $selectedMonths = (int) ($months ?? data_get($s, 'trend_months') ?? 6);
    $n = fn ($v) => $v === null ? __('N/A') : number_format((int) $v);

    $users = $s['users'] ?? [];
    $companies = $s['companies'] ?? [];
    $jobs = $s['jobs'] ?? [];
    $apps = $s['applications'] ?? [];
    $interviews = $s['interviews'] ?? [];
    $trend = $s['trend'] ?? [];
    $trendAvailable = (bool) ($s['trend_available'] ?? false);
    $trendSource = $s['trend_source'] ?? null;
    $trendMonths = (int) ($s['trend_months'] ?? ($trendSource === 'analytics' ? $selectedMonths : 6));
    $byStatus = $companies['by_status'] ?? [];
    $byType = $users['by_type'] ?? [];
    $inactiveAvailable = (bool) ($users['inactive_available'] ?? false);
    $interviewsAvailable = (bool) ($interviews['available'] ?? false);

    $userGrowth = is_array($users['growth'] ?? null) ? $users['growth'] : ['available' => false, 'mode' => 'unavailable'];
    $appGrowth = is_array($apps['growth'] ?? null) ? $apps['growth'] : ['available' => false, 'mode' => 'unavailable'];

    $pendingRaw = $byStatus['pending'] ?? null;
    $pending = is_numeric($pendingRaw) ? (int) $pendingRaw : 0;
    $trendMax = max(1, collect($trend)->map(fn ($t) => max((int) ($t['users'] ?? 0), (int) ($t['applications'] ?? 0)))->max() ?? 1);

    $statusValues = array_map(fn ($k) => $byStatus[$k] ?? null, ['approved', 'pending', 'suspended', 'rejected']);
    $statusKnown = ! in_array(null, $statusValues, true);
    $statusSum = $statusKnown ? array_sum(array_map('intval', $statusValues)) : null;
    $companyBarTotal = max(1, $statusSum ?? 0);
    $companiesChartEmpty = $statusKnown && $statusSum === 0;

    $typeValues = array_map(fn ($k) => $byType[$k] ?? null, ['candidate', 'company', 'admin']);
    $typeKnown = ! in_array(null, $typeValues, true);
    $typeSum = $typeKnown ? array_sum(array_map('intval', $typeValues)) : null;
    $typeBarTotal = max(1, $typeSum ?? 0);
    $usersChartEmpty = $typeKnown && $typeSum === 0;

    $funnel = is_array($a['recruitment_funnel'] ?? null) ? $a['recruitment_funnel'] : [];
    $funnelSteps = [
        'applications' => __('admin.overview.funnel_applications'),
        'shortlisted' => __('admin.overview.funnel_shortlisted'),
        'interviewing' => __('admin.overview.funnel_interviewing'),
        'offered' => __('admin.overview.funnel_offered'),
        'hired' => __('admin.overview.funnel_hired'),
    ];
    $funnelMax = max(1, (int) ($funnel['applications'] ?? 0));
    $appStatus = is_array(data_get($a, 'breakdowns.applications_by_status')) ? data_get($a, 'breakdowns.applications_by_status') : [];
    $appStatusTotal = max(1, array_sum(array_map('intval', $appStatus)));
    $monthly = is_array(data_get($a, 'trends.monthly')) ? data_get($a, 'trends.monthly') : [];
    $monthlyMax = max(1, collect($monthly)->map(fn ($t) => max(
        (int) ($t['users'] ?? 0),
        (int) ($t['applications'] ?? 0),
        (int) ($t['jobs'] ?? 0)
    ))->max() ?? 1);

    $statusMeta = [
        'approved'  => ['label' => __('Approved'),  'bar' => 'bg-hb-success'],
        'pending'   => ['label' => __('Pending'),   'bar' => 'bg-hb-pending'],
        'suspended' => ['label' => __('Suspended'), 'bar' => 'bg-hb-muted'],
        'rejected'  => ['label' => __('Rejected'),  'bar' => 'bg-hb-error'],
    ];
    $typeMeta = [
        'candidate' => ['label' => __('Candidates'), 'bar' => 'bg-hb-blue'],
        'company'   => ['label' => __('Company users'), 'bar' => 'bg-hb-teal'],
        'admin'     => ['label' => __('Admins'), 'bar' => 'bg-hb-navy'],
    ];

    $growthLine = function (array $g, ?int $absolute) use ($n): string {
        if (! ($g['available'] ?? false)) {
            return __('Data unavailable');
        }
        $mode = $g['mode'] ?? 'unavailable';
        if ($mode === 'percent') {
            $pct = (int) ($g['percent'] ?? 0);
            $dir = $g['direction'] ?? 'flat';
            $arrow = $dir === 'down' ? '▼' : ($dir === 'up' ? '▲' : '●');
            $color = $dir === 'down' ? 'text-hb-error' : ($dir === 'up' ? 'text-hb-success' : 'text-hb-muted');

            return '<span class="font-semibold '.$color.'">'.$arrow.' '.abs($pct).'%</span> · +'.$n($absolute).' '.__('in the last 30 days');
        }
        if ($mode === 'new_baseline') {
            return '+'.$n($absolute).' '.__('admin.overview.growth_new_baseline');
        }
        if ($mode === 'flat_zero') {
            return __('admin.overview.growth_flat_zero');
        }

        return __('Data unavailable');
    };
@endphp

<x-welcome-banner :subtitle="__('Review companies, manage users and keep an eye on activity.')" />

@if (! empty($forbidden))
    <div class="mb-6 rounded-2xl border border-hb-pending/30 bg-hb-pending-soft px-5 py-4 text-sm text-hb-pending" role="status">
        {{ __('admin.overview.forbidden_body') }}
    </div>
@endif

@if (! empty($loadError))
    <div class="mb-6 rounded-2xl border border-hb-error/30 bg-hb-error-soft px-5 py-4 text-sm text-hb-error" role="alert">
        {{ __('Could not load the platform statistics.') }}
        <span class="opacity-80">{{ trim($loadError) }}</span>
    </div>
@endif

@if ($pending > 0)
    <a href="{{ route('frontend.admin.companies.index', ['status' => 'pending']) }}"
       class="mb-6 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-hb-pending/30 bg-hb-pending-soft px-5 py-4 text-sm text-hb-pending transition hover:shadow-hb-sm">
        <span class="font-semibold">{{ __(':count companies are waiting for your approval', ['count' => $pending]) }}</span>
        <span class="font-semibold">{{ __('Review now') }} &rarr;</span>
    </a>
@endif

{{-- KPI cards --}}
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" data-admin-kpis>
    <div class="hb-surface p-6" data-admin-kpi="users">
        <p class="text-xs font-semibold uppercase tracking-wide text-hb-muted">{{ __('Total users') }}</p>
        <p class="mt-2 text-3xl font-semibold tracking-tight text-hb-navy">{{ $n($users['total'] ?? null) }}</p>
        <p class="mt-2 text-sm text-hb-muted" data-admin-growth="users">{!! $growthLine($userGrowth, $users['new_30d'] ?? null) !!}</p>
    </div>

    <div class="hb-surface p-6" data-admin-kpi="companies">
        <p class="text-xs font-semibold uppercase tracking-wide text-hb-muted">{{ __('Companies') }}</p>
        <p class="mt-2 text-3xl font-semibold tracking-tight text-hb-navy">{{ $n($companies['total'] ?? null) }}</p>
        <p class="mt-2 text-sm text-hb-muted">{{ $n($byStatus['approved'] ?? null) }} {{ __('approved') }} · {{ $n($pendingRaw) }} {{ __('pending') }}</p>
    </div>

    <div class="hb-surface p-6" data-admin-kpi="jobs">
        <p class="text-xs font-semibold uppercase tracking-wide text-hb-muted">{{ __('Open jobs') }}</p>
        @if ($jobs['available'] ?? false)
            <p class="mt-2 text-3xl font-semibold tracking-tight text-hb-navy">{{ $n($jobs['published'] ?? 0) }}</p>
            <p class="mt-2 text-sm text-hb-muted">{{ __('of') }} {{ $n($jobs['total'] ?? 0) }} {{ __('jobs in total') }}</p>
        @else
            <p class="mt-2 text-3xl font-semibold tracking-tight text-hb-navy">{{ __('N/A') }}</p>
            <p class="mt-2 text-sm text-hb-muted">{{ __('Data unavailable') }}</p>
        @endif
    </div>

    <div class="hb-surface p-6" data-admin-kpi="applications">
        <p class="text-xs font-semibold uppercase tracking-wide text-hb-muted">{{ __('Applications') }}</p>
        @if ($apps['available'] ?? false)
            <p class="mt-2 text-3xl font-semibold tracking-tight text-hb-navy">{{ $n($apps['total'] ?? 0) }}</p>
            <p class="mt-2 text-sm text-hb-muted" data-admin-growth="applications">{!! $growthLine($appGrowth, $apps['last_30d'] ?? null) !!}</p>
        @else
            <p class="mt-2 text-3xl font-semibold tracking-tight text-hb-navy">{{ __('N/A') }}</p>
            <p class="mt-2 text-sm text-hb-muted">{{ __('Data unavailable') }}</p>
        @endif
    </div>
</div>

<div class="mt-4 grid gap-4 sm:grid-cols-3">
    <x-stat-card :label="__('Pending approvals')" :value="$n($pendingRaw)" :meta="__('Companies waiting for review')" :href="route('frontend.admin.companies.index', ['status' => 'pending'])" />
    <x-stat-card
        :label="__('Scheduled interviews')"
        :value="$interviewsAvailable ? $n($interviews['scheduled'] ?? null) : __('N/A')"
        :meta="$interviewsAvailable ? __('Across all companies') : __('Data unavailable')"
    />
    <x-stat-card
        :label="__('Inactive accounts')"
        :value="$inactiveAvailable ? $n($users['inactive'] ?? null) : __('N/A')"
        :meta="$inactiveAvailable ? __('Deactivated or blocked users') : __('Data unavailable')"
        :href="route('frontend.admin.users.index')"
    />
</div>

{{-- Charts --}}
@php
    $growthHeading = $trendSource === 'analytics'
        ? __('admin.overview.growth_title', ['count' => $trendMonths])
        : __('admin.overview.growth_title_fixed');
@endphp
<div class="mt-6 grid gap-4 lg:grid-cols-3">
    <x-card class="lg:col-span-2" data-admin-growth-chart data-trend-source="{{ $trendSource ?? 'none' }}" data-trend-months="{{ $trendMonths }}">
        <x-slot:header>
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 class="text-base font-bold text-hb-navy">{{ $growthHeading }}</h2>
                <div class="flex items-center gap-4 text-xs text-hb-muted">
                    <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-hb-blue"></span>{{ __('New users') }}</span>
                    <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-hb-teal"></span>{{ __('Applications') }}</span>
                </div>
            </div>
        </x-slot:header>

        @if ($trendAvailable && count($trend))
            <div class="flex h-56 items-end gap-3 sm:gap-6" role="img" aria-label="{{ $growthHeading }}">
                @foreach ($trend as $t)
                    @php
                        $u = (int) ($t['users'] ?? 0); $apBar = (int) ($t['applications'] ?? 0);
                        $uh = $u > 0 ? max(4, round($u / $trendMax * 100)) : 2;
                        $ah = $apBar > 0 ? max(4, round($apBar / $trendMax * 100)) : 2;
                    @endphp
                    <div class="flex h-full flex-1 flex-col justify-end">
                        <div class="flex flex-1 items-end justify-center gap-1.5">
                            <div class="flex w-full max-w-[22px] flex-col items-center justify-end" style="height: {{ $uh }}%">
                                <span class="mb-1 text-[10px] font-semibold text-hb-muted">{{ $u }}</span>
                                <div class="w-full flex-1 rounded-t-md bg-hb-blue"></div>
                            </div>
                            <div class="flex w-full max-w-[22px] flex-col items-center justify-end" style="height: {{ $ah }}%">
                                <span class="mb-1 text-[10px] font-semibold text-hb-muted">{{ $apBar }}</span>
                                <div class="w-full flex-1 rounded-t-md bg-hb-teal"></div>
                            </div>
                        </div>
                        <p class="mt-2 text-center text-xs text-hb-muted">{{ $t['label'] ?? $t['month'] ?? '' }}</p>
                    </div>
                @endforeach
            </div>
        @else
            <p class="py-10 text-center text-sm text-hb-muted">{{ __('Data unavailable') }}</p>
        @endif
    </x-card>

    <div class="space-y-4">
        <x-card data-admin-companies-chart>
            <x-slot:header><h2 class="text-base font-bold text-hb-navy">{{ __('Companies by status') }}</h2></x-slot:header>
            @if (! $statusKnown)
                <p class="py-8 text-center text-sm text-hb-muted">{{ __('Data unavailable') }}</p>
            @elseif ($companiesChartEmpty)
                <p class="py-8 text-center text-sm text-hb-muted" role="status">{{ __('admin.overview.chart_empty_companies') }}</p>
            @else
                <div class="flex h-3 w-full overflow-hidden rounded-full bg-hb-page">
                    @foreach ($statusMeta as $key => $m)
                        @php $v = (int) ($byStatus[$key] ?? 0); @endphp
                        @if ($v > 0)<div class="{{ $m['bar'] }}" style="width: {{ $v / $companyBarTotal * 100 }}%" title="{{ $m['label'] }}: {{ $v }}"></div>@endif
                    @endforeach
                </div>
                <ul class="mt-4 space-y-2 text-sm">
                    @foreach ($statusMeta as $key => $m)
                        <li class="flex items-center justify-between">
                            <span class="inline-flex items-center gap-2 text-hb-text"><span class="h-2.5 w-2.5 rounded-full {{ $m['bar'] }}"></span>{{ $m['label'] }}</span>
                            <span class="font-semibold text-hb-navy">{{ $n($byStatus[$key] ?? null) }}</span>
                        </li>
                    @endforeach
                </ul>
                @if (($companies['status_matches_total'] ?? null) === false)
                    <p class="mt-3 text-xs text-hb-muted" data-admin-distribution-mismatch="companies">
                        {{ __('admin.overview.distribution_mismatch', ['sum' => $statusSum, 'total' => (int) ($companies['total'] ?? 0)]) }}
                    </p>
                @endif
            @endif
        </x-card>

        <x-card data-admin-users-chart>
            <x-slot:header><h2 class="text-base font-bold text-hb-navy">{{ __('Users by type') }}</h2></x-slot:header>
            @if (! $typeKnown)
                <p class="py-8 text-center text-sm text-hb-muted">{{ __('Data unavailable') }}</p>
            @elseif ($usersChartEmpty)
                <p class="py-8 text-center text-sm text-hb-muted" role="status">{{ __('admin.overview.chart_empty_users') }}</p>
            @else
                <ul class="space-y-4 text-sm">
                    @foreach ($typeMeta as $key => $m)
                        @php $raw = $byType[$key] ?? null; $v = (int) ($raw ?? 0); @endphp
                        <li>
                            <div class="mb-1.5 flex items-center justify-between">
                                <span class="text-hb-text">{{ $m['label'] }}</span>
                                <span class="font-semibold text-hb-navy">{{ $n($raw) }}</span>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-hb-page">
                                <div class="h-full rounded-full {{ $m['bar'] }}" style="width: {{ $v / $typeBarTotal * 100 }}%"></div>
                            </div>
                        </li>
                    @endforeach
                </ul>
                @if (($users['type_matches_total'] ?? null) === false)
                    <p class="mt-3 text-xs text-hb-muted" data-admin-distribution-mismatch="users">
                        {{ __('admin.overview.distribution_mismatch', ['sum' => $typeSum, 'total' => (int) ($users['total'] ?? 0)]) }}
                    </p>
                @endif
            @endif
        </x-card>
    </div>
</div>

{{-- Detailed analytics (GET /admin/analytics) --}}
@if ($analyticsAvailable)
    <section class="mt-8 space-y-4" aria-labelledby="admin-analytics-heading" data-admin-analytics>
        <div>
            <h2 id="admin-analytics-heading" class="text-lg font-semibold tracking-tight text-hb-navy">{{ __('admin.overview.analytics_title') }}</h2>
            <p class="mt-1 text-sm text-hb-muted">{{ __('admin.overview.analytics_subtitle') }}</p>
        </div>

        <div class="grid gap-4 lg:grid-cols-3">
            <x-card class="lg:col-span-2" data-admin-monthly-chart data-months="{{ $selectedMonths }}">
                <x-slot:header>
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h3 class="text-base font-bold text-hb-navy">{{ __('admin.overview.monthly_title', ['count' => $selectedMonths]) }}</h3>
                        <div class="flex flex-wrap items-center gap-3 text-xs text-hb-muted">
                            <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-hb-blue"></span>{{ __('New users') }}</span>
                            <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-hb-teal"></span>{{ __('Applications') }}</span>
                            <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-hb-pending"></span>{{ __('admin.overview.jobs_series') }}</span>
                        </div>
                    </div>
                </x-slot:header>
                @if (count($monthly))
                    <div class="flex h-56 items-end gap-2 sm:gap-4" role="img" aria-label="{{ __('admin.overview.monthly_title', ['count' => $selectedMonths]) }}">
                        @foreach ($monthly as $t)
                            @php
                                $u = (int) ($t['users'] ?? 0);
                                $ap = (int) ($t['applications'] ?? 0);
                                $j = (int) ($t['jobs'] ?? 0);
                                $uh = $u > 0 ? max(4, round($u / $monthlyMax * 100)) : 2;
                                $ah = $ap > 0 ? max(4, round($ap / $monthlyMax * 100)) : 2;
                                $jh = $j > 0 ? max(4, round($j / $monthlyMax * 100)) : 2;
                            @endphp
                            <div class="flex h-full flex-1 flex-col justify-end">
                                <div class="flex flex-1 items-end justify-center gap-1">
                                    <div class="flex w-full max-w-[18px] flex-col items-center justify-end" style="height: {{ $uh }}%">
                                        <div class="w-full flex-1 rounded-t-md bg-hb-blue" title="{{ $u }}"></div>
                                    </div>
                                    <div class="flex w-full max-w-[18px] flex-col items-center justify-end" style="height: {{ $ah }}%">
                                        <div class="w-full flex-1 rounded-t-md bg-hb-teal" title="{{ $ap }}"></div>
                                    </div>
                                    <div class="flex w-full max-w-[18px] flex-col items-center justify-end" style="height: {{ $jh }}%">
                                        <div class="w-full flex-1 rounded-t-md bg-hb-pending" title="{{ $j }}"></div>
                                    </div>
                                </div>
                                <p class="mt-2 text-center text-[10px] text-hb-muted">{{ $t['month'] ?? '' }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="py-10 text-center text-sm text-hb-muted" role="status">{{ __('admin.overview.trend_empty') }}</p>
                @endif
            </x-card>

            <x-card data-admin-funnel data-funnel-definition="{{ $funnel['definition'] ?? 'current_status' }}">
                <x-slot:header>
                    <h3 class="text-base font-bold text-hb-navy">{{ __('admin.overview.funnel_title') }}</h3>
                    <p class="mt-0.5 text-xs text-hb-muted">
                        {{ __('admin.overview.hire_rate', ['rate' => $funnel['hire_rate'] ?? 0]) }}
                    </p>
                    <p class="mt-2 text-xs leading-snug text-hb-muted">{{ __('admin.overview.funnel_subtitle') }}</p>
                </x-slot:header>
                <ul class="space-y-3">
                    @foreach ($funnelSteps as $key => $label)
                        @php $v = (int) ($funnel[$key] ?? 0); @endphp
                        <li>
                            <div class="mb-1 flex items-center justify-between text-sm">
                                <span class="text-hb-text">{{ $label }}</span>
                                <span class="font-semibold text-hb-navy">{{ number_format($v) }}</span>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-hb-page">
                                <div class="h-full rounded-full bg-hb-blue" style="width: {{ $v / $funnelMax * 100 }}%"></div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </x-card>
        </div>

        <div class="grid gap-4 lg:grid-cols-3">
            <x-card>
                <x-slot:header><h3 class="text-base font-bold text-hb-navy">{{ __('admin.overview.pipeline_title') }}</h3></x-slot:header>
                @if ($appStatus === [])
                    <p class="py-8 text-center text-sm text-hb-muted" role="status">{{ __('admin.overview.pipeline_empty') }}</p>
                @else
                    <ul class="space-y-2 text-sm">
                        @foreach ($appStatus as $status => $count)
                            <li class="flex items-center justify-between gap-2">
                                <span class="truncate text-hb-text">{{ $status }}</span>
                                <span class="font-semibold text-hb-navy">{{ number_format((int) $count) }}</span>
                            </li>
                            <li class="mb-2">
                                <div class="h-1.5 w-full overflow-hidden rounded-full bg-hb-page">
                                    <div class="h-full rounded-full bg-hb-teal" style="width: {{ (int) $count / $appStatusTotal * 100 }}%"></div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-card>

            <x-card>
                <x-slot:header><h3 class="text-base font-bold text-hb-navy">{{ __('admin.overview.top_companies') }}</h3></x-slot:header>
                @forelse (($analytics['top_companies'] ?? []) as $row)
                    @continue(! is_array($row))
                    <div class="flex items-start justify-between gap-2 border-b border-hb-border py-2 last:border-b-0" data-admin-top-company>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-hb-navy">{{ $row['name'] ?? '—' }}</p>
                            <p class="text-xs text-hb-muted">{{ $row['status'] ?? '—' }} · {{ __('admin.overview.jobs_count', ['count' => (int) ($row['jobs'] ?? 0)]) }}</p>
                        </div>
                        <span class="shrink-0 text-sm font-semibold text-hb-navy">{{ number_format((int) ($row['applications'] ?? 0)) }}</span>
                    </div>
                @empty
                    <p class="py-8 text-center text-sm text-hb-muted" role="status">{{ __('admin.overview.top_empty') }}</p>
                @endforelse
            </x-card>

            <x-card>
                <x-slot:header><h3 class="text-base font-bold text-hb-navy">{{ __('admin.overview.top_jobs') }}</h3></x-slot:header>
                @forelse (($analytics['top_jobs'] ?? []) as $row)
                    @continue(! is_array($row))
                    <div class="flex items-start justify-between gap-2 border-b border-hb-border py-2 last:border-b-0" data-admin-top-job>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-hb-navy">{{ $row['title'] ?? '—' }}</p>
                            <p class="truncate text-xs text-hb-muted">{{ $row['company'] ?? '—' }} · {{ $row['status'] ?? '—' }}</p>
                        </div>
                        <span class="shrink-0 text-sm font-semibold text-hb-navy">{{ number_format((int) ($row['applications'] ?? 0)) }}</span>
                    </div>
                @empty
                    <p class="py-8 text-center text-sm text-hb-muted" role="status">{{ __('admin.overview.top_empty') }}</p>
                @endforelse
            </x-card>
        </div>
    </section>
@elseif (empty($forbidden))
    <div class="mt-8 rounded-2xl border border-hb-border bg-hb-page/40 px-5 py-6 text-sm text-hb-muted" role="status" data-admin-analytics-unavailable>
        <p class="font-semibold text-hb-navy">{{ __('admin.overview.analytics_unavailable_title') }}</p>
        <p class="mt-1">{{ __('admin.overview.analytics_unavailable_body') }}</p>
    </div>
@endif

{{-- Work queues --}}
<div class="mt-6 grid gap-4 lg:grid-cols-2">
    <x-card :padding="false">
        <x-slot:header>
            <div class="flex items-center justify-between gap-2">
                <h2 class="text-base font-bold text-hb-navy">{{ __('Pending company approvals') }}</h2>
                <a class="text-sm font-semibold text-hb-blue hover:underline" href="{{ route('frontend.admin.companies.index', ['status' => 'pending']) }}">{{ __('View all') }}</a>
            </div>
        </x-slot:header>
        @php $queue = $s['pending_companies'] ?? []; @endphp
        @forelse ($queue as $c)
            <div class="flex items-center justify-between gap-3 border-b border-hb-border px-6 py-3 last:border-b-0">
                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-hb-navy">{{ $c['name'] ?? '—' }}</p>
                    <p class="truncate text-xs text-hb-muted">{{ $c['industry'] ?? '—' }} · {{ \App\Support\Rows::date($c['created_at'] ?? null, 'Y-m-d') }}</p>
                </div>
                <x-button variant="light" size="sm" :href="route('frontend.admin.companies.index', ['status' => 'pending', 'search' => $c['name'] ?? ''])">{{ __('Review') }}</x-button>
            </div>
        @empty
            <p class="px-6 py-10 text-center text-sm text-hb-muted">{{ __('All caught up — nothing is waiting for review.') }}</p>
        @endforelse
    </x-card>

    <x-card :padding="false" data-admin-recent-activity data-activity-source="{{ $s['recent_activity_source'] ?? 'none' }}">
        <x-slot:header>
            <div class="flex items-center justify-between gap-2">
                <div>
                    <h2 class="text-base font-bold text-hb-navy">{{ __('Recent activity') }}</h2>
                    @if (($s['recent_activity_source'] ?? null) === 'analytics')
                        <p class="mt-0.5 text-xs text-hb-muted">{{ __('admin.overview.activity_from_analytics') }}</p>
                    @endif
                </div>
                <a class="text-sm font-semibold text-hb-blue hover:underline" href="{{ route('frontend.admin.audit-logs.index') }}">{{ __('View all') }}</a>
            </div>
        </x-slot:header>
        @forelse ($s['recent_activity'] ?? [] as $log)
            <div class="flex items-center justify-between gap-3 border-b border-hb-border px-6 py-3 last:border-b-0">
                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-hb-navy">{{ $log['action'] ?? '—' }}</p>
                    <p class="truncate text-xs text-hb-muted">
                        {{ data_get($log, 'user.name') ?? __('System') }}
                        @if (! empty($log['actor_type']))
                            · {{ $log['actor_type'] }}
                        @endif
                        · {{ $log['auditable_type'] ?? '—' }}
                    </p>
                </div>
                <time class="shrink-0 text-xs text-hb-muted" datetime="{{ $log['created_at'] ?? '' }}">{{ \App\Support\Rows::date($log['created_at'] ?? null) }}</time>
            </div>
        @empty
            <p class="px-6 py-10 text-center text-sm text-hb-muted">{{ __('Nothing to show yet.') }}</p>
        @endforelse
    </x-card>
</div>
@endsection

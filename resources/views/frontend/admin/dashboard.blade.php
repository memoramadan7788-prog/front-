@extends('frontend.layouts.app')

@section('title', __('Overview'))

@section('page_header')
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('Overview') }}</h1>
        @if (! empty($stats['generated_at']))
            <p class="text-xs text-hb-muted">{{ __('Updated') }} {{ \App\Support\Rows::date($stats['generated_at']) }}</p>
        @endif
    </div>
@endsection

@section('content')
@php
    $s = is_array($stats ?? null) ? $stats : [];
    $n = fn ($v) => $v === null ? __('N/A') : number_format((int) $v);
    $delta = function ($cur, $prev) {
        $cur = (int) $cur; $prev = (int) $prev;
        return $prev > 0 ? (int) round((($cur - $prev) / $prev) * 100) : ($cur > 0 ? 100 : 0);
    };

    $users = $s['users'] ?? [];
    $companies = $s['companies'] ?? [];
    $jobs = $s['jobs'] ?? [];
    $apps = $s['applications'] ?? [];
    $interviews = $s['interviews'] ?? [];
    $trend = $s['trend'] ?? [];
    $trendAvailable = (bool) ($s['trend_available'] ?? false);
    $byStatus = $companies['by_status'] ?? [];
    $byType = $users['by_type'] ?? [];
    $userGrowthAvailable = (bool) ($users['growth_available'] ?? false);
    $inactiveAvailable = (bool) ($users['inactive_available'] ?? false);
    $interviewsAvailable = (bool) ($interviews['available'] ?? false);

    $userDelta = $userGrowthAvailable ? $delta($users['new_30d'] ?? 0, $users['prev_30d'] ?? 0) : null;
    $appDelta = ($apps['available'] ?? false) ? $delta($apps['last_30d'] ?? 0, $apps['prev_30d'] ?? 0) : null;

    $pendingRaw = $byStatus['pending'] ?? null;
    $pending = is_numeric($pendingRaw) ? (int) $pendingRaw : 0;
    $trendMax = max(1, collect($trend)->map(fn ($t) => max((int) ($t['users'] ?? 0), (int) ($t['applications'] ?? 0)))->max() ?? 1);
    $companyTotal = max(1, array_sum(array_map(fn ($v) => (int) ($v ?? 0), $byStatus)));
    $typeTotal = max(1, array_sum(array_map(fn ($v) => (int) ($v ?? 0), $byType)));

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
@endphp

<x-welcome-banner :subtitle="__('Review companies, manage users and keep an eye on activity.')" />

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
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <div class="hb-surface p-6">
        <p class="text-xs font-semibold uppercase tracking-wide text-hb-muted">{{ __('Total users') }}</p>
        <p class="mt-2 text-3xl font-semibold tracking-tight text-hb-navy">{{ $n($users['total'] ?? null) }}</p>
        @if ($userGrowthAvailable)
            <p class="mt-2 text-sm text-hb-muted">
                <span class="font-semibold {{ $userDelta >= 0 ? 'text-hb-success' : 'text-hb-error' }}">{{ $userDelta >= 0 ? '▲' : '▼' }} {{ abs($userDelta) }}%</span>
                · +{{ $n($users['new_30d'] ?? null) }} {{ __('in the last 30 days') }}
            </p>
        @else
            <p class="mt-2 text-sm text-hb-muted">{{ __('Data unavailable') }}</p>
        @endif
    </div>

    <div class="hb-surface p-6">
        <p class="text-xs font-semibold uppercase tracking-wide text-hb-muted">{{ __('Companies') }}</p>
        <p class="mt-2 text-3xl font-semibold tracking-tight text-hb-navy">{{ $n($companies['total'] ?? null) }}</p>
        <p class="mt-2 text-sm text-hb-muted">{{ $n($byStatus['approved'] ?? null) }} {{ __('approved') }} · {{ $n($pendingRaw) }} {{ __('pending') }}</p>
    </div>

    <div class="hb-surface p-6">
        <p class="text-xs font-semibold uppercase tracking-wide text-hb-muted">{{ __('Open jobs') }}</p>
        @if ($jobs['available'] ?? false)
            <p class="mt-2 text-3xl font-semibold tracking-tight text-hb-navy">{{ $n($jobs['published'] ?? 0) }}</p>
            <p class="mt-2 text-sm text-hb-muted">{{ __('of') }} {{ $n($jobs['total'] ?? 0) }} {{ __('jobs in total') }}</p>
        @else
            <p class="mt-2 text-3xl font-semibold tracking-tight text-hb-navy">{{ __('N/A') }}</p>
            <p class="mt-2 text-sm text-hb-muted">{{ __('Data unavailable') }}</p>
        @endif
    </div>

    <div class="hb-surface p-6">
        <p class="text-xs font-semibold uppercase tracking-wide text-hb-muted">{{ __('Applications') }}</p>
        @if ($apps['available'] ?? false)
            <p class="mt-2 text-3xl font-semibold tracking-tight text-hb-navy">{{ $n($apps['total'] ?? 0) }}</p>
            <p class="mt-2 text-sm text-hb-muted">
                <span class="font-semibold {{ $appDelta >= 0 ? 'text-hb-success' : 'text-hb-error' }}">{{ $appDelta >= 0 ? '▲' : '▼' }} {{ abs($appDelta) }}%</span>
                · +{{ $n($apps['last_30d'] ?? null) }} {{ __('in the last 30 days') }}
            </p>
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
<div class="mt-6 grid gap-4 lg:grid-cols-3">
    <x-card class="lg:col-span-2">
        <x-slot:header>
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 class="text-base font-bold text-hb-navy">{{ __('Growth — last 6 months') }}</h2>
                <div class="flex items-center gap-4 text-xs text-hb-muted">
                    <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-hb-blue"></span>{{ __('New users') }}</span>
                    <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-hb-teal"></span>{{ __('Applications') }}</span>
                </div>
            </div>
        </x-slot:header>

        @if ($trendAvailable && count($trend))
            <div class="flex h-56 items-end gap-3 sm:gap-6" role="img" aria-label="{{ __('Growth — last 6 months') }}">
                @foreach ($trend as $t)
                    @php
                        $u = (int) ($t['users'] ?? 0); $a = (int) ($t['applications'] ?? 0);
                        $uh = $u > 0 ? max(4, round($u / $trendMax * 100)) : 2;
                        $ah = $a > 0 ? max(4, round($a / $trendMax * 100)) : 2;
                    @endphp
                    <div class="flex h-full flex-1 flex-col justify-end">
                        <div class="flex flex-1 items-end justify-center gap-1.5">
                            <div class="flex w-full max-w-[22px] flex-col items-center justify-end" style="height: {{ $uh }}%">
                                <span class="mb-1 text-[10px] font-semibold text-hb-muted">{{ $u }}</span>
                                <div class="w-full flex-1 rounded-t-md bg-hb-blue"></div>
                            </div>
                            <div class="flex w-full max-w-[22px] flex-col items-center justify-end" style="height: {{ $ah }}%">
                                <span class="mb-1 text-[10px] font-semibold text-hb-muted">{{ $a }}</span>
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
        <x-card>
            <x-slot:header><h2 class="text-base font-bold text-hb-navy">{{ __('Companies by status') }}</h2></x-slot:header>
            <div class="flex h-3 w-full overflow-hidden rounded-full bg-hb-page">
                @foreach ($statusMeta as $key => $m)
                    @php $v = (int) ($byStatus[$key] ?? 0); @endphp
                    @if ($v > 0)<div class="{{ $m['bar'] }}" style="width: {{ $v / $companyTotal * 100 }}%" title="{{ $m['label'] }}: {{ $v }}"></div>@endif
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
        </x-card>

        <x-card>
            <x-slot:header><h2 class="text-base font-bold text-hb-navy">{{ __('Users by type') }}</h2></x-slot:header>
            <ul class="space-y-4 text-sm">
                @foreach ($typeMeta as $key => $m)
                    @php $raw = $byType[$key] ?? null; $v = (int) ($raw ?? 0); @endphp
                    <li>
                        <div class="mb-1.5 flex items-center justify-between">
                            <span class="text-hb-text">{{ $m['label'] }}</span>
                            <span class="font-semibold text-hb-navy">{{ $n($raw) }}</span>
                        </div>
                        <div class="h-2 w-full overflow-hidden rounded-full bg-hb-page">
                            <div class="h-full rounded-full {{ $m['bar'] }}" style="width: {{ $v / $typeTotal * 100 }}%"></div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </x-card>
    </div>
</div>

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

    <x-card :padding="false">
        <x-slot:header>
            <div class="flex items-center justify-between gap-2">
                <h2 class="text-base font-bold text-hb-navy">{{ __('Recent activity') }}</h2>
                <a class="text-sm font-semibold text-hb-blue hover:underline" href="{{ route('frontend.admin.audit-logs.index') }}">{{ __('View all') }}</a>
            </div>
        </x-slot:header>
        @forelse ($s['recent_activity'] ?? [] as $log)
            <div class="flex items-center justify-between gap-3 border-b border-hb-border px-6 py-3 last:border-b-0">
                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-hb-navy">{{ $log['action'] ?? '—' }}</p>
                    <p class="truncate text-xs text-hb-muted">{{ data_get($log, 'user.name') ?? __('System') }} · {{ $log['auditable_type'] ?? '—' }}</p>
                </div>
                <span class="shrink-0 text-xs text-hb-muted">{{ \App\Support\Rows::date($log['created_at'] ?? null) }}</span>
            </div>
        @empty
            <p class="px-6 py-10 text-center text-sm text-hb-muted">{{ __('Nothing to show yet.') }}</p>
        @endforelse
    </x-card>
</div>
@endsection

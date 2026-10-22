@extends('layouts.app')

@section('title', $pageTitle ?? __('admin.dashboard.page_title'))
@section('breadcrumb', __('admin.dashboard.breadcrumb'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy sm:text-3xl">{{ __('admin.dashboard.title') }}</h1>
    <p class="mt-1 text-sm text-hb-muted">
        {{ __('admin.dashboard.meta', [
            'name' => $dashboard['admin_name'],
            'date' => $dashboard['snapshot_date'],
        ]) }}
    </p>
@endsection

@section('page_actions')
    <a
        href="{{ route('admin.settings.index') }}"
        class="hb-btn-primary"
        title="{{ __('admin.dashboard.audit_soon') }}"
    >
        {{ __('admin.dashboard.view_audit') }}
    </a>
@endsection

@section('content')
    <div data-admin-dashboard>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($dashboard['stats'] as $stat)
                @php
                    $statLabel = match ($stat['id'] ?? '') {
                        'jobs' => __('admin.dashboard.stat_jobs'),
                        'engagements' => __('admin.dashboard.stat_engagements'),
                        'review' => __('admin.dashboard.stat_review'),
                        default => __('admin.dashboard.stat_orgs'),
                    };
                    $href = null;
                    if (! empty($stat['href']) && \Illuminate\Support\Facades\Route::has($stat['href'])) {
                        $href = route($stat['href'], $stat['query'] ?? []);
                    }
                @endphp
                @if ($href)
                    <a
                        href="{{ $href }}"
                        class="hb-surface block p-5 transition hover:border-hb-blue/40 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-hb-blue/40"
                    >
                        <p class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ $statLabel }}</p>
                        <p class="mt-2 text-3xl font-semibold tracking-tight text-hb-navy">{{ $stat['value'] }}</p>
                        <p class="mt-2 text-sm text-hb-muted">{{ $stat['meta'] }}</p>
                    </a>
                @elseif (! empty($stat['pending']))
                    <button
                        type="button"
                        class="hb-surface block w-full p-5 text-start transition hover:border-hb-blue/40 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-hb-blue/40"
                        data-open-modal="admin-unavailable-modal"
                        title="{{ __('admin.dashboard.opens_later') }}"
                    >
                        <p class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ $statLabel }}</p>
                        <p class="mt-2 text-3xl font-semibold tracking-tight text-hb-navy">{{ $stat['value'] }}</p>
                        <p class="mt-2 text-sm text-hb-muted">{{ $stat['meta'] }}</p>
                    </button>
                @else
                    <div class="hb-surface p-5">
                        <p class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ $statLabel }}</p>
                        <p class="mt-2 text-3xl font-semibold tracking-tight text-hb-navy">{{ $stat['value'] }}</p>
                        <p class="mt-2 text-sm text-hb-muted">{{ $stat['meta'] }}</p>
                    </div>
                @endif
            @endforeach
        </div>

        <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1.4fr)_minmax(16rem,0.7fr)]">
            <div class="min-w-0 space-y-6">
                <section class="hb-surface overflow-hidden" aria-labelledby="admin-priorities-heading">
                    <div class="border-b border-hb-border px-5 py-4 sm:px-6">
                        <h2 id="admin-priorities-heading" class="text-base font-semibold text-hb-navy">
                            {{ __('admin.dashboard.priorities_heading') }}
                        </h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[40rem] text-start text-sm">
                            <caption class="sr-only">{{ __('admin.dashboard.priorities_heading') }}</caption>
                            <thead class="bg-hb-page text-xs font-semibold tracking-wide text-hb-muted uppercase">
                                <tr>
                                    <th scope="col" class="px-5 py-3">{{ __('admin.dashboard.col_priority') }}</th>
                                    <th scope="col" class="px-4 py-3">{{ __('admin.dashboard.col_object') }}</th>
                                    <th scope="col" class="px-4 py-3">{{ __('admin.dashboard.col_owner') }}</th>
                                    <th scope="col" class="px-5 py-3">{{ __('admin.dashboard.col_next') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-hb-border">
                                @foreach ($dashboard['priorities'] as $row)
                                    @php
                                        $title = __('admin.dashboard.'.$row['title_key']);
                                        $next = __('admin.dashboard.'.$row['next_key']);
                                        $priorityHref = null;
                                        if (! empty($row['href']) && \Illuminate\Support\Facades\Route::has($row['href'])) {
                                            $priorityHref = route($row['href'], $row['query'] ?? []);
                                        }
                                    @endphp
                                    <tr>
                                        <td class="px-5 py-4 font-semibold text-hb-navy">{{ $title }}</td>
                                        <td class="px-4 py-4 text-hb-muted">{{ $row['object'] }}</td>
                                        <td class="px-4 py-4 text-hb-text">{{ $row['owner'] }}</td>
                                        <td class="px-5 py-4">
                                            <p class="text-hb-text">{{ $next }}</p>
                                            @if ($priorityHref)
                                                <a
                                                    href="{{ $priorityHref }}"
                                                    class="hb-link-hit mt-1 inline-flex font-semibold text-hb-blue hover:underline"
                                                >
                                                    {{ __('admin.dashboard.open_queue') }}
                                                </a>
                                            @else
                                                <button
                                                    type="button"
                                                    class="mt-1 text-sm font-semibold text-hb-blue hover:underline"
                                                    data-open-modal="admin-unavailable-modal"
                                                    title="{{ __('admin.dashboard.opens_later') }}"
                                                >
                                                    {{ __('admin.dashboard.opens_later') }}
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="hb-surface overflow-hidden" aria-labelledby="admin-org-health-heading">
                    <div class="border-b border-hb-border px-5 py-4 sm:px-6">
                        <h2 id="admin-org-health-heading" class="text-base font-semibold text-hb-navy">
                            {{ __('admin.dashboard.org_health_heading') }}
                        </h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[36rem] text-start text-sm">
                            <caption class="sr-only">{{ __('admin.dashboard.org_health_heading') }}</caption>
                            <thead class="bg-hb-page text-xs font-semibold tracking-wide text-hb-muted uppercase">
                                <tr>
                                    <th scope="col" class="px-5 py-3">{{ __('admin.dashboard.col_organization') }}</th>
                                    <th scope="col" class="px-4 py-3">{{ __('admin.dashboard.col_type') }}</th>
                                    <th scope="col" class="px-4 py-3">{{ __('admin.dashboard.col_state') }}</th>
                                    <th scope="col" class="px-5 py-3">{{ __('admin.dashboard.col_activity') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-hb-border">
                                @foreach ($dashboard['org_health'] as $org)
                                    @php
                                        $typeLabel = ($org['type_key'] ?? '') === 'agency'
                                            ? __('admin.dashboard.type_agency')
                                            : __('admin.dashboard.type_company');
                                        $stateLabel = ($org['state_key'] ?? '') === 'verification_pending'
                                            ? __('admin.dashboard.state_verification_pending')
                                            : __('admin.dashboard.state_active_verified');
                                        $orgShowHref = ! empty($org['org_id']) && \Illuminate\Support\Facades\Route::has('admin.organizations.show')
                                            ? route('admin.organizations.show', $org['org_id'])
                                            : route('admin.organizations.index', [
                                                'tab' => ($org['state_key'] ?? '') === 'verification_pending' ? 'pending' : 'active',
                                                'q' => $org['name'],
                                            ]);
                                    @endphp
                                    <tr>
                                        <td class="px-5 py-4">
                                            <a
                                                href="{{ $orgShowHref }}"
                                                class="hb-link-hit font-semibold text-hb-navy hover:text-hb-blue hover:underline"
                                            >
                                                {{ $org['name'] }}
                                            </a>
                                        </td>
                                        <td class="px-4 py-4 text-hb-muted">{{ $typeLabel }}</td>
                                        <td class="px-4 py-4 text-hb-text">{{ $stateLabel }}</td>
                                        <td class="px-5 py-4 text-hb-muted">{{ $org['activity'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <aside class="space-y-6">
                <section class="hb-surface p-5 sm:p-6" aria-labelledby="admin-recent-heading">
                    <h2 id="admin-recent-heading" class="text-base font-semibold text-hb-navy">
                        {{ __('admin.dashboard.recent_heading') }}
                    </h2>
                    <ul class="mt-4 space-y-4">
                        @foreach ($dashboard['recent_events'] as $event)
                            @php
                                $eventTitle = match ($event['title_key'] ?? '') {
                                    'team_access' => __('admin.dashboard.event_team_access'),
                                    'engagement_assigned' => __('admin.dashboard.event_engagement_assigned'),
                                    default => __('admin.dashboard.event_job_changes'),
                                };
                            @endphp
                            <li class="border-b border-hb-border pb-4 last:border-0 last:pb-0">
                                <p class="text-xs font-semibold tracking-wide text-hb-muted uppercase">
                                    <time dir="ltr">{{ $event['time'] }}</time>
                                </p>
                                <p class="mt-1 text-sm font-semibold text-hb-navy">{{ $eventTitle }}</p>
                                <p class="mt-0.5 text-sm text-hb-muted">{{ $event['detail'] }}</p>
                            </li>
                        @endforeach
                    </ul>
                </section>

                <section class="hb-surface p-5 sm:p-6" aria-labelledby="admin-security-heading">
                    <h2 id="admin-security-heading" class="text-base font-semibold text-hb-navy">
                        {{ __('admin.dashboard.security_heading') }}
                    </h2>
                    <p class="mt-3 flex items-center gap-2 text-sm font-semibold text-hb-navy">
                        <span class="inline-block h-2 w-2 rounded-full bg-emerald-500" aria-hidden="true"></span>
                        {{ __('admin.dashboard.no_critical') }}
                    </p>
                    <p class="mt-3 text-sm text-hb-muted">{{ __('admin.dashboard.security_body') }}</p>
                    <a
                        href="{{ route('admin.settings.index', ['tab' => 'security']) }}"
                        class="hb-btn-outline mt-5 inline-flex"
                        title="{{ __('admin.dashboard.policies_soon') }}"
                    >
                        {{ __('admin.dashboard.review_policies') }}
                    </a>
                </section>
            </aside>
        </div>
    </div>

    @include('layouts.modal', [
        'modalId' => 'admin-unavailable-modal',
        'modalTitle' => __('admin.dashboard.unavailable_title'),
        'modalBody' => '<p>'.e(__('admin.dashboard.unavailable_body')).'</p>',
        'modalFooter' => '<button type="button" class="hb-btn-outline" data-hb-modal-close>'.e(__('common.actions.close')).'</button>',
    ])
@endsection

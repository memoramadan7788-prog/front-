@extends('layouts.app')

@section('title', $pageTitle ?? __('interviewer.dashboard.page_title'))
@section('breadcrumb', __('interviewer.dashboard.breadcrumb'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy sm:text-3xl">{{ __('interviewer.dashboard.greeting') }}</h1>
    <p class="mt-1 text-sm text-hb-muted">{{ __('interviewer.dashboard.intro') }}</p>
@endsection

@section('page_actions')
    <button
        type="button"
        class="hb-btn-primary"
        disabled
        title="{{ __('interviewer.dashboard.availability_soon') }}"
        aria-disabled="true"
    >
        {{ __('interviewer.dashboard.manage_availability') }}
    </button>
@endsection

@section('content')
    @php
        $next = $dashboard['next_interview'] ?? null;
        $overdue = $dashboard['overdue'] ?? null;
    @endphp

    <div
        data-interviewer-dashboard
        data-checklist-key="{{ $dashboard['session_checklist_key'] ?? 'hb_iv_int-ns204-portfolio_session_v1' }}"
        data-checklist-legacy-keys="{{ json_encode($dashboard['session_checklist_legacy_keys'] ?? [], JSON_UNESCAPED_UNICODE) }}"
    >
        <div class="grid gap-4 sm:grid-cols-3">
            @foreach ($dashboard['stats'] as $stat)
                @php
                    $href = ! empty($stat['href']) && \Illuminate\Support\Facades\Route::has($stat['href'])
                        ? route($stat['href'])
                        : null;
                    $anchor = $stat['anchor'] ?? null;
                @endphp
                @php
                    $statLabel = match ($stat['id'] ?? '') {
                        'prep' => __('interviewer.dashboard.stat_prep'),
                        'feedback' => __('interviewer.dashboard.stat_feedback'),
                        default => __('interviewer.dashboard.stat_upcoming'),
                    };
                @endphp
                @if ($href)
                    <a href="{{ $href }}" class="hb-surface block p-5 transition hover:border-hb-blue/40 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-hb-blue/40">
                        <p class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ $statLabel }}</p>
                        <p class="mt-2 text-3xl font-semibold tracking-tight text-hb-navy">{{ $stat['value'] }}</p>
                        <p class="mt-2 text-sm text-hb-muted">{{ $stat['meta'] }}</p>
                    </a>
                @elseif ($anchor)
                    <a href="#{{ $anchor }}" class="hb-surface block p-5 transition hover:border-hb-blue/40 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-hb-blue/40">
                        <p class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ $statLabel }}</p>
                        <p class="mt-2 text-3xl font-semibold tracking-tight text-hb-navy">{{ $stat['value'] }}</p>
                        <p class="mt-2 text-sm text-hb-muted">{{ $stat['meta'] }}</p>
                    </a>
                @else
                    <div class="hb-surface p-5">
                        <p class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ $statLabel }}</p>
                        <p class="mt-2 text-3xl font-semibold tracking-tight text-hb-navy">{{ $stat['value'] }}</p>
                        <p class="mt-2 text-sm text-hb-muted">{{ $stat['meta'] }}</p>
                    </div>
                @endif
            @endforeach
        </div>

        <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1fr)_300px]">
            <div class="min-w-0 space-y-6">
                @if ($next)
                    <section class="hb-surface p-5 sm:p-6" aria-labelledby="next-interview-heading">
                        <p class="sr-only" id="next-interview-heading">{{ __('interviewer.dashboard.next_heading') }}</p>
                        <x-badge variant="info" :dot="true">{{ $next['status'] }}</x-badge>
                        <h2 class="mt-3 text-xl font-semibold tracking-tight text-hb-navy">{{ $next['title'] }}</h2>
                        <p class="mt-2 text-sm text-hb-muted">{{ $next['role_line'] }}</p>
                        <p class="mt-1 text-sm font-medium text-hb-text">{{ $next['when_line'] }}</p>
                        <p class="mt-4 text-sm text-hb-muted">{{ $next['focus'] }}</p>
                        <div class="mt-5 flex flex-wrap gap-2">
                            <a href="{{ route('interviewer.interviews.prepare', $next['id']) }}" class="hb-btn-primary">
                                {{ __('interviewer.dashboard.prepare') }}
                            </a>
                            <a href="{{ route('interviewer.schedule.index') }}" class="hb-btn-outline">
                                {{ __('interviewer.dashboard.view_schedule') }}
                            </a>
                        </div>
                    </section>
                @endif

                <section class="hb-surface overflow-hidden" aria-labelledby="this-week-heading">
                    <div class="border-b border-hb-border px-5 py-4 sm:px-6">
                        <h2 id="this-week-heading" class="text-base font-semibold text-hb-navy">{{ __('interviewer.dashboard.this_week') }}</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[36rem] text-start text-sm">
                            <caption class="sr-only">{{ __('interviewer.dashboard.this_week') }}</caption>
                            <thead class="bg-hb-page text-xs font-semibold tracking-wide text-hb-muted uppercase">
                                <tr>
                                    <th scope="col" class="px-5 py-3">{{ __('interviewer.dashboard.col_candidate') }}</th>
                                    <th scope="col" class="px-4 py-3">{{ __('interviewer.dashboard.col_interview') }}</th>
                                    <th scope="col" class="px-4 py-3">{{ __('interviewer.dashboard.col_when') }}</th>
                                    <th scope="col" class="px-5 py-3">{{ __('interviewer.dashboard.col_action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-hb-border">
                                @forelse ($dashboard['this_week'] as $row)
                                    <tr>
                                        <td class="px-5 py-4 font-semibold text-hb-navy">{{ $row['candidate'] }}</td>
                                        <td class="px-4 py-4 text-hb-muted">{{ $row['interview'] }}</td>
                                        <td class="px-4 py-4 text-hb-text">{{ $row['when'] }}</td>
                                        <td class="px-5 py-4">
                                            <a
                                                href="{{ route('interviewer.interviews.prepare', $row['id']) }}"
                                                class="hb-link-hit font-semibold text-hb-blue hover:underline"
                                            >
                                                {{ ($row['action_key'] ?? '') === 'review_brief'
                                                    ? __('interviewer.dashboard.action_review_brief')
                                                    : __('interviewer.dashboard.action_prepare') }}
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-5 py-8 text-sm text-hb-muted">{{ __('interviewer.dashboard.empty_week') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                @if ($overdue)
                    <section id="feedback-attention" class="hb-surface p-5 sm:p-6 scroll-mt-24" aria-labelledby="feedback-heading">
                        <x-badge variant="pending" :dot="true">{{ $overdue['overdue_label'] }}</x-badge>
                        <h2 id="feedback-heading" class="mt-3 text-base font-semibold text-hb-navy">{{ __('interviewer.dashboard.feedback_heading') }}</h2>
                        <p class="mt-2 text-sm text-hb-muted">{{ $overdue['body'] }}</p>
                        <a
                            href="{{ route('interviewer.evaluations.create', $overdue['id']) }}"
                            class="hb-btn-outline mt-5 inline-flex"
                        >
                            {{ __('interviewer.dashboard.complete_feedback') }}
                        </a>
                    </section>
                @endif
            </div>

            <aside class="space-y-6">
                <section
                    id="prep-checklist"
                    class="hb-surface p-5 scroll-mt-24"
                    aria-labelledby="checklist-heading"
                    data-prep-checklist
                    data-saved-note="{{ __('interviewer.dashboard.checklist_saved') }}"
                >
                    <h2 id="checklist-heading" class="text-base font-semibold text-hb-navy">{{ __('interviewer.dashboard.checklist_heading') }}</h2>
                    <p class="mt-1 text-xs text-hb-muted">{{ __('interviewer.dashboard.checklist_purpose') }}</p>
                    <ul class="mt-4 space-y-3">
                        @foreach ($dashboard['checklist'] as $item)
                            <li>
                                <label class="flex cursor-pointer items-start gap-3 text-sm text-hb-text">
                                    <input
                                        type="checkbox"
                                        class="mt-0.5 h-4 w-4 rounded border-hb-border text-hb-blue focus:ring-hb-blue/40"
                                        data-checklist-item="{{ $item['id'] }}"
                                        @checked(! empty($item['done']))
                                    >
                                    <span data-checklist-label>{{ $item['label'] }}</span>
                                </label>
                            </li>
                        @endforeach
                    </ul>
                    <p class="mt-4 text-xs text-hb-muted" data-checklist-status role="status"></p>
                    @if (! empty($dashboard['session_checklist_interview_id']))
                        <a
                            href="{{ route('interviewer.interviews.prepare', $dashboard['session_checklist_interview_id']) }}"
                            class="mt-3 inline-flex text-sm font-semibold text-hb-blue hover:underline"
                        >
                            {{ __('interviewer.dashboard.checklist_open_prep') }}
                        </a>
                    @endif
                </section>

                <section class="hb-surface p-5" aria-labelledby="availability-heading">
                    <h2 id="availability-heading" class="text-base font-semibold text-hb-navy">{{ __('interviewer.dashboard.availability_heading') }}</h2>
                    <ul class="mt-4 space-y-2 text-sm text-hb-text">
                        @foreach ($dashboard['availability']['windows'] as $window)
                            <li>{{ $window }}</li>
                        @endforeach
                    </ul>
                    <p class="mt-3 text-xs text-hb-muted">{{ $dashboard['availability']['note'] }}</p>
                    <button
                        type="button"
                        class="hb-btn-outline mt-4 w-full"
                        disabled
                        title="{{ __('interviewer.dashboard.availability_soon') }}"
                        aria-disabled="true"
                    >
                        {{ __('interviewer.dashboard.edit_availability') }}
                    </button>
                </section>
            </aside>
        </div>

        <p class="mt-8 text-center text-xs text-hb-muted">{{ $dashboard['footer'] }}</p>
    </div>
@endsection

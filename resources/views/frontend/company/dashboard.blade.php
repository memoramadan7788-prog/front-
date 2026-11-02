@extends('frontend.layouts.app')

@section('title', __('Overview'))

@section('page_header')
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('Overview') }}</h1>
        <div class="flex items-center gap-3">
            <a href="{{ route('frontend.company.vacancies.create') }}" class="inline-flex items-center px-4 py-2 rounded-xl bg-hb-navy text-white text-sm font-medium hover:bg-hb-navy/90 transition">
                + {{ __('New vacancy') }}
            </a>
        </div>
    </div>
@endsection

@section('content')
@php
    $kpis = \App\Support\Rows::from($kpis ?? []);
    $activeRequisitions = \App\Support\Rows::from($activeRequisitions ?? []);
    $pipelineAlerts = \App\Support\Rows::from($pipelineAlerts ?? []);
    $todaysPriorities = \App\Support\Rows::from($todaysPriorities ?? []);
    $nextInterview = $nextInterview ?? null;
    $sourcingOverview = \App\Support\Rows::from($sourcingOverview ?? []);
@endphp

<div class="space-y-6">
    <x-welcome-banner :subtitle="__('Manage your vacancies, assessments, offers and team from here.')" />

    <!-- 1. Real KPIs Grid -->
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="bg-white p-5 rounded-xl border border-hb-border shadow-sm">
            <p class="text-xs font-medium text-hb-muted">{{ __('Active Vacancies') }}</p>
            <p class="text-2xl font-bold text-hb-navy mt-1">{{ data_get($kpis, 'active_vacancies_count', 0) }}</p>
        </div>
        <div class="bg-white p-5 rounded-xl border border-hb-border shadow-sm">
            <p class="text-xs font-medium text-hb-muted">{{ __('Total Applicants') }}</p>
            <p class="text-2xl font-bold text-hb-navy mt-1">{{ data_get($kpis, 'total_applicants_count', 0) }}</p>
        </div>
        <div class="bg-white p-5 rounded-xl border border-hb-border shadow-sm">
            <p class="text-xs font-medium text-hb-muted">{{ __('Interviews Scheduled') }}</p>
            <p class="text-2xl font-bold text-hb-navy mt-1">{{ data_get($kpis, 'interviews_scheduled_count', 0) }}</p>
        </div>
        <div class="bg-white p-5 rounded-xl border border-hb-border shadow-sm">
            <p class="text-xs font-medium text-hb-muted">{{ __('Pending Offers') }}</p>
            <p class="text-2xl font-bold text-hb-navy mt-1">{{ data_get($kpis, 'pending_offers_count', 0) }}</p>
        </div>
    </div>

    <!-- 2. Pipeline Attention Alerts & Today's Priorities -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Pipeline Attention Alerts -->
        <div class="bg-white rounded-xl border border-hb-border p-5 shadow-sm">
            <h2 class="text-base font-bold text-hb-navy mb-4">{{ __('Pipeline Attention Alerts') }}</h2>
            <div class="space-y-3">
                @forelse($pipelineAlerts as $alert)
                    <div class="flex items-center justify-between p-3 rounded-lg bg-hb-page/50 border border-hb-border/50">
                        <div>
                            <p class="text-sm font-semibold text-hb-navy">{{ data_get($alert, 'title') }}</p>
                            <p class="text-xs text-hb-muted">{{ data_get($alert, 'description') }}</p>
                        </div>
                        @if(filled(data_get($alert, 'action_url')))
                            <a href="{{ data_get($alert, 'action_url') }}" class="text-xs font-medium text-hb-blue hover:underline">
                                {{ __('Review') }} &rarr;
                            </a>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-hb-muted text-center py-4">{{ __('No urgent pipeline alerts at the moment.') }}</p>
                @endforelse
            </div>
        </div>

        <!-- Today's Priorities & Next Interview -->
        <div class="space-y-6">
            <!-- Next Interview Card -->
            <div class="bg-white rounded-xl border border-hb-border p-5 shadow-sm">
                <h2 class="text-base font-bold text-hb-navy mb-3">{{ __('Next Interview') }}</h2>
                @if(filled($nextInterview))
                    <div class="flex items-center justify-between bg-hb-navy/5 p-4 rounded-xl border border-hb-navy/10">
                        <div>
                            <p class="text-sm font-bold text-hb-navy">{{ data_get($nextInterview, 'candidate_name') }}</p>
                            <p class="text-xs text-hb-muted mt-0.5">{{ data_get($nextInterview, 'job_title') }}</p>
                            <p class="text-xs font-semibold text-hb-blue mt-2">🕒 {{ data_get($nextInterview, 'scheduled_at') }}</p>
                        </div>
                        <a href="{{ data_get($nextInterview, 'live_url', '#') }}" class="px-3 py-1.5 bg-hb-navy text-white text-xs font-medium rounded-lg hover:bg-hb-navy/90 transition">
                            {{ __('Join Live') }}
                        </a>
                    </div>
                @else
                    <p class="text-sm text-hb-muted text-center py-4">{{ __('No upcoming interviews scheduled for today.') }}</p>
                @endif
            </div>

            <!-- Today's Priorities -->
            <div class="bg-white rounded-xl border border-hb-border p-5 shadow-sm">
                <h2 class="text-base font-bold text-hb-navy mb-3">{{ __('Todays Priorities') }}</h2>
                <ul class="space-y-2">
                    @forelse($todaysPriorities as $priority)
                        <li class="flex items-center text-sm text-hb-navy">
                            <span class="w-2 h-2 rounded-full bg-hb-blue mr-2"></span>
                            <span class="truncate">{{ data_get($priority, 'task') }}</span>
                        </li>
                    @empty
                        <p class="text-sm text-hb-muted text-center py-2">{{ __('All priorities for today are completed.') }}</p>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <!-- 3. Active Requisitions Table & Sourcing Overview -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Active Requisitions Table (2 Cols) -->
        <div class="lg:col-span-2 bg-white rounded-xl border border-hb-border overflow-hidden shadow-sm">
            <div class="p-5 border-b border-hb-border flex items-center justify-between">
                <h2 class="text-base font-bold text-hb-navy">{{ __('Active Requisitions') }}</h2>
                <a href="{{ route('frontend.company.vacancies.index') }}" class="text-xs font-medium text-hb-blue hover:underline">
                    {{ __('View All') }} &rarr;
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-hb-page/40 text-xs font-semibold text-hb-muted border-b border-hb-border">
                            <th class="p-3 text-right">{{ __('Job Title') }}</th>
                            <th class="p-3 text-center">{{ __('Applicants') }}</th>
                            <th class="p-3 text-center">{{ __('Status') }}</th>
                            <th class="p-3 text-left">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-hb-border text-sm">
                        @forelse($activeRequisitions as $vacancy)
                            <tr class="hover:bg-hb-page/20 transition">
                                <td class="p-3 text-right font-semibold text-hb-navy">
                                    <a href="{{ route('frontend.company.vacancies.overview', data_get($vacancy, 'id')) }}" class="hover:underline">
                                        {{ data_get($vacancy, 'title') }}
                                    </a>
                                </td>
                                <td class="p-3 text-center text-hb-muted">{{ data_get($vacancy, 'applicants_count', 0) }}</td>
                                <td class="p-3 text-center">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ data_get($vacancy, 'status', 'Active') }}
                                    </span>
                                </td>
                                <td class="p-3 text-left">
                                    <a href="{{ route('frontend.company.vacancies.applications', data_get($vacancy, 'id')) }}" class="text-xs text-hb-blue hover:underline">
                                        {{ __('Manage') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-6 text-center text-sm text-hb-muted">
                                    {{ __('No active job requisitions found.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Sourcing Overview (1 Col) -->
        <div class="bg-white rounded-xl border border-hb-border p-5 shadow-sm flex flex-col justify-between">
            <div>
                <h2 class="text-base font-bold text-hb-navy mb-4">{{ __('Sourcing Overview') }}</h2>
                <div class="space-y-4">
                    @forelse($sourcingOverview as $source)
                        <div>
                            <div class="flex justify-between text-xs font-semibold text-hb-navy mb-1">
                                <span>{{ data_get($source, 'channel') }}</span>
                                <span>{{ data_get($source, 'percentage') }}%</span>
                            </div>
                            <div class="w-full bg-hb-page rounded-full h-2 overflow-hidden border border-hb-border">
                                <div class="bg-hb-navy h-2 rounded-full" style="width: {{ data_get($source, 'percentage', 0) }}%;"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-hb-muted text-center py-6">{{ __('No sourcing data available.') }}</p>
                    @endforelse
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-hb-border grid grid-cols-2 gap-2">
                <a href="{{ route('frontend.company.exams.create') }}" class="px-3 py-2 bg-hb-page hover:bg-hb-border/50 text-hb-navy text-xs font-medium rounded-lg text-center transition">
                    + {{ __('New exam') }}
                </a>
                <a href="{{ route('frontend.company.offers.create') }}" class="px-3 py-2 bg-hb-page hover:bg-hb-border/50 text-hb-navy text-xs font-medium rounded-lg text-center transition">
                    + {{ __('Send offer') }}
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
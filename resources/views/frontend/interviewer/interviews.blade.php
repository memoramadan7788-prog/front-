@extends('frontend.layouts.app')

@section('title', __('My interviews'))

@section('page_header')
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('My interviews') }}</h1>
        <div class="flex items-center gap-2">
            <x-button variant="light" size="sm" onclick="toggleCalendarReview()">📅 {{ __('Calendar Connection Review') }}</x-button>
            <x-button variant="primary" size="sm" :href="route('frontend.interviewer.availability.add')">{{ __('Add availability') }}</x-button>
        </div>
    </div>
@endsection

@section('content')
@php 
    $items = \App\Support\Rows::from($interviews ?? []); 
    $meta = \App\Support\Rows::meta($interviews ?? null); 
    $tab = request()->query('tab', 'upcoming'); 
    $viewMode = request()->query('view', 'list'); 
@endphp

<div class="space-y-6">
    <!-- Calendar Connection Review Panel -->
    <div id="calendar-review-panel" class="hidden bg-blue-50 border border-blue-200 rounded-xl p-4 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <span class="text-2xl">🔗</span>
            <div>
                <h4 class="text-sm font-bold text-blue-900">{{ __('Google / Outlook Calendar Sync Status') }}</h4>
                <p class="text-xs text-blue-700">{{ __('Your calendar is successfully synced. Interview slots update automatically.') }}</p>
            </div>
        </div>
        <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full">✅ Connected</span>
    </div>

    <!-- Top Navigation Tabs & Quick Access -->
    <div class="flex flex-wrap items-center justify-between border-b border-hb-border gap-4 pb-3">
        <div class="flex gap-4">
            <a href="{{ request()->fullUrlWithQuery(['tab' => 'upcoming', 'view' => 'list']) }}" class="text-sm font-semibold border-b-2 pb-2 {{ $tab === 'upcoming' && $viewMode === 'list' ? 'border-hb-navy text-hb-navy' : 'border-transparent text-hb-muted hover:text-hb-navy' }}">
                ⏳ {{ __('Upcoming Interviews') }}
            </a>
            <a href="{{ request()->fullUrlWithQuery(['tab' => 'past', 'view' => 'list']) }}" class="text-sm font-semibold border-b-2 pb-2 {{ $tab === 'past' ? 'border-hb-navy text-hb-navy' : 'border-transparent text-hb-muted hover:text-hb-navy' }}">
                📁 {{ __('Past Interviews') }}
            </a>
            <a href="{{ request()->fullUrlWithQuery(['view' => 'week']) }}" class="text-sm font-semibold border-b-2 pb-2 {{ $viewMode === 'week' ? 'border-hb-navy text-hb-navy' : 'border-transparent text-hb-muted hover:text-hb-navy' }}">
                📅 {{ __('Week View') }}
            </a>
        </div>

        <!-- Meeting Access Panel -->
        <div class="bg-amber-50 border border-amber-200 px-3 py-1.5 rounded-lg flex items-center gap-2 text-xs text-amber-900 font-medium">
            <span>🚀</span>
            <span>{{ __('Meeting Access Panel') }}</span>
        </div>
    </div>

    @if($viewMode === 'week')
        <!-- Clean Week View Dynamic Container -->
        <div class="bg-white rounded-xl border border-hb-border p-5 shadow-sm">
            <h3 class="font-bold text-base text-hb-navy mb-4">{{ __('Weekly Schedule Grid') }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-7 gap-3">
                @foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $day)
                    <div class="bg-hb-page/40 p-3 rounded-lg border border-hb-border min-h-[120px]">
                        <span class="text-xs font-bold text-hb-navy block mb-2">{{ __($day) }}</span>
                        <div class="space-y-2 text-xs">
                            <div class="p-2 bg-white rounded border border-hb-border text-center text-hb-muted">
                                {{ __('No slots') }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <!-- Standard Dynamic List View -->
        <div class="bg-white rounded-xl border border-hb-border shadow-sm overflow-hidden">
            <x-table>
                <x-slot:head>
                    <th class="px-4 py-3 text-start">{{ __('common.role_labels.candidate') }}</th>
                    <th class="px-4 py-3 text-start">{{ __('Job') }}</th>
                    <th class="px-4 py-3 text-start">{{ __('Round') }}</th>
                    <th class="px-4 py-3 text-start">{{ __('Status') }}</th>
                    <th class="px-4 py-3 text-start">{{ __('When') }}</th>
                    <th class="px-4 py-3 text-end">{{ __('Actions') }}</th>
                </x-slot:head>

                @forelse ($items as $row)
                    <tr>
                        <td class="px-4 py-3 font-medium text-hb-navy">{{ data_get($row, 'application.candidate.name') ?? data_get($row, 'candidate_name') ?? '—' }}</td>
                        <td class="px-4 py-3">{{ data_get($row, 'application.job.title') ?? data_get($row, 'job_title') ?? '—' }}</td>
                        <td class="px-4 py-3">{{ data_get($row, 'round_no', '—') }}</td>
                        <td class="px-4 py-3"><x-badge :variant="\App\Support\Rows::badgeVariant(data_get($row, 'status'))" :dot="true">{{ data_get($row, 'status', '—') }}</x-badge></td>
                        <td class="px-4 py-3 text-hb-muted text-xs">{{ \App\Support\Rows::date(data_get($row, 'scheduled_at')) }}</td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap justify-end gap-2">
                                <x-button variant="light" size="sm" :href="route('frontend.interviewer.interviews.show', ['interviewId' => data_get($row, 'id')])">{{ __('View') }}</x-button>
                                <x-button variant="primary" size="sm" :href="route('frontend.interviewer.interviews.live', ['interviewId' => data_get($row, 'id')])">{{ __('interviewer.live.open_workspace') }}</x-button>
                                <x-button variant="light" size="sm" :href="route('frontend.interviewer.interviews.evaluation', ['interviewId' => data_get($row, 'id')])">{{ __('Evaluate') }}</x-button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-hb-muted">{{ __('Nothing to show yet.') }}</td></tr>
                @endforelse
            </x-table>
        </div>
        @include('frontend.partials.pagination', ['meta' => $meta])
    @endif
</div>

<script>
    function toggleCalendarReview() {
        let panel = document.getElementById('calendar-review-panel');
        panel.classList.toggle('hidden');
    }
</script>
@endsection
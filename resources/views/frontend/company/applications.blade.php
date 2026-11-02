@extends('frontend.layouts.app')

@section('title', __('Applications'))

@section('page_header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('Applications') }}</h1>
        <div class="flex items-center gap-2">
            <!-- Export Button -->
            <a href="#" class="inline-flex items-center px-3 py-2 rounded-xl border border-hb-border bg-white text-hb-navy text-sm font-medium hover:bg-hb-page transition">
                📥 {{ __('Export') }}
            </a>
        </div>
    </div>
@endsection

@section('content')
@php 
    $items = \App\Support\Rows::from($applications ?? []); 
    $meta = \App\Support\Rows::meta($applications ?? null); 
    $activeTab = request()->query('tab', 'list'); // list, board, activity
@endphp

<div class="space-y-6">
    <!-- Tabs Header: Board / List / Activity -->
    <div class="flex border-b border-hb-border gap-6">
        <a href="{{ request()->fullUrlWithQuery(['tab' => 'list']) }}" class="pb-3 text-sm font-semibold border-b-2 {{ $activeTab === 'list' ? 'border-hb-navy text-hb-navy' : 'border-transparent text-hb-muted hover:text-hb-navy' }}">
            📋 {{ __('List View') }}
        </a>
        <a href="{{ request()->fullUrlWithQuery(['tab' => 'board']) }}" class="pb-3 text-sm font-semibold border-b-2 {{ $activeTab === 'board' ? 'border-hb-navy text-hb-navy' : 'border-transparent text-hb-muted hover:text-hb-navy' }}">
            📊 {{ __('Kanban Board') }}
        </a>
        <a href="{{ request()->fullUrlWithQuery(['tab' => 'activity']) }}" class="pb-3 text-sm font-semibold border-b-2 {{ $activeTab === 'activity' ? 'border-hb-navy text-hb-navy' : 'border-transparent text-hb-muted hover:text-hb-navy' }}">
            ⚡ {{ __('Activity Feed') }}
        </a>
    </div>

    <!-- Filters Section (Stage & Source Filters) -->
    <div class="bg-white p-4 rounded-xl border border-hb-border shadow-sm flex flex-wrap gap-4 items-center justify-between">
        <form method="GET" class="flex flex-wrap gap-3 items-center w-full lg:w-auto">
            <input type="hidden" name="tab" value="{{ $activeTab }}">
            
            <!-- Stage Filter -->
            <select name="stage" class="rounded-xl border border-hb-border px-3 py-2 text-sm focus:border-hb-navy focus:ring-1 focus:ring-hb-navy">
                <option value="">{{ __('All Stages') }}</option>
                <option value="applied" {{ request('stage') === 'applied' ? 'selected' : '' }}>Applied</option>
                <option value="screening" {{ request('stage') === 'screening' ? 'selected' : '' }}>Screening</option>
                <option value="interview" {{ request('stage') === 'interview' ? 'selected' : '' }}>Interview</option>
                <option value="offer" {{ request('stage') === 'offer' ? 'selected' : '' }}>Offer</option>
            </select>

            <!-- Source Filter -->
            <select name="source" class="rounded-xl border border-hb-border px-3 py-2 text-sm focus:border-hb-navy focus:ring-1 focus:ring-hb-navy">
                <option value="">{{ __('All Sources') }}</option>
                <option value="direct" {{ request('source') === 'direct' ? 'selected' : '' }}>Direct Apply</option>
                <option value="talent_pool" {{ request('source') === 'talent_pool' ? 'selected' : '' }}>Talent Pool</option>
            </select>

            <button type="submit" class="px-4 py-2 bg-hb-navy text-white text-sm font-medium rounded-xl hover:bg-hb-navy/90 transition">
                {{ __('Filter') }}
            </button>
        </form>
    </div>

    @if($activeTab === 'list')
        <!-- Bulk Actions Toolbar Form -->
        <form action="#" method="POST" id="bulk-action-form">
            @csrf
            <div class="bg-white rounded-xl border border-hb-border shadow-sm overflow-hidden">
                <!-- Bulk Actions Bar -->
                <div class="p-3 bg-hb-page/40 border-b border-hb-border flex items-center justify-between gap-4 flex-wrap">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="select-all" class="rounded border-hb-border text-hb-navy focus:ring-hb-navy">
                        <label for="select-all" class="text-xs font-semibold text-hb-navy">{{ __('Select All') }}</label>
                    </div>
                    <div class="flex items-center gap-2">
                        <select name="bulk_action" class="rounded-lg border border-hb-border px-3 py-1.5 text-xs focus:border-hb-navy">
                            <option value="">{{ __('Bulk Actions') }}</option>
                            <option value="move_stage">{{ __('Move Stage') }}</option>
                            <option value="assign_owner">{{ __('Assign Owner') }}</option>
                            <option value="send_message">{{ __('Send Message') }}</option>
                        </select>
                        <button type="submit" class="px-3 py-1.5 bg-hb-navy text-white text-xs font-medium rounded-lg hover:bg-hb-navy/90 transition">
                            {{ __('Apply') }}
                        </button>
                    </div>
                </div>

                <x-table>
                    <x-slot:head>
                        <th class="w-10 px-4 py-3"></th>
                        <th class="px-4 py-3 text-start">{{ __('common.role_labels.candidate') }}</th>
                        <th class="px-4 py-3 text-start">{{ __('Status') }}</th>
                        <th class="px-4 py-3 text-start">{{ __('Applied') }}</th>
                        <th class="px-4 py-3 text-end">{{ __('Actions') }}</th>
                    </x-slot:head>

                    @forelse ($items as $row)
                        <tr>
                            <td class="px-4 py-3">
                                <input type="checkbox" name="selected_applications[]" value="{{ data_get($row, 'id') }}" class="rounded border-hb-border text-hb-navy focus:ring-hb-navy">
                            </td>
                            <td class="px-4 py-3 font-medium text-hb-navy">{{ data_get($row, 'candidate.name') ?? data_get($row, 'candidate_name') ?? '—' }}</td>
                            <td class="px-4 py-3"><x-badge :variant="\App\Support\Rows::badgeVariant(data_get($row, 'status'))" :dot="true">{{ data_get($row, 'status', '—') }}</x-badge></td>
                            <td class="px-4 py-3 text-hb-muted text-xs">{{ \App\Support\Rows::date(data_get($row, 'applied_at')) }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap justify-end gap-2">
                                    <x-button variant="light" size="sm" :href="route('frontend.company.applications.show', ['applicationId' => data_get($row, 'id')])">{{ __('company.applicant_profile.title') }}</x-button>
                                    <x-button variant="light" size="sm" :href="route('frontend.company.applications.status', ['applicationId' => data_get($row, 'id')])">{{ __('Change status') }}</x-button>
                                    <x-button variant="light" size="sm" :href="route('frontend.company.interviews.schedule', ['application_id' => data_get($row, 'id')])">{{ __('company.interviews.schedule_for_applicant') }}</x-button>
                                    <x-button variant="light" size="sm" :href="route('frontend.company.offers.create', ['application_id' => data_get($row, 'id')])">{{ __('Send offer') }}</x-button>
                                    <x-button variant="light" size="sm" :href="route('frontend.company.applications.ats', ['applicationId' => data_get($row, 'id')])">{{ __('ats.open') }}</x-button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-hb-muted">{{ __('Nothing to show yet.') }}</td></tr>
                    @endforelse
                </x-table>
            </div>
        </form>
        @include('frontend.partials.pagination', ['meta' => $meta])

    @elseif($activeTab === 'board')
        <!-- Kanban Board View Placeholder / Structure -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @foreach(['Applied', 'Screening', 'Interview', 'Offer'] as $stage)
                <div class="bg-hb-page/50 rounded-xl p-4 border border-hb-border">
                    <h3 class="font-bold text-sm text-hb-navy mb-3 flex items-center justify-between">
                        <span>{{ __($stage) }}</span>
                        <span class="text-xs px-2 py-0.5 rounded-full bg-white border border-hb-border">0</span>
                    </h3>
                    <div class="space-y-3">
                        <div class="p-3 bg-white rounded-lg border border-hb-border text-center text-xs text-hb-muted">
                            {{ __('No applicants in this stage') }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

    @else
        <!-- Activity Feed View -->
        <div class="bg-white rounded-xl border border-hb-border p-6 shadow-sm">
            <h3 class="font-bold text-sm text-hb-navy mb-4">{{ __('Recent Pipeline Activities') }}</h3>
            <p class="text-sm text-hb-muted text-center py-6">{{ __('No recent activities recorded yet.') }}</p>
        </div>
    @endif
</div>

<script>
    document.getElementById('select-all')?.addEventListener('change', function(e) {
        document.querySelectorAll('input[name="selected_applications[]"]').forEach(cb => cb.checked = e.target.checked);
    });
</script>
@endsection
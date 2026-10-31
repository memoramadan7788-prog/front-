@extends('frontend.layouts.app')

@section('title', __('company.vacancies_management.title'))

@section('page_header')
    <div><p class="text-sm font-semibold text-hb-muted">{{ __('company.vacancies_management.eyebrow') }}</p><h1 class="mt-1 text-2xl font-semibold tracking-tight text-hb-navy">{{ __('company.vacancies_management.title') }}</h1></div>
@endsection

@section('page_actions')
    <x-button variant="primary" size="md" :href="route('frontend.company.vacancies.create')">{{ __('company.vacancies_management.new') }}</x-button>
@endsection

@section('content')
@php $draftCount = collect(\App\Support\Rows::from($vacancies ?? []))->filter(fn ($row) => strtolower((string) data_get($row, 'status')) === 'draft')->count(); @endphp
<div class="space-y-5" data-company-vacancies>
    @if (session('success_key'))<x-alert variant="success">{{ __('company.vacancies_management.saved') }}</x-alert>@endif
    @if ($loadError)<x-alert variant="error">{{ $loadError }}</x-alert>@endif
    <nav class="flex flex-wrap gap-2" aria-label="{{ __('company.vacancies_management.tabs') }}">@foreach (['open', 'draft', 'closed', 'all'] as $tab)<a href="{{ route('frontend.company.vacancies.index', array_merge(request()->except('page'), ['status' => $tab])) }}" @class(['rounded-full px-4 py-2 text-sm font-semibold', 'bg-hb-blue text-white' => $filters['status'] === $tab, 'bg-white text-hb-muted ring-1 ring-hb-border' => $filters['status'] !== $tab])>{{ __('company.vacancies_management.'.$tab) }}</a>@endforeach</nav>
    <form method="GET" action="{{ route('frontend.company.vacancies.index') }}" class="grid gap-3 rounded-2xl border border-hb-border bg-white p-4 sm:grid-cols-2 lg:grid-cols-4" data-loading-form>
        <input type="hidden" name="status" value="{{ $filters['status'] }}">
        <label class="text-xs font-semibold text-hb-muted">{{ __('company.vacancies_management.search') }}<input name="q" maxlength="120" value="{{ $filters['q'] }}" class="mt-1 w-full rounded-xl border border-hb-border px-3 py-2.5 text-sm" /></label>
        <label class="text-xs font-semibold text-hb-muted">{{ __('company.vacancies_management.location') }}<input name="location" maxlength="120" value="{{ $filters['location'] }}" class="mt-1 w-full rounded-xl border border-hb-border px-3 py-2.5 text-sm" /></label>
        <label class="text-xs font-semibold text-hb-muted">{{ __('company.vacancies_management.hiring_model') }}<select name="hiring_model" class="mt-1 w-full rounded-xl border border-hb-border px-3 py-2.5 text-sm"><option value="">{{ __('company.vacancies_management.any_model') }}</option>@foreach (['direct', 'agency'] as $model)<option value="{{ $model }}" @selected($filters['hiring_model'] === $model)>{{ __('company.jobs.hiring_model.'.$model) }}</option>@endforeach</select></label>
        <div class="flex items-end gap-2"><x-button type="submit" variant="primary" size="md" data-loading-submit><span data-submit-idle>{{ __('company.vacancies_management.apply_filters') }}</span><span data-submit-loading class="hidden" aria-hidden="true">{{ __('common.please_wait') }}</span></x-button><a class="px-3 py-2.5 text-sm text-hb-muted underline" href="{{ route('frontend.company.vacancies.index', ['status' => $filters['status']]) }}">{{ __('company.vacancies_management.clear') }}</a></div>
    </form>
    <p class="text-sm text-hb-muted" role="status">{{ __('company.vacancies_management.local_filter_scope') }}</p>

    @if ($draftCount > 0)<section class="rounded-2xl border border-amber-200 bg-amber-50 p-5"><p class="text-xs font-bold uppercase tracking-wider text-amber-800">{{ __('company.vacancies_management.draft_label') }}</p><h2 class="mt-1 text-lg font-bold text-hb-navy">{{ trans_choice('company.vacancies_management.draft_count', $draftCount, ['count' => $draftCount]) }}</h2><p class="mt-1 text-sm text-hb-muted">{{ __('company.vacancies_management.draft_hint') }}</p></section>@endif

    @if (count($items) === 0 && !$loadError)<x-empty-state :title="__('company.vacancies_management.empty_title')">{{ __('company.vacancies_management.empty_body') }}</x-empty-state>@elseif (count($items) > 0)<div class="grid gap-4">@foreach ($items as $row)
        <article class="rounded-2xl border border-hb-border bg-white p-5 shadow-sm sm:p-6"><div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between"><div class="min-w-0"><div class="flex flex-wrap items-center gap-2"><h2 class="text-lg font-bold text-hb-navy">{{ data_get($row, 'title', __('company.vacancies_management.untitled')) }}</h2><x-badge :variant="\App\Support\Rows::badgeVariant(data_get($row, 'status'))" :dot="true">{{ data_get($row, 'status', '—') }}</x-badge></div><p class="mt-2 text-sm text-hb-muted">{{ data_get($row, 'location', '—') }} <span aria-hidden="true">·</span> {{ __('company.vacancies_management.requisition_id') }} #{{ data_get($row, 'id', '—') }}</p><div class="mt-4 grid gap-3 text-sm sm:grid-cols-3"><p><span class="block text-xs text-hb-muted">{{ __('company.vacancies_management.owner') }}</span>{{ data_get($row, 'requisition_owner.name', data_get($row, 'owner.name', data_get($row, 'requisition_owner_id', '—'))) }}</p><p><span class="block text-xs text-hb-muted">{{ __('company.vacancies_management.hiring_model') }}</span>{{ data_get($row, 'hiring_model') ? __('company.jobs.hiring_model.'.data_get($row, 'hiring_model')) : '—' }}</p><p><span class="block text-xs text-hb-muted">{{ __('company.vacancies_management.pipeline') }}</span>{{ is_numeric(data_get($row, 'applications_count')) ? trans_choice('company.vacancies_management.pipeline_count', (int) data_get($row, 'applications_count'), ['count' => (int) data_get($row, 'applications_count')]) : __('company.vacancies_management.pipeline_unavailable') }}</p></div></div><div class="flex flex-wrap gap-2"><x-button variant="light" size="sm" :href="route('frontend.company.vacancies.overview', ['vacancyId' => data_get($row, 'id')])">{{ __('company.vacancies_management.overview') }}</x-button><x-button variant="light" size="sm" :href="route('frontend.company.vacancies.applications', ['vacancyId' => data_get($row, 'id')])">{{ __('company.vacancies_management.applications') }}</x-button><x-button variant="light" size="sm" :href="route('frontend.company.vacancies.update', ['vacancyId' => data_get($row, 'id')])">{{ __('company.vacancies_management.edit') }}</x-button></div></div></article>
    @endforeach</div>@endif
    @if (is_array($pagination ?? null) && $pagination['last_page'] > 1)
        <nav class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between" aria-label="{{ __('company.vacancies_management.pagination') }}">
            <p class="text-sm text-hb-muted">
                @if (is_int($pagination['total']))
                    {{ __('company.vacancies_management.pagination_summary', ['current' => $pagination['current_page'], 'last' => $pagination['last_page'], 'total' => $pagination['total']]) }}
                @else
                    {{ __('company.vacancies_management.pagination_pages', ['current' => $pagination['current_page'], 'last' => $pagination['last_page']]) }}
                @endif
            </p>
            <div class="flex items-center gap-3">
                <x-button variant="light" size="sm" :href="route('frontend.company.vacancies.index', array_merge($filters, ['page' => $pagination['current_page'] - 1]))" :disabled="$pagination['current_page'] <= 1">{{ __('company.vacancies_management.previous') }}</x-button>
                <span class="text-sm text-hb-muted" data-pagination-pages>{{ $pagination['current_page'] }} / {{ $pagination['last_page'] }}</span>
                <x-button variant="light" size="sm" :href="route('frontend.company.vacancies.index', array_merge($filters, ['page' => $pagination['current_page'] + 1]))" :disabled="$pagination['current_page'] >= $pagination['last_page']">{{ __('company.vacancies_management.next') }}</x-button>
            </div>
        </nav>
    @endif
</div>
@endsection

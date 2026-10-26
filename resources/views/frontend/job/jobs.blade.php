@extends('frontend.layouts.public')

@section('title', __('Find a job'))

@section('content')
<section class="border-b border-hb-border bg-hb-page">
<div class="hb-container py-12 lg:py-16">
<h1 class="text-4xl font-semibold tracking-tight text-hb-navy sm:text-5xl">{{ __('Find a job') }}</h1>
<form method="GET" action="{{ route('frontend.jobs.index') }}" class="hb-surface mt-8 grid gap-3 p-5 shadow-hb-md sm:grid-cols-4">
    <x-form.input name="q" :label="__('Keywords')" value="{{ request('q') }}" />
    <x-form.input name="location" :label="__('Location')" value="{{ request('location') }}" />
    <x-form.select name="work_mode" :label="__('Work mode')"><option value="">{{ __('Any') }}</option>@foreach (['onsite' => 'On-site', 'remote' => 'Remote', 'hybrid' => 'Hybrid'] as $v => $l)<option value="{{ $v }}" @selected(request('work_mode') === $v)>{{ __($l) }}</option>@endforeach</x-form.select>
    <div class="flex items-end"><x-button type="submit">{{ __('Search') }}</x-button></div>
</form>
</div>
</section>
<div class="hb-container py-10">
@php $items = \App\Support\Rows::from($jobs ?? []); $meta = $meta ?? \App\Support\Rows::meta($jobs ?? null); @endphp
<div class="grid gap-4 md:grid-cols-2">
    @forelse ($items as $row)
        <a href="{{ route('frontend.jobs.show', ['jobId' => data_get($row, 'id')]) }}" class="hb-surface group block p-6 transition duration-200 hover:-translate-y-0.5 hover:border-hb-blue/40 hover:shadow-hb-md">
            <h2 class="text-lg font-semibold text-hb-navy transition group-hover:text-hb-blue">{{ data_get($row, 'title') }}</h2>
            <p class="mt-1 text-sm text-hb-muted">{{ data_get($row, 'company.name', data_get($row, 'company_name', '')) }} · {{ data_get($row, 'location', '—') }}</p>
            <div class="mt-3 flex flex-wrap gap-2">
                <x-badge variant="info">{{ data_get($row, 'work_mode', '—') }}</x-badge>
                <x-badge>{{ data_get($row, 'employment_type', '—') }}</x-badge>
            </div>
        </a>
    @empty
        <div class="hb-surface md:col-span-2"><x-empty-state :title="__('No jobs match your search.')" /></div>
    @endforelse
</div>
@include('frontend.partials.pagination', ['meta' => $meta])
</div>
@endsection

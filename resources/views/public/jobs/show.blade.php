@extends('layouts.public')

@section('title', $pageTitle ?? __('public.jobs.show_page_title'))

@section('content')
    {{-- Figma 03 · Job Details (8:31406) --}}
    <section class="bg-hb-page">
        <div class="hb-container py-10 lg:py-14">
            <p class="text-xs font-semibold tracking-[0.12em] text-hb-muted uppercase">
                {{ __('public.jobs.show_eyebrow', ['title' => $job['title']]) }}
            </p>

            <div class="mt-6 grid gap-8 lg:grid-cols-[minmax(0,1fr)_320px]">
                <div class="space-y-8">
                    <header class="hb-surface p-6">
                        <div class="flex flex-wrap items-center gap-2">
                            <x-badge variant="info" :dot="true">{{ $job['source'] }}</x-badge>
                            <x-badge variant="success" :dot="true">{{ $job['status'] }}</x-badge>                        </div>
                        <h1 class="mt-4 text-3xl font-semibold text-hb-navy">{{ $job['title'] }}</h1>
                        <p class="mt-2 text-sm text-hb-muted">
                            @if (($job['company_slug'] ?? null) === 'northstar-labs')
                                <a href="{{ route('companies.show', $job['company_slug']) }}" class="font-medium text-hb-blue hover:underline">{{ $job['company'] }}</a>
                            @else
                                {{ $job['company'] }}
                            @endif
                            · {{ $job['code'] }} · {{ $job['discipline'] }}
                        </p>
                        <p class="mt-3 text-sm text-hb-text">
                            {{ $job['location'] }} · {{ $job['work_model'] }} · {{ $job['employment'] }} · {{ $job['salary_label'] }} USD
                        </p>
                        <p class="mt-4 text-lg font-medium text-hb-navy">{{ $job['summary'] }}</p>
                    </header>

                    <article class="hb-surface space-y-6 p-6 text-sm leading-relaxed text-hb-text">
                        @foreach (preg_split("/\n\n/", $job['description']) as $para)
                            <p class="text-hb-muted">{{ $para }}</p>
                        @endforeach

                        <div>
                            <h2 class="text-base font-semibold text-hb-navy">{{ __('public.jobs.what_you_do') }}</h2>
                            <ul class="mt-3 list-disc space-y-2 ps-5 text-hb-muted">
                                @foreach ($job['responsibilities'] as $item)
                                    <li>{{ $item }}</li>
                                @endforeach
                            </ul>
                        </div>

                        <div>
                            <h2 class="text-base font-semibold text-hb-navy">{{ __('public.jobs.requirements') }}</h2>
                            <ul class="mt-3 list-disc space-y-2 ps-5 text-hb-muted">
                                @foreach ($job['requirements'] as $item)
                                    <li>{{ $item }}</li>
                                @endforeach
                            </ul>
                        </div>

                        <div>
                            <h2 class="text-base font-semibold text-hb-navy">{{ __('public.jobs.benefits') }}</h2>
                            <p class="mt-3 text-hb-muted">{{ $job['benefits'] }}</p>
                        </div>

                        <div>
                            <h2 class="text-base font-semibold text-hb-navy">{{ __('public.jobs.hiring_process') }}</h2>
                            <div class="mt-4">
                                <x-timeline :steps="collect(__('public.jobs.timeline'))->map(fn ($label) => ['label' => $label, 'status' => 'pending'])->all()" />
                            </div>
                            <p class="mt-4 text-hb-muted">{{ $job['process'] }}</p>
                            <p class="mt-3 text-hb-muted">
                                {{ __('public.jobs.inclusive_note', ['company' => $job['company']]) }}
                            </p>
                        </div>
                    </article>
                </div>

                <aside class="space-y-4 lg:sticky lg:top-28 lg:self-start">
                    <div class="hb-surface p-5">
                        <h2 class="text-sm font-semibold text-hb-navy">{{ __('public.jobs.sidebar_next') }}</h2>
                        <p class="mt-3 text-2xl font-semibold text-hb-navy">{{ $job['salary_label'] }}</p>
                        <p class="mt-1 text-xs text-hb-muted">{{ __('public.jobs.salary_annual') }}<br>{{ $job['location'] }} · {{ $job['work_model'] }} · {{ $job['employment'] }}</p>
                        <div class="mt-5 space-y-2">
                            <span class="hb-btn-primary flex w-full cursor-not-allowed justify-center opacity-70" title="{{ __('public.jobs.apply_title') }}">
                                {{ __('public.jobs.apply') }}
                            </span>
                            <x-button variant="secondary" class="w-full" type="button" disabled>{{ __('public.jobs.save_job') }}</x-button>
                        </div>
                        <p class="mt-3 text-xs text-hb-muted">
                            {{ __('public.jobs.apply_note') }}
                        </p>
                    </div>

                    <div class="hb-surface p-5 text-sm">
                        <h2 class="font-semibold text-hb-navy">{{ __('public.jobs.about_company', ['name' => $job['company']]) }}</h2>
                        <p class="mt-2 text-hb-muted">
                            {{ __('public.jobs.company_blurb') }}
                        </p>
                        @if (($job['company_slug'] ?? null) === 'northstar-labs')
                            <a href="{{ route('companies.show', $job['company_slug']) }}" class="mt-3 inline-block font-semibold text-hb-blue hover:underline">{{ __('public.jobs.view_company') }}</a>
                        @endif
                        <p class="mt-4 text-hb-muted">
                            {{ __('public.jobs.hiring_contact') }} {{ $job['hiring_contact'] }}<br>
                            {{ __('public.jobs.hiring_manager') }} {{ $job['hiring_manager'] }}
                        </p>
                    </div>

                    <div class="hb-surface p-5 text-sm">
                        <h2 class="font-semibold text-hb-navy">{{ __('public.jobs.role_details') }}</h2>
                        <dl class="mt-3 space-y-2 text-hb-muted">
                            <div><dt class="inline font-medium text-hb-text">{{ __('public.jobs.job_id') }}</dt> <dd class="inline">{{ $job['code'] }}</dd></div>
                            <div><dt class="inline font-medium text-hb-text">{{ __('public.jobs.posted_label') }}</dt> <dd class="inline">{{ $job['posted_at'] }}</dd></div>
                            <div><dt class="inline font-medium text-hb-text">{{ __('public.jobs.team_label') }}</dt> <dd class="inline">{{ $job['discipline'] }}</dd></div>
                            <div><dt class="inline font-medium text-hb-text">{{ __('public.jobs.reports_to') }}</dt> <dd class="inline">{{ $job['reports_to'] }}</dd></div>
                        </dl>
                        <a href="{{ route('jobs.index') }}" class="mt-4 inline-block font-semibold text-hb-blue hover:underline">{{ __('public.jobs.back_all') }}</a>
                    </div>
                </aside>
            </div>
        </div>
    </section>
@endsection

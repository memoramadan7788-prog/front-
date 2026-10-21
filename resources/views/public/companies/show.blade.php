@extends('layouts.public')

@section('title', $pageTitle ?? __('public.company.page_title'))

@section('content')
    {{-- Figma 05 · Northstar Labs (8:31557) --}}
    <section class="bg-hb-page" data-company-profile>
        <div class="hb-container py-10 lg:py-14">
            <p class="text-xs font-semibold tracking-[0.12em] text-hb-muted uppercase">
                {{ __('public.company.eyebrow', ['name' => $company['name']]) }}
            </p>

            <div data-tabs id="company-tabs">
                <header class="mt-4 hb-surface p-6 lg:p-8">
                    <div class="flex flex-wrap items-center gap-2">
                        <x-badge variant="info" :dot="true">{{ $company['industry'] }}</x-badge>                    </div>
                    <h1 class="mt-4 text-3xl font-semibold text-hb-navy sm:text-4xl">{{ $company['name'] }}</h1>
                    <p class="mt-2 text-lg text-hb-muted">{{ $company['tagline'] }}</p>
                    <p class="mt-3 text-sm text-hb-muted">{{ $company['location'] }} · {{ $company['website'] }}</p>

                    <div class="mt-6 flex flex-wrap gap-4 border-b border-hb-border text-sm font-semibold" role="tablist" aria-label="{{ __('public.company.tabs_aria') }}">
                        @foreach ([
                            ['overview', __('public.company.tab_overview')],
                            ['culture', __('public.company.tab_culture')],
                            ['roles', __('public.company.tab_roles', ['count' => count($openRoles)])],
                        ] as [$key, $label])
                            <button
                                type="button"
                                id="company-tabs-tab-{{ $key }}"
                                role="tab"
                                aria-controls="company-tabs-panel-{{ $key }}"
                                aria-selected="{{ $key === 'overview' ? 'true' : 'false' }}"
                                tabindex="{{ $key === 'overview' ? '0' : '-1' }}"
                                class="border-b-2 px-1 pb-3 {{ $key === 'overview' ? 'border-hb-blue text-hb-blue' : 'border-transparent text-hb-muted' }}"
                            >
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </header>

                <div class="mt-8 space-y-8">
                    <div id="company-tabs-panel-overview" role="tabpanel" aria-labelledby="company-tabs-tab-overview">
                        <article class="hb-surface p-6 lg:p-8">
                            <h2 class="text-xl font-semibold text-hb-navy">{{ $company['headline'] ?? __('public.company.overview_headline') }}</h2>
                            @foreach (preg_split("/\n\n/", $company['overview']) as $para)
                                <p class="mt-4 text-sm leading-relaxed text-hb-muted">{{ $para }}</p>
                            @endforeach

                            <h3 class="mt-8 font-semibold text-hb-navy">{{ __('public.company.how_we_work') }}</h3>
                            <div class="mt-4 grid gap-4 md:grid-cols-2">
                                @foreach ($company['how_we_work'] as $item)
                                    <div class="rounded-lg border border-hb-border bg-hb-page p-4">
                                        <p class="font-semibold text-hb-navy">{{ $item['title'] }}</p>
                                        <p class="mt-2 text-sm text-hb-muted">{{ $item['body'] }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </article>
                    </div>

                    <div id="company-tabs-panel-culture" role="tabpanel" aria-labelledby="company-tabs-tab-culture" class="hidden" hidden>
                        <article class="hb-surface p-6 lg:p-8">
                            <h2 class="text-xl font-semibold text-hb-navy">{{ __('public.company.culture_title') }}</h2>
                            <p class="mt-4 text-sm leading-relaxed text-hb-muted">{{ $company['benefits'] }}</p>

                            <h3 class="mt-8 font-semibold text-hb-navy">{{ __('public.company.hiring_team') }}</h3>
                            <ul class="mt-4 grid gap-4 sm:grid-cols-2">
                                @foreach ($company['team'] as $person)
                                    <li class="rounded-lg border border-hb-border p-4">
                                        <p class="font-semibold text-hb-navy">{{ $person['name'] }}</p>
                                        <p class="mt-1 text-sm text-hb-muted">{{ $person['role'] }}</p>
                                    </li>
                                @endforeach
                            </ul>
                            <p class="mt-4 text-sm text-hb-muted">{{ __('public.company.interview_contact') }}</p>

                            <h3 class="mt-8 font-semibold text-hb-navy">{{ __('public.company.locations') }}</h3>
                            <div class="mt-4 grid gap-4 md:grid-cols-2">
                                @foreach ($company['locations'] as $location)
                                    <div class="rounded-lg border border-hb-border p-4">
                                        <p class="font-semibold text-hb-navy">{{ $location['title'] }}</p>
                                        <p class="mt-2 whitespace-pre-line text-sm text-hb-muted">{{ $location['body'] }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </article>
                    </div>

                    <div id="company-tabs-panel-roles" role="tabpanel" aria-labelledby="company-tabs-tab-roles" class="hidden" hidden>
                        <div>
                            <h2 class="text-xl font-semibold text-hb-navy">{{ __('public.company.openings') }}</h2>
                            <p class="mt-1 text-xs text-hb-muted"></p>
                            <div class="mt-6 space-y-4">
                                @forelse ($openRoles as $job)
                                    @include('public.jobs._card', ['job' => $job])
                                @empty
                                    <div class="hb-surface p-8 text-center text-sm text-hb-muted">{{ __('public.company.empty_roles') }}</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

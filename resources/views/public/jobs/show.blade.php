@extends('layouts.public')

@section('title', $pageTitle ?? 'Job details · Hirebridge')

@section('content')
    {{-- Figma 03 · Job Details (8:31406) --}}
    <section class="bg-hb-page">
        <div class="hb-container py-10 lg:py-14">
            <p class="text-xs font-semibold tracking-[0.12em] text-hb-muted uppercase">
                03 / Hirebridge / {{ $job['title'] }}
            </p>

            <div class="mt-6 grid gap-8 lg:grid-cols-[minmax(0,1fr)_320px]">
                <div class="space-y-8">
                    <header class="hb-surface p-6">
                        <div class="flex flex-wrap items-center gap-2">
                            <x-badge variant="info" :dot="true">{{ $job['source'] }}</x-badge>
                            <x-badge variant="success" :dot="true">{{ $job['status'] }}</x-badge>
                            <span class="rounded-full bg-hb-pending-soft px-2 py-0.5 text-[10px] font-semibold text-hb-pending uppercase">Mock job</span>
                        </div>
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
                            <h2 class="text-base font-semibold text-hb-navy">What you’ll do</h2>
                            <ul class="mt-3 list-disc space-y-2 ps-5 text-hb-muted">
                                @foreach ($job['responsibilities'] as $item)
                                    <li>{{ $item }}</li>
                                @endforeach
                            </ul>
                        </div>

                        <div>
                            <h2 class="text-base font-semibold text-hb-navy">What you’ll bring</h2>
                            <ul class="mt-3 list-disc space-y-2 ps-5 text-hb-muted">
                                @foreach ($job['requirements'] as $item)
                                    <li>{{ $item }}</li>
                                @endforeach
                            </ul>
                        </div>

                        <div>
                            <h2 class="text-base font-semibold text-hb-navy">Benefits & ways of working</h2>
                            <p class="mt-3 text-hb-muted">{{ $job['benefits'] }}</p>
                        </div>

                        <div>
                            <h2 class="text-base font-semibold text-hb-navy">Our hiring process</h2>
                            <div class="mt-4">
                                <x-timeline :steps="[
                                    ['label' => 'Applied', 'status' => 'pending'],
                                    ['label' => 'Screening', 'status' => 'pending'],
                                    ['label' => 'Interview', 'status' => 'pending'],
                                    ['label' => 'Offer', 'status' => 'pending'],
                                    ['label' => 'Hired', 'status' => 'pending'],
                                ]" />
                            </div>
                            <p class="mt-4 text-hb-muted">{{ $job['process'] }}</p>
                            <p class="mt-3 text-hb-muted">
                                {{ $job['company'] }} welcomes applicants from different backgrounds. Request an accommodation through the hiring team.
                                Applications are reviewed against role-related criteria.
                            </p>
                        </div>
                    </article>
                </div>

                <aside class="space-y-4 lg:sticky lg:top-28 lg:self-start">
                    <div class="hb-surface p-5">
                        <h2 class="text-sm font-semibold text-hb-navy">Your next step</h2>
                        <p class="mt-3 text-2xl font-semibold text-hb-navy">{{ $job['salary_label'] }}</p>
                        <p class="mt-1 text-xs text-hb-muted">Annual base salary · USD<br>{{ $job['location'] }} · {{ $job['work_model'] }} · {{ $job['employment'] }}</p>
                        <div class="mt-5 space-y-2">
                            <span class="hb-btn-primary flex w-full cursor-not-allowed justify-center opacity-70" title="Applications require authentication — later stage">
                                Apply for this role
                            </span>
                            <x-button variant="secondary" class="w-full" type="button" disabled>Save job</x-button>
                        </div>
                        <p class="mt-3 text-xs text-hb-muted">
                            You’ll review your materials and consent before submitting. No application is sent automatically.
                        </p>
                    </div>

                    <div class="hb-surface p-5 text-sm">
                        <h2 class="font-semibold text-hb-navy">About {{ $job['company'] }}</h2>
                        <p class="mt-2 text-hb-muted">
                            Collaborative software for operational teams. Based in Austin, with teammates across the United States.
                        </p>
                        @if (($job['company_slug'] ?? null) === 'northstar-labs')
                            <a href="{{ route('companies.show', $job['company_slug']) }}" class="mt-3 inline-block font-semibold text-hb-blue hover:underline">View company profile →</a>
                        @endif
                        <p class="mt-4 text-hb-muted">
                            Hiring contact: {{ $job['hiring_contact'] }}<br>
                            Hiring manager: {{ $job['hiring_manager'] }}
                        </p>
                    </div>

                    <div class="hb-surface p-5 text-sm">
                        <h2 class="font-semibold text-hb-navy">Role details</h2>
                        <dl class="mt-3 space-y-2 text-hb-muted">
                            <div><dt class="inline font-medium text-hb-text">Job ID:</dt> <dd class="inline">{{ $job['code'] }}</dd></div>
                            <div><dt class="inline font-medium text-hb-text">Posted:</dt> <dd class="inline">{{ $job['posted_at'] }}</dd></div>
                            <div><dt class="inline font-medium text-hb-text">Team:</dt> <dd class="inline">{{ $job['discipline'] }}</dd></div>
                            <div><dt class="inline font-medium text-hb-text">Reports to:</dt> <dd class="inline">{{ $job['reports_to'] }}</dd></div>
                        </dl>
                        <a href="{{ route('jobs.index') }}" class="mt-4 inline-block font-semibold text-hb-blue hover:underline">← Back to all jobs</a>
                    </div>
                </aside>
            </div>
        </div>
    </section>
@endsection

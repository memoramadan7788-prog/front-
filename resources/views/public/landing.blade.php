@extends('layouts.public')

@section('title', $pageTitle ?? 'Hirebridge')

@section('content')
    {{-- Figma 01 · Public · Home (8:31126) --}}

    {{-- Search hero --}}
    <section class="bg-hb-surface">
        <div class="hb-container grid items-center gap-10 py-12 lg:grid-cols-2 lg:gap-16 lg:py-16">
            <div class="space-y-6">
                <div>
                    <p class="text-xs font-bold tracking-[0.08em] text-hb-blue uppercase">01 / Your next chapter starts here</p>
                    <h1 class="mt-4 text-4xl font-semibold leading-tight text-hb-navy sm:text-5xl lg:text-[56px] lg:leading-[1.15]">
                        The right people.<br>The right next step.
                    </h1>
                    <p class="mt-4 max-w-xl text-base leading-relaxed text-hb-muted sm:text-lg">
                        Discover meaningful work. Build a stronger team. Connect directly with companies
                        or get dedicated support from our recruitment specialists.
                    </p>
                </div>

                <form action="{{ route('jobs.index') }}" method="get" class="flex flex-col gap-2 rounded-lg border border-hb-border p-2 sm:flex-row sm:items-end">
                    <label class="min-w-0 flex-1 px-2 py-1">
                        <span class="block text-[11px] font-semibold tracking-wide text-hb-muted uppercase">Role or keyword</span>
                        <input type="search" name="q" value="Product designer" class="mt-1 w-full border-0 bg-transparent p-0 text-sm text-hb-text focus:ring-0" placeholder="Product designer">
                    </label>
                    <label class="min-w-0 flex-1 border-t border-hb-border px-2 py-1 sm:border-t-0 sm:border-s">
                        <span class="block text-[11px] font-semibold tracking-wide text-hb-muted uppercase">Location</span>
                        <input type="text" name="location" value="Austin, TX" class="mt-1 w-full border-0 bg-transparent p-0 text-sm text-hb-text focus:ring-0" placeholder="Austin, TX">
                    </label>
                    <x-button type="submit" variant="primary" class="shrink-0">Find jobs</x-button>
                </form>

                <p class="text-xs text-hb-muted">Popular: Product Design · Engineering · Customer Success</p>

                <div class="flex flex-wrap items-center gap-4">
                    <x-button variant="secondary" :href="route('about')">I’m hiring</x-button>
                    <p class="text-sm text-hb-muted">Find the hiring model that fits your team.</p>
                </div>
            </div>

            <figure class="overflow-hidden rounded-lg">
                <img
                    src="{{ asset('images/public/collaborative-workplace.jpg') }}"
                    alt="Collaborative workplace with a diverse team reviewing plans together"
                    class="aspect-[512/392] w-full object-cover"
                    width="512"
                    height="392"
                >
                <figcaption class="bg-hb-navy px-6 py-6 text-base font-medium text-white">
                    Work is better when the fit is right.
                </figcaption>
            </figure>
        </div>
    </section>

    {{-- Employer trust --}}
    <section id="companies" class="border-t border-hb-border bg-hb-page">
        <div class="hb-container py-10">
            <p class="text-center text-xs font-semibold tracking-[0.12em] text-hb-muted uppercase">
                Explore teams hiring on Hirebridge · Demo organizations
            </p>
            <div class="mt-6 flex flex-wrap items-center justify-center gap-x-10 gap-y-4 text-sm font-semibold tracking-wide text-hb-navy/70 uppercase">
                @foreach ($demoCompanies as $company)
                    <span>{{ $company }}</span>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Featured opportunities --}}
    <section id="jobs" class="border-t border-hb-border bg-hb-surface">
        <div class="hb-container py-16">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="text-2xl font-semibold text-hb-navy sm:text-3xl">A good place to start.</h2>
                    <p class="mt-2 text-sm text-hb-muted">Clear role expectations. Transparent salaries. Real next steps.</p>
                </div>
                <x-button variant="secondary" :href="route('jobs.index')">Explore all jobs</x-button>
            </div>

            <div class="mt-10 grid gap-4 lg:grid-cols-3">
                @foreach ($featuredJobs as $job)
                    @include('public.jobs._card', ['job' => $job])
                @endforeach
            </div>
            <p class="mt-4 text-xs text-hb-muted">Demo job cards · not live listings</p>
        </div>
    </section>

    {{-- Hiring models --}}
    <section id="employers" class="border-t border-hb-border bg-hb-page">
        <div class="hb-container py-16">
            <h2 class="text-2xl font-semibold text-hb-navy sm:text-3xl">One platform. The support you need.</h2>
            <div class="mt-10 grid gap-6 lg:grid-cols-3">
                <article class="hb-surface p-6">
                    <p class="text-xs font-semibold text-hb-blue">● Your next opportunity</p>
                    <h3 class="mt-2 text-lg font-semibold text-hb-navy">For candidates</h3>
                    <p class="mt-3 text-sm leading-relaxed text-hb-muted">
                        Find roles that fit your skills, salary expectations, and way of working. Track every application in one place.
                    </p>
                    <ul class="mt-4 space-y-2 text-sm text-hb-text">
                        <li>✓ Transparent roles and hiring stages</li>
                        <li>✓ Interview preparation and direct messages</li>
                        <li>✓ Private profile and alert controls</li>
                    </ul>
                    <x-button class="mt-6" variant="primary" :href="route('jobs.index')">Create your profile</x-button>
                </article>
                <article class="hb-surface p-6">
                    <p class="text-xs font-semibold text-hb-blue">● You lead the process</p>
                    <h3 class="mt-2 text-lg font-semibold text-hb-navy">Direct company hiring</h3>
                    <p class="mt-3 text-sm leading-relaxed text-hb-muted">
                        Build your pipeline, collaborate with your team, and make thoughtful hiring decisions in your own workspace.
                    </p>
                    <ul class="mt-4 space-y-2 text-sm text-hb-text">
                        <li>✓ Publish and manage requisitions</li>
                        <li>✓ Schedule interviews and collect scorecards</li>
                        <li>✓ Maintain ownership of candidate decisions</li>
                    </ul>
                    <x-button class="mt-6" variant="secondary" :href="route('about')">Explore direct hiring</x-button>
                </article>
                <article class="hb-surface p-6">
                    <p class="text-xs font-semibold text-hb-blue">● A specialist by your side</p>
                    <h3 class="mt-2 text-lg font-semibold text-hb-navy">Recruitment agency services</h3>
                    <p class="mt-3 text-sm leading-relaxed text-hb-muted">
                        Partner with a dedicated recruiter for targeted search, candidate qualification, and coordinated delivery.
                    </p>
                    <ul class="mt-4 space-y-2 text-sm text-hb-text">
                        <li>✓ Role scoping and market calibration</li>
                        <li>✓ Curated shortlists with clear context</li>
                        <li>✓ Agreed engagement terms before search</li>
                    </ul>
                    <x-button class="mt-6" variant="secondary" :href="route('about') . '#engagements'">Agency services</x-button>
                </article>
            </div>
        </div>
    </section>

    {{-- How it works --}}
    <section class="border-t border-hb-border bg-hb-surface">
        <div class="hb-container py-16">
            <h2 class="text-2xl font-semibold text-hb-navy">Less uncertainty. More momentum.</h2>
            <div class="mt-10 grid gap-8 md:grid-cols-3">
                @foreach ([
                    ['01', 'Start with what matters', 'Share your goals, skills, or hiring brief. Choose direct hiring or agency support.'],
                    ['02', 'Make the right connections', 'Discover relevant opportunities or review qualified candidates with your team.'],
                    ['03', 'Move forward together', 'Keep interviews, feedback, and next steps organized from first conversation to offer.'],
                ] as [$num, $title, $body])
                    <div>
                        <p class="text-sm font-semibold text-hb-blue">{{ $num }}</p>
                        <h3 class="mt-2 text-lg font-semibold text-hb-navy">{{ $title }}</h3>
                        <p class="mt-2 text-sm text-hb-muted">{{ $body }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Disciplines --}}
    <section class="border-t border-hb-border bg-hb-page">
        <div class="hb-container py-16">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <h2 class="text-2xl font-semibold text-hb-navy">Specialists across your team.</h2>
                <a href="{{ route('jobs.index') }}" class="text-sm font-semibold text-hb-blue hover:underline">Browse disciplines →</a>
            </div>
            <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['Product & Design', 'Product design, research, product management'],
                    ['Engineering & Data', 'Software engineering, analytics, infrastructure'],
                    ['Marketing & Growth', 'Brand, lifecycle, demand generation'],
                    ['People & Operations', 'Talent, customer success, business operations'],
                ] as [$title, $body])
                    <a href="{{ route('jobs.index', ['discipline' => $title]) }}" class="hb-surface block p-5 transition hover:border-hb-blue/40">
                        <h3 class="font-semibold text-hb-navy">{{ $title }}</h3>
                        <p class="mt-2 text-sm text-hb-muted">{{ $body }}</p>
                        <p class="mt-4 text-sm font-semibold text-hb-blue">Explore roles →</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Employer value --}}
    <section class="border-t border-hb-border bg-hb-surface">
        <div class="hb-container grid items-center gap-10 py-16 lg:grid-cols-2">
            <x-card>
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="font-semibold text-hb-navy">Senior Product Designer</h3>
                        <p class="mt-1 text-sm text-hb-muted">Northstar Labs · NS-204 · Direct + agency sourcing</p>
                    </div>
                    <x-badge variant="success" :dot="true">Open</x-badge>
                </div>
                <div class="mt-6 grid grid-cols-5 gap-2 text-center text-xs">
                    @foreach ([['Applied','3'],['Screening','3'],['Interview','3'],['Offer','2'],['Hired','1']] as [$label,$n])
                        <div class="rounded-lg bg-hb-page px-1 py-3">
                            <p class="font-semibold text-hb-navy">{{ $n }}</p>
                            <p class="mt-1 text-hb-muted">{{ $label }}</p>
                        </div>
                    @endforeach
                </div>
                <p class="mt-6 text-sm text-hb-text">
                    Next: Maya Chen · Portfolio interview<br>
                    <span class="text-hb-muted">Oct 22 · 10:00 AM Central · Alex Morgan</span>
                </p>
            </x-card>
            <div>
                <h2 class="text-2xl font-semibold text-hb-navy">A shared view of every next step.</h2>
                <p class="mt-3 text-sm leading-relaxed text-hb-muted">
                    Keep recruiters, hiring managers, and interviewers aligned without losing the human context.
                    Candidates see clear updates, not a black box.
                </p>
                <p class="mt-4 text-sm font-medium text-hb-text">Structured pipelines · Shared interview plans · Clear ownership</p>
                <x-button class="mt-6" variant="primary" :href="route('about')">See how hiring works</x-button>
            </div>
        </div>
    </section>

    {{-- Testimonial --}}
    <section class="border-t border-hb-border bg-hb-page">
        <div class="hb-container max-w-3xl py-16 text-center">
            <p class="text-xs font-semibold tracking-[0.12em] text-hb-muted uppercase">Conceptual testimonial · Demo content</p>
            <blockquote class="mt-6 text-xl font-medium leading-relaxed text-hb-navy sm:text-2xl">
                “Knowing who I’d meet, what to prepare, and where I stood made the process feel thoughtful from the start.”
            </blockquote>
            <p class="mt-4 text-sm text-hb-muted">Maya Chen · Senior Product Designer · Candidate journey example</p>
        </div>
    </section>

    {{-- Commitment --}}
    <section class="border-t border-hb-border bg-hb-surface">
        <div class="hb-container py-16">
            <h2 class="text-2xl font-semibold text-hb-navy">A clearer process, by design.</h2>
            <div class="mt-10 grid gap-8 md:grid-cols-3">
                @foreach ([
                    ['Transparency first', 'Salary ranges, work models, and hiring stages are visible before you apply.'],
                    ['People stay in control', 'Choose profile visibility and how recruiters can contact you.'],
                    ['Support when it matters', 'Get a clear point of contact and practical guidance for the next step.'],
                ] as [$title, $body])
                    <div>
                        <h3 class="font-semibold text-hb-navy">{{ $title }}</h3>
                        <p class="mt-2 text-sm text-hb-muted">{{ $body }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Final CTA --}}
    <section class="border-t border-hb-border bg-hb-navy">
        <div class="hb-container flex flex-col items-start justify-between gap-6 py-16 lg:flex-row lg:items-center">
            <div>
                <h2 class="text-2xl font-semibold text-white sm:text-3xl">What’s your next chapter?</h2>
                <p class="mt-2 text-sm text-hb-nav-muted">Find meaningful work, or the people who help your business grow.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <x-button variant="primary" :href="route('jobs.index')">Find your next role</x-button>
                <a href="{{ route('contact') }}" class="inline-flex items-center justify-center rounded-lg border border-white/30 px-4 py-2.5 text-sm font-semibold text-white hover:bg-white/10">
                    Build your team
                </a>
            </div>
        </div>
    </section>
@endsection

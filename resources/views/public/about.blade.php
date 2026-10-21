@extends('layouts.public')

@section('title', $pageTitle ?? 'Specialist recruitment · Hirebridge')

@section('content')
    {{-- Figma 04 · Specialist recruitment (8:31475) --}}
    <section class="bg-hb-page">
        <div class="hb-container py-10 lg:py-14">
            <p class="text-xs font-semibold tracking-[0.12em] text-hb-muted uppercase">
                04 / Hirebridge / Specialist recruitment, with a shared plan.
            </p>
            <div class="mt-4 max-w-3xl">
                <h1 class="text-3xl font-semibold text-hb-navy sm:text-4xl">Specialist recruitment, with a shared plan.</h1>
                <p class="mt-3 text-lg text-hb-muted">Focused search and thoughtful candidate support, built around your hiring needs.</p>
                <p class="mt-4 text-sm leading-relaxed text-hb-muted">
                    From a hard-to-fill specialist role to a coordinated hiring program, Hirebridge brings a dedicated
                    recruitment partner into your team’s workflow.
                </p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <x-button variant="primary" :href="route('contact')">Discuss your hiring needs</x-button>
                    <x-button variant="secondary" :href="route('about') . '#engagements'">Explore direct hiring</x-button>
                </div>
            </div>

            <div class="mt-12 hb-surface p-6 lg:p-8">
                <h2 class="text-xl font-semibold text-hb-navy">Your team stays in control</h2>
                <p class="mt-3 max-w-3xl text-sm text-hb-muted">
                    Agree the scope, service, and terms before search begins. Your company owns interview decisions;
                    your recruiter owns search delivery and coordination.
                </p>
                <p class="mt-4 text-sm font-medium text-hb-blue">● Clear ownership · Shared workspace</p>
            </div>

            <div id="engagements" class="mt-12">
                <h2 class="text-2xl font-semibold text-hb-navy">Choose the right engagement</h2>
                <div class="mt-6 grid gap-4 lg:grid-cols-3">
                    @foreach ([
                        ['Contingent search', 'For a defined permanent role', 'Targeted sourcing, candidate qualification, and curated submissions. Fees and replacement terms agreed in writing.'],
                        ['Retained search', 'For specialist and leadership needs', 'Dedicated research, calibrated market mapping, and regular search reviews against an agreed brief.'],
                        ['Embedded recruitment', 'For sustained hiring capacity', 'A recruiter works alongside your team with a shared delivery plan, reporting cadence, and agreed service scope.'],
                    ] as [$title, $eyebrow, $body])
                        <article class="hb-surface p-6">
                            <h3 class="text-lg font-semibold text-hb-navy">{{ $title }}</h3>
                            <p class="mt-1 text-sm font-medium text-hb-muted">{{ $eyebrow }}</p>
                            <p class="mt-3 text-sm text-hb-muted">{{ $body }}</p>
                            <a href="{{ route('contact') }}" class="mt-4 inline-block text-sm font-semibold text-hb-blue hover:underline">Discuss this model →</a>
                        </article>
                    @endforeach
                </div>
            </div>

            <div class="mt-14">
                <h2 class="text-2xl font-semibold text-hb-navy">A transparent process, from brief to start date</h2>
                <ol class="mt-6 space-y-4">
                    @foreach ([
                        ['01 · Discover', 'What we do: Clarify role outcomes, salary, location, and search scope', 'What you own: Approve the hiring brief and engagement terms'],
                        ['02 · Calibrate', 'What we do: Share market context and refine target profiles', 'What you own: Align on requirements and interview criteria'],
                        ['03 · Search & qualify', 'What we do: Source, screen, and share evidence-based shortlists', 'What you own: Review submissions and select interviewees'],
                        ['04 · Coordinate', 'What we do: Manage candidate communication and scheduling', 'What you own: Interview, evaluate, and make decisions'],
                        ['05 · Close & support', 'What we do: Support offer communication and start-date coordination', 'What you own: Approve terms and complete onboarding'],
                    ] as [$title, $we, $you])
                        <li class="hb-surface p-5">
                            <p class="font-semibold text-hb-navy">{{ $title }}</p>
                            <p class="mt-2 text-sm text-hb-muted">{{ $we }}</p>
                            <p class="mt-1 text-sm text-hb-muted">{{ $you }}</p>
                        </li>
                    @endforeach
                </ol>
            </div>

            <div class="mt-14 grid gap-8 lg:grid-cols-2">
                <div>
                    <h2 class="text-xl font-semibold text-hb-navy">Where we specialize</h2>
                    <ul class="mt-4 space-y-2 text-sm text-hb-text">
                        <li>Product & Design</li>
                        <li>Engineering & Data</li>
                        <li>Marketing & Growth</li>
                        <li>People & Operations</li>
                    </ul>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-hb-navy">What every submission includes</h2>
                    <p class="mt-3 text-sm text-hb-muted">
                        Role-relevant experience and motivation, agreed salary expectations, location and availability,
                        source details, and the candidate’s consent to be introduced.
                    </p>
                    <h3 class="mt-6 font-semibold text-hb-navy">Shared visibility</h3>
                    <p class="mt-2 text-sm text-hb-muted">
                        See engagement ownership, submission progress, interview status, and next actions alongside your
                        direct-hire pipeline. Sources remain clearly labeled.
                    </p>
                </div>
            </div>

            <div class="mt-12 hb-surface p-6">
                <h2 class="text-lg font-semibold text-hb-navy">No one-size-fits-all fees</h2>
                <p class="mt-2 text-sm text-hb-muted">
                    Pricing depends on the engagement model and scope. We’ll explain fees, payment milestones,
                    confidentiality, and replacement provisions in your proposal. No commitment is made by requesting a consultation.
                </p>
            </div>

            <div class="mt-12 rounded-hb bg-hb-navy p-8 text-white lg:flex lg:items-center lg:justify-between lg:gap-8">
                <div>
                    <h2 class="text-2xl font-semibold">Start with a practical conversation.</h2>
                    <p class="mt-2 text-sm text-hb-nav-muted">Tell us what you’re hiring for. We’ll recommend a useful next step.</p>
                </div>
                <x-button class="mt-6 lg:mt-0" variant="primary" :href="route('contact')">Request a consultation</x-button>
            </div>
        </div>
    </section>
@endsection

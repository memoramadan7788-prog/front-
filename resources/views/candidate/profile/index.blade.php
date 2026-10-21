@extends('layouts.app')

@section('title', $pageTitle ?? 'Profile & resume · Hirebridge')
@section('breadcrumb', '22 / My workspace / Profile & resume')

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">Profile & resume</h1>
    <p class="mt-1 text-sm text-hb-muted">
        Your experience, in your own words. Keep it current and control who sees it.
    </p>
@endsection

@section('page_actions')
    <button type="button" class="hb-btn-primary" disabled title="Profile preview — Backend Team">
        Preview profile
    </button>
@endsection

@section('content')
    <div class="hb-mock-banner mb-6 rounded-lg text-start">
        Frontend UI only — profile and resume content is mock presentation data (Figma 22). Nothing is saved.
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_300px]">
        <div class="space-y-6">
            {{-- Profile basics --}}
            <section class="hb-surface p-5 sm:p-6" aria-labelledby="profile-basics-heading">
                <div class="mb-5">
                    <h2 id="profile-basics-heading" class="text-lg font-semibold text-hb-navy">{{ $profile['name'] }}</h2>
                    <p class="mt-1 text-sm text-hb-muted">
                        {{ $profile['headline'] }} · {{ $profile['location_short'] }}
                    </p>
                </div>

                <form class="space-y-4" data-profile-form="basics" novalidate aria-label="Profile basics">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="profile-headline" class="hb-label">Headline</label>
                            <input
                                id="profile-headline"
                                name="headline"
                                type="text"
                                class="hb-input"
                                value="{{ $profile['headline'] }}"
                                autocomplete="organization-title"
                            >
                        </div>
                        <div>
                            <label for="profile-location" class="hb-label">Location</label>
                            <input
                                id="profile-location"
                                name="location"
                                type="text"
                                class="hb-input"
                                value="{{ $profile['location'] }}"
                                autocomplete="address-level2"
                            >
                        </div>
                    </div>

                    <div>
                        <label for="profile-about" class="hb-label">About you</label>
                        <textarea
                            id="profile-about"
                            name="about"
                            class="hb-input min-h-28"
                            rows="4"
                        >{{ $profile['summary'] }}</textarea>
                    </div>

                    <div>
                        <label for="profile-portfolio" class="hb-label">Portfolio</label>
                        <input
                            id="profile-portfolio"
                            name="portfolio"
                            type="url"
                            class="hb-input"
                            value="{{ $profile['portfolio'] }}"
                            autocomplete="url"
                        >
                    </div>

                    <div class="pt-1">
                        <button type="button" class="hb-btn-primary" data-profile-save="basics">
                            Save profile
                        </button>
                    </div>

                    <div
                        class="hidden rounded-lg border border-hb-info-soft bg-hb-info-soft px-3 py-2.5 text-sm text-hb-navy"
                        data-profile-frontend-note="basics"
                        role="status"
                    >
                        Profile form validated on the client. No profile was saved — Backend Team persistence is required.
                    </div>
                </form>
            </section>

            {{-- Experience --}}
            <section class="hb-surface p-5 sm:p-6" aria-labelledby="experience-heading">
                <h2 id="experience-heading" class="text-lg font-semibold text-hb-navy">Experience</h2>

                <ul class="mt-4 space-y-5">
                    @foreach ($profile['experience'] as $job)
                        <li @class(['border-b border-hb-border pb-5' => ! $loop->last])>
                            <p class="font-semibold text-hb-navy">
                                {{ $job['title'] }} · {{ $job['company'] }}
                            </p>
                            <p class="mt-1 text-sm text-hb-muted">
                                {{ $job['dates'] }} · {{ $job['location'] }}
                            </p>
                            <p class="mt-3 text-sm leading-relaxed text-hb-text">{{ $job['summary'] }}</p>
                        </li>
                    @endforeach
                </ul>

                <div class="mt-5">
                    <button type="button" class="hb-btn-outline" disabled title="Add experience — Backend Team">
                        Add experience
                    </button>
                </div>
            </section>

            {{-- Education & skills --}}
            <section class="hb-surface p-5 sm:p-6" aria-labelledby="education-skills-heading">
                <h2 id="education-skills-heading" class="text-lg font-semibold text-hb-navy">Education &amp; skills</h2>

                <ul class="mt-4 space-y-3">
                    @foreach ($profile['education'] as $edu)
                        <li>
                            <p class="font-semibold text-hb-navy">
                                {{ $edu['degree'] }} · {{ $edu['school'] }}
                            </p>
                            <p class="mt-1 text-sm text-hb-muted">{{ $edu['dates'] }}</p>
                        </li>
                    @endforeach
                </ul>

                <div class="mt-5">
                    <p class="text-sm font-medium text-hb-text">Skills</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($profile['skills'] as $skill)
                            @php
                                $dotClass = match ($skill['tone'] ?? 'blue') {
                                    'teal' => 'bg-hb-teal',
                                    'pending' => 'bg-hb-pending',
                                    'success' => 'bg-hb-success',
                                    default => 'bg-hb-blue',
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1.5 rounded-full border border-hb-border bg-hb-page px-3 py-1 text-sm text-hb-text">
                                <span class="h-1.5 w-1.5 rounded-full {{ $dotClass }}" aria-hidden="true"></span>
                                {{ $skill['label'] }}
                            </span>
                        @endforeach
                    </div>
                </div>

                <div class="mt-5">
                    <button type="button" class="hb-btn-outline" disabled title="Edit education & skills — Backend Team">
                        Edit education &amp; skills
                    </button>
                </div>
            </section>
        </div>

        <aside class="space-y-4" aria-label="Resume and visibility">
            {{-- Resume --}}
            <section class="hb-surface p-5" aria-labelledby="resume-heading">
                <div class="flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-hb-success" aria-hidden="true"></span>
                    <h2 id="resume-heading" class="text-sm font-semibold text-hb-navy">Current resume</h2>
                </div>
                <p class="mt-3 font-medium text-hb-navy">{{ $profile['resume']['filename'] }}</p>
                <p class="mt-1 text-xs text-hb-muted">
                    {{ $profile['resume']['size'] }} · Updated {{ $profile['resume']['updated'] }}
                </p>
                <div class="mt-4 flex flex-col gap-2">
                    <button type="button" class="hb-btn-outline w-full" disabled title="Replace resume — Backend Team">
                        Replace resume
                    </button>
                    <button type="button" class="text-sm font-semibold text-hb-blue hover:underline disabled:cursor-not-allowed disabled:no-underline disabled:opacity-60" disabled title="Download PDF — Backend Team">
                        Download PDF
                    </button>
                </div>
            </section>

            {{-- Visibility & consent --}}
            <section class="hb-surface p-5" aria-labelledby="visibility-heading">
                <h2 id="visibility-heading" class="text-base font-semibold text-hb-navy">Visibility &amp; consent</h2>

                <form class="mt-4 space-y-3" data-profile-form="visibility" novalidate aria-label="Visibility and consent">
                    @foreach ($profile['visibility'] as $option)
                        <label class="flex items-start gap-2.5 text-sm text-hb-text">
                            <input
                                type="checkbox"
                                name="{{ $option['name'] }}"
                                class="hb-checkbox mt-0.5"
                                value="1"
                                @checked($option['checked'])
                            >
                            <span>{{ $option['label'] }}</span>
                        </label>
                    @endforeach

                    <p class="pt-1 text-xs leading-relaxed text-hb-muted">{{ $profile['visibility_note'] }}</p>

                    <button type="button" class="hb-btn-outline mt-2 w-full" data-profile-save="visibility">
                        Save visibility
                    </button>

                    <div
                        class="hidden rounded-lg border border-hb-info-soft bg-hb-info-soft px-3 py-2.5 text-sm text-hb-navy"
                        data-profile-frontend-note="visibility"
                        role="status"
                    >
                        Visibility choices were not saved — Backend Team persistence is required.
                    </div>
                </form>
            </section>
        </aside>
    </div>

    <p class="mt-8 text-xs text-hb-muted">{{ $profile['footer'] }}</p>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-profile-save]').forEach((button) => {
            button.addEventListener('click', () => {
                const key = button.getAttribute('data-profile-save');
                const note = document.querySelector(`[data-profile-frontend-note="${key}"]`);
                if (note) {
                    note.classList.remove('hidden');
                }
            });
        });
    </script>
@endpush

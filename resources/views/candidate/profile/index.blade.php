@extends('layouts.app')

@section('title', $pageTitle ?? __('candidate.profile.page_title'))
@section('breadcrumb', __('candidate.workspace') . ' / ' . __('navigation.candidate.profile'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('candidate.profile.title') }}</h1>
    <p class="mt-1 text-sm text-hb-muted">
        {{ __('candidate.profile.intro') }}
    </p>
@endsection

@section('page_actions')
    <button type="button" class="hb-btn-primary" data-open-modal="candidate-profile-preview" data-profile-preview>
        {{ __('candidate.profile.preview_profile') }}
    </button>
@endsection

@section('content')
    <div class="sr-only" aria-live="polite" data-profile-live></div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_300px]" data-candidate-profile>
        <div class="space-y-6">
            {{-- Profile basics --}}
            <section class="hb-surface p-5 sm:p-6" aria-labelledby="profile-basics-heading">
                <div class="mb-5">
                    <h2 id="profile-basics-heading" class="text-lg font-semibold text-hb-navy" data-profile-display-name>{{ $profile['name'] }}</h2>
                    <p class="mt-1 text-sm text-hb-muted" data-profile-display-headline>
                        {{ $profile['headline'] }} · {{ $profile['location_short'] }}
                    </p>
                </div>

                <form class="space-y-4" data-profile-form="basics" novalidate aria-label="{{ __('candidate.profile.profile_basics_form_aria') }}">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="profile-headline" class="hb-label">{{ __('candidate.profile.headline') }}</label>
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
                            <label for="profile-location" class="hb-label">{{ __('candidate.profile.location') }}</label>
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
                        <label for="profile-about" class="hb-label">{{ __('candidate.profile.about_you') }}</label>
                        <textarea
                            id="profile-about"
                            name="about"
                            class="hb-input min-h-28"
                            rows="4"
                        >{{ $profile['summary'] }}</textarea>
                    </div>

                    <div>
                        <label for="profile-portfolio" class="hb-label">{{ __('candidate.profile.portfolio') }}</label>
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
                            {{ __('candidate.profile.save_profile') }}
                        </button>
                    </div>

                    <div
                        class="hidden rounded-lg border border-hb-info-soft bg-hb-info-soft px-3 py-2.5 text-sm text-hb-navy"
                        data-profile-frontend-note="basics"
                        role="status"
                    >
                        {{ __('candidate.profile.saved_device') }}
                    </div>
                </form>
            </section>

            {{-- {{ __('candidate.profile.experience') }} --}}
            <section class="hb-surface p-5 sm:p-6" aria-labelledby="experience-heading">
                <h2 id="experience-heading" class="text-lg font-semibold text-hb-navy">{{ __('candidate.profile.experience') }}</h2>

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
                    <button type="button" class="hb-btn-outline" disabled title="{{ __('candidate.profile.add_experience_soon') }}">
                        {{ __('candidate.profile.add_experience') }}
                    </button>
                </div>
            </section>

            {{-- Education & skills --}}
            <section class="hb-surface p-5 sm:p-6" aria-labelledby="education-skills-heading">
                <h2 id="education-skills-heading" class="text-lg font-semibold text-hb-navy">{{ __('candidate.profile.education_skills') }}</h2>

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
                    <p class="text-sm font-medium text-hb-text">{{ __('candidate.profile.skills') }}</p>
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
                    <button type="button" class="hb-btn-outline" disabled title="{{ __('candidate.profile.edit_education_soon') }}">
                        {{ __('candidate.profile.edit_education_skills') }}
                    </button>
                </div>
            </section>
        </div>

        <aside class="space-y-4" aria-label="{{ __('candidate.profile.resume_visibility_aside') }}">
            {{-- Resume --}}
            <section class="hb-surface p-5" aria-labelledby="resume-heading">
                <div class="flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-hb-success" aria-hidden="true"></span>
                    <h2 id="resume-heading" class="text-sm font-semibold text-hb-navy">{{ __('candidate.profile.current_resume') }}</h2>
                </div>
                <p class="mt-3 font-medium text-hb-navy">{{ $profile['resume']['filename'] }}</p>
                <p class="mt-1 text-xs text-hb-muted">
                    {{ $profile['resume']['size'] }} · {{ __('candidate.profile.updated', ['date' => $profile['resume']['updated']]) }}
                </p>
                <div class="mt-4 flex flex-col gap-2">
                    <button type="button" class="hb-btn-outline w-full" disabled title="{{ __('candidate.profile.replace_resume_soon') }}">
                        {{ __('candidate.profile.replace_resume') }}
                    </button>
                    <button type="button" class="text-sm font-semibold text-hb-blue hover:underline disabled:cursor-not-allowed disabled:no-underline disabled:opacity-60" disabled title="{{ __('candidate.profile.download_pdf_soon') }}">
                        {{ __('candidate.profile.download_pdf') }}
                    </button>
                </div>
            </section>

            {{-- Optional company membership (Workflow 2 / F9A) --}}
            @php
                $membership = $membership ?? [];
                $membershipRequest = $membership['request'] ?? null;
                $membershipStatus = $membershipRequest['status'] ?? null;
                $membershipStatusLabel = match ($membershipStatus) {
                    'pending' => __('candidate.membership.status_pending'),
                    'approved' => __('candidate.membership.status_approved'),
                    'rejected' => __('candidate.membership.status_rejected'),
                    default => __('candidate.membership.profile_status_none'),
                };
            @endphp
            <section class="hb-surface p-5" aria-labelledby="membership-aside-heading" data-profile-membership-card>
                <h2 id="membership-aside-heading" class="text-base font-semibold text-hb-navy">{{ __('candidate.membership.profile_card_title') }}</h2>
                <p class="mt-2 text-sm text-hb-muted">{{ __('candidate.membership.profile_card_body') }}</p>
                <p class="mt-3 text-sm font-medium text-hb-navy">{{ $membershipStatusLabel }}</p>
                @if (! empty($membershipRequest['company_name']))
                    <p class="mt-1 text-sm text-hb-muted">{{ $membershipRequest['company_name'] }} · {{ $membershipRequest['job_title'] ?? '' }}</p>
                @endif
                <a href="{{ route('candidate.membership') }}" class="hb-btn-outline mt-4 inline-flex w-full justify-center">
                    {{ __('candidate.membership.profile_card_cta') }}
                </a>
            </section>

            {{-- Visibility & consent --}}
            <section class="hb-surface p-5" aria-labelledby="visibility-heading">
                <h2 id="visibility-heading" class="text-base font-semibold text-hb-navy">{{ __('candidate.profile.visibility_consent') }}</h2>

                <form class="mt-4 space-y-3" data-profile-form="visibility" novalidate aria-label="{{ __('candidate.profile.visibility_form_aria') }}">
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
                        {{ __('candidate.profile.save_visibility') }}
                    </button>

                    <div
                        class="hidden rounded-lg border border-hb-info-soft bg-hb-info-soft px-3 py-2.5 text-sm text-hb-navy"
                        data-profile-frontend-note="visibility"
                        role="status"
                    >
                        {{ __('candidate.profile.visibility_saved') }}
                    </div>
                </form>
            </section>
        </aside>
    </div>

    <p class="mt-8 text-xs text-hb-muted">{{ $profile['footer'] }}</p>

    @include('layouts.modal', [
        'modalId' => 'candidate-profile-preview',
        'modalTitle' => __('candidate.profile.preview_profile'),
        'modalBody' => view('candidate.profile._preview-modal-body', ['profile' => $profile])->render(),
        'modalFooter' => '<button type="button" class="hb-btn-outline" data-hb-modal-close>'.e(__('common.actions.close')).'</button>',
    ])
@endsection

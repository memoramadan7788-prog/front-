@extends('layouts.app')

@section('title', $pageTitle ?? __('onboarding.candidate.page_title'))
@section('breadcrumb', __('onboarding.candidate.breadcrumb'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('onboarding.candidate.title') }}</h1>
    <p class="mt-1 text-sm text-hb-muted">
        {{ __('onboarding.candidate.eyebrow') }} ·
        <span data-onboarding-step-label>{{ __('onboarding.candidate.step_labels.1') }}</span>
    </p>
@endsection

@section('content')

    <div data-onboarding="candidate" data-onboarding-total="4" data-onboarding-start="1" data-onboarding-storage="hb_onboarding_candidate_v1">
        <x-stepper
            variant="underline"
            :current="1"
            :steps="[
                ['label' => __('onboarding.candidate.stepper.basics')],
                ['label' => __('onboarding.candidate.stepper.resume')],
                ['label' => __('onboarding.candidate.stepper.skills_preferences')],
                ['label' => __('onboarding.candidate.stepper.review_finish')],
            ]"
            class="mb-6"
        />

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_300px]">
            <form class="hb-surface p-5 sm:p-6" data-onboarding-form novalidate aria-label="{{ __('onboarding.candidate.form_aria') }}">
                @include('onboarding.partials.candidate.step-basics')
                @include('onboarding.partials.candidate.step-resume')
                @include('onboarding.partials.candidate.step-skills')
                @include('onboarding.partials.candidate.step-review')

                <div class="mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-hb-border pt-5">
                    <button type="button" class="hb-btn-outline" data-onboarding-back>
                        {{ __('common.actions.back') }}
                    </button>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" class="hb-btn-primary" data-onboarding-next>
                            {{ __('common.actions.continue') }}
                        </button>
                        <button type="submit" class="hb-btn-primary hidden" data-onboarding-finish>
                            {{ __('onboarding.candidate.finish_setup') }}
                        </button>
                    </div>
                </div>

                <p class="mt-3 text-xs text-hb-muted" data-onboarding-save-hint>
                    {{ __('onboarding.candidate.saved_hint') }}
                </p>

                <div class="mt-4 hidden rounded-lg border border-hb-info-soft bg-hb-info-soft px-3 py-2.5 text-sm text-hb-navy" data-onboarding-frontend-note role="status" tabindex="-1">
                    <p>{{ __('onboarding.candidate.ready_review') }}</p>
                    <p class="mt-2">
                        <a href="{{ route('candidate.dashboard') }}" class="font-semibold text-hb-blue hover:underline">
                            {{ __('onboarding.candidate.explore_workspace') }}
                        </a>
                    </p>
                </div>
            </form>

            <aside class="space-y-4" aria-label="{{ __('onboarding.candidate.progress_aria') }}">
                <div class="hb-surface p-4" data-candidate-preview>
                    <p class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ __('onboarding.candidate.profile_so_far') }}</p>
                    <p class="mt-3 text-sm font-medium text-hb-blue">
                        <span aria-hidden="true">●</span>
                        <span data-profile-progress>{{ __('onboarding.candidate.percent_complete', ['percent' => 25]) }}</span>
                    </p>
                    <p class="mt-2 text-base font-semibold text-hb-navy">Maya Chen</p>
                    <p class="mt-1 text-sm text-hb-muted">
                        <span data-preview-headline>Senior Product Designer</span><br>
                        <span data-preview-location>Austin, TX</span>
                        ·
                        <span data-preview-years>8</span> {{ __('onboarding.candidate.years_experience_suffix') }}
                    </p>
                    <ul class="mt-4 space-y-2 text-sm text-hb-muted">
                        <li class="flex gap-2"><span class="text-hb-success" aria-hidden="true">✓</span> {{ __('onboarding.candidate.preview.email_verified') }}</li>
                        <li class="flex gap-2"><span class="text-hb-success" aria-hidden="true">✓</span> {{ __('onboarding.candidate.preview.basics_completed') }}</li>
                        <li class="flex gap-2"><span class="text-hb-success" aria-hidden="true">✓</span> <span data-preview-resume>Maya_Chen_Resume.pdf</span> {{ __('onboarding.candidate.preview.imported') }}</li>
                        <li class="flex gap-2"><span class="text-hb-success" aria-hidden="true">✓</span> {{ __('onboarding.candidate.preview.experience_entries_reviewed') }}</li>
                    </ul>
                    <p class="mt-4 text-xs leading-relaxed text-hb-muted" data-profile-next>
                        {{ __('onboarding.candidate.preview.next') }}
                    </p>
                </div>

                <div class="hb-surface p-4">
                    <p class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ __('onboarding.candidate.in_control') }}</p>
                    <p class="mt-3 text-sm leading-relaxed text-hb-muted">
                        {{ __('onboarding.candidate.salary_privacy') }}
                    </p>
                </div>
            </aside>
        </div>

        <p class="mt-8 text-xs text-hb-muted">
            {{ __('common.footer.workspace_note', ['date' => 'October 20, 2026']) }}
        </p>
    </div>
@endsection

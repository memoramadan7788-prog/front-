@extends('layouts.app')

@section('title', $pageTitle ?? 'Build a profile that feels like you · Hirebridge')
@section('breadcrumb', '11 / My workspace / Profile & resume')

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">Build a profile that feels like you</h1>
    <p class="mt-1 text-sm text-hb-muted">
        Candidate onboarding ·
        <span data-onboarding-step-label>Step 3 of 4 · Skills &amp; work preferences</span>
    </p>
@endsection

@section('content')
    <div class="hb-mock-banner mb-6 rounded-lg text-start">
        Frontend UI only — profile data is not saved. Persistence requires Backend Team integration.
    </div>

    <div data-onboarding="candidate" data-onboarding-total="4" data-onboarding-start="3">
        <x-stepper
            variant="underline"
            :current="3"
            :steps="[
                ['label' => 'Basics'],
                ['label' => 'Resume import'],
                ['label' => 'Skills & preferences'],
                ['label' => 'Review & finish'],
            ]"
            class="mb-6"
        />

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_300px]">
            <form class="hb-surface p-5 sm:p-6" data-onboarding-form novalidate aria-label="Candidate profile setup">
                @include('onboarding.partials.candidate.step-basics')
                @include('onboarding.partials.candidate.step-resume')
                @include('onboarding.partials.candidate.step-skills')
                @include('onboarding.partials.candidate.step-review')

                <div class="mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-hb-border pt-5">
                    <button type="button" class="hb-btn-outline" data-onboarding-back>
                        Back
                    </button>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" class="hb-btn-primary" data-onboarding-next>
                            Continue to review
                        </button>
                        <button type="submit" class="hb-btn-primary hidden" data-onboarding-finish>
                            Finish profile setup
                        </button>
                    </div>
                </div>

                <p class="mt-3 text-xs text-hb-muted" data-onboarding-save-hint>
                    Saved just now · You can update these preferences at any time.
                </p>

                <div class="mt-4 hidden rounded-lg border border-hb-info-soft bg-hb-info-soft px-3 py-2.5 text-sm text-hb-navy" data-onboarding-frontend-note role="status">
                    Profile form validated on the client. No profile was saved — Backend Team onboarding persistence is required.
                </div>
            </form>

            <aside class="space-y-4" aria-label="Profile progress">
                <div class="hb-surface p-4" data-candidate-preview>
                    <p class="text-xs font-semibold tracking-wide text-hb-muted uppercase">Your profile so far</p>
                    <p class="mt-3 text-sm font-medium text-hb-blue">
                        <span aria-hidden="true">●</span>
                        <span data-profile-progress>75% complete</span>
                    </p>
                    <p class="mt-2 text-base font-semibold text-hb-navy">Maya Chen</p>
                    <p class="mt-1 text-sm text-hb-muted">
                        <span data-preview-headline>Senior Product Designer</span><br>
                        <span data-preview-location>Austin, TX</span>
                        ·
                        <span data-preview-years>8</span> years’ experience
                    </p>
                    <ul class="mt-4 space-y-2 text-sm text-hb-muted">
                        <li class="flex gap-2"><span class="text-hb-success" aria-hidden="true">✓</span> Email verified</li>
                        <li class="flex gap-2"><span class="text-hb-success" aria-hidden="true">✓</span> Basics completed</li>
                        <li class="flex gap-2"><span class="text-hb-success" aria-hidden="true">✓</span> <span data-preview-resume>Maya_Chen_Resume.pdf</span> imported</li>
                        <li class="flex gap-2"><span class="text-hb-success" aria-hidden="true">✓</span> 3 experience entries reviewed</li>
                    </ul>
                    <p class="mt-4 text-xs leading-relaxed text-hb-muted" data-profile-next>
                        Next: Review visibility and finish your profile.
                    </p>
                </div>

                <div class="hb-surface p-4">
                    <p class="text-xs font-semibold tracking-wide text-hb-muted uppercase">You’re in control</p>
                    <p class="mt-3 text-sm leading-relaxed text-hb-muted">
                        Salary preferences aren’t shown publicly. Recruiters only see your profile according to the visibility settings you choose.
                    </p>
                </div>
            </aside>
        </div>

        <p class="mt-8 text-xs text-hb-muted">
            Hirebridge · Private workspace · All times Central unless noted — October 20, 2026
        </p>
    </div>
@endsection

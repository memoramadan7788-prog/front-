@extends('layouts.app')

@section('title', $pageTitle ?? __('onboarding.company.page_title'))
@section('breadcrumb', __('onboarding.company.breadcrumb'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('onboarding.company.title') }}</h1>
    <p class="mt-1 text-sm text-hb-muted">
        {{ __('onboarding.company.eyebrow') }} ·
        <span data-onboarding-step-label>{{ __('onboarding.company.step_labels.2') }}</span>
    </p>
@endsection

@section('content')

    <div data-onboarding="company" data-onboarding-total="4" data-onboarding-start="2">
        <x-stepper
            variant="underline"
            :current="2"
            :steps="[
                ['label' => __('onboarding.company.stepper.identity')],
                ['label' => __('onboarding.company.stepper.workspace')],
                ['label' => __('onboarding.company.stepper.hiring')],
                ['label' => __('onboarding.company.stepper.invite')],
            ]"
            class="mb-6"
        />

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_300px]">
            <form class="hb-surface p-5 sm:p-6" data-onboarding-form novalidate aria-label="{{ __('onboarding.company.form_aria') }}">
                @include('onboarding.partials.company.step-identity')
                @include('onboarding.partials.company.step-workspace')
                @include('onboarding.partials.company.step-hiring')
                @include('onboarding.partials.company.step-invite')

                <div class="mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-hb-border pt-5">
                    <button type="button" class="hb-btn-outline" data-onboarding-back>
                        {{ __('common.actions.back') }}
                    </button>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" class="hb-btn-primary" data-onboarding-next>
                            {{ __('onboarding.company.save_continue') }}
                        </button>
                        <button type="submit" class="hb-btn-primary hidden" data-onboarding-finish>
                            {{ __('onboarding.company.finish') }}
                        </button>
                    </div>
                </div>

                <div class="mt-4 hidden rounded-lg border border-hb-info-soft bg-hb-info-soft px-3 py-2.5 text-sm text-hb-navy" data-onboarding-frontend-note role="status">
                    {{ __('onboarding.company.ready_review') }}
                    <p class="mt-2">
                        <a href="{{ route('onboarding.company.status') }}" class="font-semibold text-hb-blue hover:underline">
                            {{ __('onboarding.company.view_status') }}
                        </a>
                    </p>
                </div>
            </form>

            <aside class="space-y-4" aria-label="{{ __('onboarding.company.context_aria') }}">
                <div class="hb-surface p-4" data-workspace-preview>
                    <p class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ __('onboarding.company.workspace_preview') }}</p>
                    <p class="mt-3 text-sm font-medium text-hb-pending">
                        <span aria-hidden="true">●</span> {{ __('onboarding.company.setup_progress') }}
                    </p>
                    <p class="mt-2 text-lg font-semibold text-hb-navy" data-preview-name>Northstar Labs</p>
                    <p class="mt-1 text-sm text-hb-muted">
                        <span data-preview-industry>Software &amp; collaboration</span>
                        ·
                        <span data-preview-location>Austin, TX</span>
                    </p>
                    <p class="mt-4 text-xs leading-relaxed text-hb-muted">
                        {{ __('onboarding.company.workspace_owner') }}
                        <span class="block">(olivia@northstarlabs.example)</span>
                    </p>
                </div>

                <div class="hb-surface p-4">
                    <p class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ __('onboarding.company.up_next') }}</p>
                    <ul class="mt-3 space-y-3 text-sm text-hb-text">
                        <li>
                            <span class="font-semibold text-hb-navy">03</span>
                            <span class="text-hb-muted"> — {{ __('onboarding.company.up_next_hiring') }}</span>
                        </li>
                        <li>
                            <span class="font-semibold text-hb-navy">04</span>
                            <span class="text-hb-muted"> — {{ __('onboarding.company.invite_intro') }}</span>
                        </li>
                    </ul>
                    <p class="mt-4 text-xs leading-relaxed text-hb-muted">
                        {{ __('onboarding.company.publish_after_review') }}
                    </p>
                </div>
            </aside>
        </div>

        <p class="mt-8 text-xs text-hb-muted">
            {{ __('common.footer.workspace_note', ['date' => 'October 20, 2026']) }}
        </p>
    </div>
@endsection

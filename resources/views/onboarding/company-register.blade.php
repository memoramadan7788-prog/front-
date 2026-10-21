@extends('layouts.app')

@section('title', $pageTitle ?? 'Set up your hiring workspace · Hirebridge')
@section('breadcrumb', '12 / Company workspace / Overview')

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">Set up your hiring workspace</h1>
    <p class="mt-1 text-sm text-hb-muted">
        Company onboarding ·
        <span data-onboarding-step-label>Step 2 of 4 · Workspace details</span>
    </p>
@endsection

@section('content')
    <div class="hb-mock-banner mb-6 rounded-lg text-start">
        Frontend UI only — companies are not registered or approved here. Backend Team integration is required.
    </div>

    <div data-onboarding="company" data-onboarding-total="4" data-onboarding-start="2">
        <x-stepper
            variant="underline"
            :current="2"
            :steps="[
                ['label' => 'Company identity'],
                ['label' => 'Workspace details'],
                ['label' => 'Hiring preferences'],
                ['label' => 'Invite your team'],
            ]"
            class="mb-6"
        />

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_300px]">
            <form class="hb-surface p-5 sm:p-6" data-onboarding-form novalidate aria-label="Company workspace setup">
                @include('onboarding.partials.company.step-identity')
                @include('onboarding.partials.company.step-workspace')
                @include('onboarding.partials.company.step-hiring')
                @include('onboarding.partials.company.step-invite')

                <div class="mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-hb-border pt-5">
                    <button type="button" class="hb-btn-outline" data-onboarding-back>
                        Back
                    </button>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" class="hb-btn-primary" data-onboarding-next>
                            Save &amp; continue
                        </button>
                        <button type="submit" class="hb-btn-primary hidden" data-onboarding-finish>
                            Finish setup
                        </button>
                    </div>
                </div>

                <div class="mt-4 hidden rounded-lg border border-hb-info-soft bg-hb-info-soft px-3 py-2.5 text-sm text-hb-navy" data-onboarding-frontend-note role="status">
                    Workspace form validated on the client. No company was created or approved — Backend Team registration is required.
                    <p class="mt-2">
                        <a href="{{ route('onboarding.company.status') }}" class="font-semibold text-hb-blue hover:underline">
                            Preview pending-review status UI →
                        </a>
                    </p>
                </div>
            </form>

            <aside class="space-y-4" aria-label="Onboarding context">
                <div class="hb-surface p-4" data-workspace-preview>
                    <p class="text-xs font-semibold tracking-wide text-hb-muted uppercase">Workspace preview</p>
                    <p class="mt-3 text-sm font-medium text-hb-pending">
                        <span aria-hidden="true">●</span> Setup in progress
                    </p>
                    <p class="mt-2 text-lg font-semibold text-hb-navy" data-preview-name>Northstar Labs</p>
                    <p class="mt-1 text-sm text-hb-muted">
                        <span data-preview-industry>Software &amp; collaboration</span>
                        ·
                        <span data-preview-location>Austin, TX</span>
                    </p>
                    <p class="mt-4 text-xs leading-relaxed text-hb-muted">
                        Workspace owner: Olivia Bennett
                        <span class="block">(olivia@northstarlabs.example)</span>
                    </p>
                </div>

                <div class="hb-surface p-4">
                    <p class="text-xs font-semibold tracking-wide text-hb-muted uppercase">Up next</p>
                    <ul class="mt-3 space-y-3 text-sm text-hb-text">
                        <li>
                            <span class="font-semibold text-hb-navy">03</span>
                            <span class="text-hb-muted"> — Choose direct hiring, agency support, or both.</span>
                        </li>
                        <li>
                            <span class="font-semibold text-hb-navy">04</span>
                            <span class="text-hb-muted"> — Invite hiring managers and interviewers with scoped access.</span>
                        </li>
                    </ul>
                    <p class="mt-4 text-xs leading-relaxed text-hb-muted">
                        Job publishing becomes available after company review. You can save draft roles now.
                    </p>
                </div>
            </aside>
        </div>

        <p class="mt-8 text-xs text-hb-muted">
            Hirebridge · Private workspace · All times Central unless noted — October 20, 2026
        </p>
    </div>
@endsection

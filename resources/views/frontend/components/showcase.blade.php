@extends('layouts.app')

@section('title', 'Component showcase · Hirebridge')

@section('breadcrumb', 'Stage 2 / Frontend / Component showcase')

@section('page_header')
    <h1 class="hb-page-title">Component showcase</h1>
    <p class="mt-1 hb-body text-hb-muted">
        Reusable Blade components and shared partials aligned to Frame 51. Mock data only — no backend operations.
    </p>
@endsection

@section('page_actions')
    <x-button variant="secondary" size="sm" :href="route('frontend.layouts.app', ['role' => $uiRole])">
        Back to layout preview
    </x-button>
@endsection

@section('content')
    <div class="space-y-8">
        <x-card>
            <x-slot:header>
                <h2 class="font-semibold text-hb-navy">Buttons</h2>
            </x-slot:header>
            <div class="flex flex-wrap gap-3">
                <x-button variant="primary">Primary</x-button>
                <x-button variant="secondary">Secondary</x-button>
                <x-button variant="tertiary">Tertiary link</x-button>
                <x-button variant="destructive">Destructive</x-button>
                <x-button variant="primary" :loading="true">Saving</x-button>
                <x-button variant="primary" :disabled="true">Disabled</x-button>
            </div>
        </x-card>

        <x-card>
            <x-slot:header>
                <h2 class="hb-section-title">Badges & lifecycle</h2>
            </x-slot:header>
            <div class="flex flex-wrap gap-2">
                <x-badge variant="success" :dot="true">Hired</x-badge>
                <x-badge variant="pending" :dot="true">Draft offer</x-badge>
                <x-badge variant="error" :dot="true">Rejected</x-badge>
                <x-badge variant="info" :dot="true">Interview</x-badge>
                <x-badge variant="neutral">Neutral</x-badge>
                <x-status-badge status="applied" />
                <x-status-badge status="screening" />
                <x-status-badge status="withdrawn" />
                <x-badge variant="neutral">{{ __('common.sources.direct_hire') }}</x-badge>
                <x-badge variant="pending" :dot="true">{{ __('common.sources.agency_managed') }}</x-badge>
            </div>
        </x-card>

        <x-card>
            <x-slot:header>
                <h2 class="hb-section-title">Feedback & empty</h2>
            </x-slot:header>
            <div class="space-y-4">
                <x-alert variant="success">Preferences saved</x-alert>
                <x-alert variant="warning">You have unsaved changes</x-alert>
                <x-empty-state
                    title="No saved roles yet"
                    action-label="Discover jobs"
                    :action-href="route('jobs.index')"
                >
                    Saved roles will appear here when you bookmark openings.
                </x-empty-state>
                <x-skeleton :lines="3" />
            </div>
        </x-card>

        <x-card>
            <x-slot:header>
                <h2 class="font-semibold text-hb-navy">Tabs + timeline</h2>
            </x-slot:header>

            <x-tabs
                id="showcase-tabs"
                :items="[
                    ['key' => 'board', 'label' => 'Board'],
                    ['key' => 'list', 'label' => 'List'],
                    ['key' => 'activity', 'label' => 'Activity'],
                ]"
                active="board"
            >
                <div id="showcase-tabs-panel-board" role="tabpanel" aria-labelledby="showcase-tabs-tab-board">
                    <x-timeline :steps="[
                        ['label' => 'Applied', 'status' => 'completed'],
                        ['label' => 'Screening', 'status' => 'completed'],
                        ['label' => 'Interview', 'status' => 'current'],
                        ['label' => 'Offer', 'status' => 'pending'],
                        ['label' => 'Hired', 'status' => 'pending'],
                    ]" />
                </div>
                <div id="showcase-tabs-panel-list" role="tabpanel" aria-labelledby="showcase-tabs-tab-list" class="hidden" hidden>
                    <x-table>
                        <x-slot:head>
                            <th class="px-4 py-3 text-start">Candidate</th>
                            <th class="px-4 py-3 text-start">Stage</th>
                            <th class="px-4 py-3 text-start">Source</th>
                        </x-slot:head>
                        <tr>
                            <td class="px-4 py-3 font-medium">Maya Chen</td>
                            <td class="px-4 py-3"><x-badge variant="info" :dot="true">Interview</x-badge></td>
                            <td class="px-4 py-3 text-hb-muted">Direct hire</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium">Ethan Cole</td>
                            <td class="px-4 py-3"><x-badge variant="pending" :dot="true">Offer</x-badge></td>
                            <td class="px-4 py-3 text-hb-muted">Agency-managed</td>
                        </tr>
                    </x-table>
                </div>
                <div id="showcase-tabs-panel-activity" role="tabpanel" aria-labelledby="showcase-tabs-tab-activity" class="hidden" hidden>
                    <p class="text-sm text-hb-muted">Activity feed arrives with feature modules. This panel verifies tab switching.</p>
                </div>
            </x-tabs>
        </x-card>

        <x-card :padding="false">
            <x-slot:header>
                <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-hb-navy">Applicant pipeline</h2>
                        <p class="text-sm text-hb-muted">NS-204 · Senior Product Designer · mock candidates</p>
                    </div>
                    <x-button variant="primary" size="sm">Add candidate</x-button>
                </div>
            </x-slot:header>

            <div class="p-5">
                <x-kanban
                    :columns="$kanbanColumns"
                    footer="7 direct-hire · 5 agency-managed · 0 rejected · 0 withdrawn"
                />
            </div>
        </x-card>

        <x-card>
            <x-slot:header>
                <h2 class="font-semibold text-hb-navy">Slot picker</h2>
            </x-slot:header>
            <x-slot-picker
                name="interview_slot"
                :slots="$interviewSlots"
                value="2026-10-22T10:00"
            />
        </x-card>

        <x-card>
            <x-slot:header>
                <h2 class="font-semibold text-hb-navy">Empty table state</h2>
            </x-slot:header>
            <x-table empty="No records to display yet.">
                <x-slot:head>
                    <th class="px-4 py-3 text-start">Name</th>
                    <th class="px-4 py-3 text-start">Status</th>
                </x-slot:head>
            </x-table>
        </x-card>
    </div>
@endsection

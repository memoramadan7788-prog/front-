@extends('layouts.app')

@section('title', $pageTitle ?? __('company.evaluations.page_title', ['name' => $evaluation['applicant']]))
@section('breadcrumb', __('company.evaluations.breadcrumb'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy sm:text-3xl">
        {{ __('company.evaluations.title', ['name' => $evaluation['applicant']]) }}
    </h1>
    <p class="mt-1 text-sm text-hb-muted">{{ $evaluation['subtitle'] }}</p>
    <div class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-2 text-sm">
        <x-badge variant="info" :dot="true">{{ $evaluation['stage'] }}</x-badge>
        <x-badge variant="neutral" :dot="true">{{ $evaluation['source'] }}</x-badge>
        <span class="text-hb-muted">
            {{ __('company.evaluations.decision_owner_line', [
                'owner' => $evaluation['decision_owner'],
                'complete' => $evaluation['feedback_complete'],
            ]) }}
        </span>
    </div>
@endsection

@section('page_actions')
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('company.applicants.show', $evaluation['applicant_id']) }}" class="hb-btn-outline">
            {{ __('company.evaluations.back_to_applicant') }}
        </a>
        <a href="{{ route('company.interviews.index') }}" class="hb-btn-outline">
            {{ __('company.evaluations.back_to_calendar') }}
        </a>
    </div>
@endsection

@section('content')
    @php
        $evalI18n = [
            'clarification_feedback' => __('company.evaluations.js.clarification_feedback'),
            'revisit_feedback' => __('company.evaluations.js.revisit_feedback'),
            'expand_scores' => __('company.evaluations.js.expand_scores'),
            'collapse_scores' => __('company.evaluations.js.collapse_scores'),
        ];
    @endphp

    <div
        data-company-evaluation
        data-i18n="{{ json_encode($evalI18n, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) }}"
    >
        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_300px]">
            <div class="min-w-0 space-y-6">
                <section class="hb-surface p-5 sm:p-6" aria-labelledby="competency-heading">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h2 id="competency-heading" class="text-lg font-semibold text-hb-navy">{{ __('company.evaluations.competency_heading') }}</h2>
                            <p class="mt-1 text-xs text-hb-muted">{{ $evaluation['scale_legend'] }}</p>
                        </div>
                        <button type="button" class="hb-btn-outline text-xs" data-toggle-scores aria-expanded="true">
                            {{ __('company.evaluations.collapse_scores') }}
                        </button>
                    </div>

                    <div class="mt-5 overflow-x-auto rounded-lg border border-hb-border" data-scorecard>
                        <table class="w-full min-w-[40rem] text-start text-sm">
                            <caption class="sr-only">{{ __('company.evaluations.competency_heading') }}</caption>
                            <tbody class="divide-y divide-hb-border">
                                @foreach ($evaluation['competencies'] as $row)
                                    <tr>
                                        <th scope="row" class="bg-hb-page px-4 py-3 align-top font-semibold text-hb-navy">{{ $row['label'] }}</th>
                                        <td class="px-4 py-3 align-top text-hb-muted">
                                            {{ __('company.evaluations.alex_score', [
                                                'round' => $row['alex']['round'],
                                                'score' => $row['alex']['score'],
                                                'label' => $row['alex']['label'],
                                            ]) }}
                                        </td>
                                        <td class="px-4 py-3 align-top text-hb-muted">
                                            {{ __('company.evaluations.daniel_score', [
                                                'round' => $row['daniel']['round'],
                                                'score' => $row['daniel']['score'],
                                                'label' => $row['daniel']['label'],
                                            ]) }}
                                        </td>
                                        <td class="px-4 py-3 align-top text-hb-text">
                                            <span class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ __('company.evaluations.evidence_summary') }}</span>
                                            <p class="mt-1">{{ $row['summary'] }}</p>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="hb-surface p-5 sm:p-6" aria-labelledby="recommendations-heading">
                    <h2 id="recommendations-heading" class="text-lg font-semibold text-hb-navy">{{ __('company.evaluations.recommendations_heading') }}</h2>
                    <ul class="mt-5 space-y-4">
                        @foreach ($evaluation['recommendations'] as $rec)
                            <li class="rounded-lg border border-hb-border bg-hb-page px-4 py-3">
                                <div class="flex flex-wrap items-baseline justify-between gap-2">
                                    <p class="font-semibold text-hb-navy">{{ $rec['name'] }}</p>
                                    <p class="text-sm font-medium text-hb-blue">{{ $rec['verdict'] }} · {{ $rec['meta'] }}</p>
                                </div>
                                @if (! empty($rec['comment']))
                                    <p class="mt-2 text-sm text-hb-text">{{ $rec['comment'] }}</p>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </section>
            </div>

            <aside class="space-y-4">
                <section class="hb-surface p-5" aria-labelledby="decision-heading">
                    <h2 id="decision-heading" class="sr-only">{{ __('company.evaluations.decision_heading') }}</h2>
                    <x-badge variant="success" :dot="true">{{ $evaluation['decision']['status'] }}</x-badge>
                    <p class="mt-3 text-sm text-hb-muted">{{ $evaluation['decision']['detail'] }}</p>
                    <div class="mt-5 flex flex-col gap-2">
                        <button type="button" class="hb-btn-primary" disabled title="{{ __('company.evaluations.offer_soon') }}">
                            {{ __('company.evaluations.open_draft_offer') }}
                        </button>
                        <button type="button" class="hb-btn-outline" data-request-clarification>
                            {{ __('company.evaluations.request_clarification') }}
                        </button>
                        <button type="button" class="text-sm font-semibold text-hb-blue hover:underline" data-revisit-decision>
                            {{ __('company.evaluations.revisit_decision') }}
                        </button>
                        <p class="hidden text-xs text-hb-blue" data-eval-feedback role="status"></p>
                    </div>
                </section>

                <section class="rounded-lg border border-hb-blue/20 bg-hb-info-soft/50 p-4 text-sm text-hb-text">
                    <p>{{ $evaluation['evidence_note'] }}</p>
                </section>
            </aside>
        </div>

        <p class="mt-8 text-center text-xs text-hb-muted">{{ $evaluation['footer'] }}</p>
    </div>

    @push('scripts')
        <script>
            (() => {
                const root = document.querySelector('[data-company-evaluation]');
                if (!root) return;
                const i18n = JSON.parse(root.getAttribute('data-i18n') || '{}');
                const scorecard = root.querySelector('[data-scorecard]');
                const toggle = root.querySelector('[data-toggle-scores]');
                const feedback = root.querySelector('[data-eval-feedback]');

                toggle?.addEventListener('click', () => {
                    const expanded = toggle.getAttribute('aria-expanded') === 'true';
                    toggle.setAttribute('aria-expanded', expanded ? 'false' : 'true');
                    scorecard?.classList.toggle('hidden', expanded);
                    toggle.textContent = expanded ? (i18n.expand_scores || '') : (i18n.collapse_scores || '');
                });

                root.querySelector('[data-request-clarification]')?.addEventListener('click', () => {
                    if (feedback) {
                        feedback.textContent = i18n.clarification_feedback || '';
                        feedback.classList.remove('hidden');
                    }
                });

                root.querySelector('[data-revisit-decision]')?.addEventListener('click', () => {
                    if (feedback) {
                        feedback.textContent = i18n.revisit_feedback || '';
                        feedback.classList.remove('hidden');
                    }
                });
            })();
        </script>
    @endpush
@endsection

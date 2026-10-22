@extends('layouts.app')

@section('title', $pageTitle ?? __('interviewer.evaluations.page_title', ['name' => $scorecard['interview']['applicant']]))
@section('breadcrumb', __('interviewer.evaluations.breadcrumb'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy sm:text-3xl">{{ $scorecard['title'] }}</h1>
    <p class="mt-1 text-sm text-hb-muted">{{ $scorecard['meta'] }}</p>
@endsection

@section('page_actions')
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ $scorecard['index_url'] }}" class="hb-btn-outline">{{ __('interviewer.evaluations.back_queue') }}</a>
        @if (! empty($scorecard['live_url']))
            <a href="{{ $scorecard['live_url'] }}" class="hb-btn-outline">{{ __('interviewer.evaluations.back_live') }}</a>
        @endif
    </div>
@endsection

@section('content')
    @php
        $interview = $scorecard['interview'];
        $seed = $scorecard['seed'];
    @endphp

    <div
        data-interviewer-scorecard
        data-interview-id="{{ $interview['id'] }}"
        data-storage-key="{{ $scorecard['storage_key'] }}"
        data-seed="{{ json_encode([
            'ratings' => collect($scorecard['competencies'])->mapWithKeys(fn ($c) => [$c['id'] => $c['seed_rating'] ?? ''])->all(),
            'evidence' => collect($scorecard['competencies'])->mapWithKeys(fn ($c) => [$c['id'] => $c['seed_evidence'] ?? ''])->all(),
            'strengths' => $seed['strengths'] ?? '',
            'concerns' => $seed['concerns'] ?? '',
            'recommendation' => $seed['recommendation'] ?? '',
            'attested' => false,
        ], JSON_UNESCAPED_UNICODE) }}"
        data-i18n="{{ json_encode([
            'draft_saved' => __('interviewer.evaluations.draft_saved'),
            'draft_unsaved' => __('interviewer.evaluations.draft_unsaved'),
            'status_draft' => __('interviewer.evaluations.badge_draft'),
            'status_ready' => __('interviewer.evaluations.badge_ready_local'),
            'err_rating' => __('interviewer.evaluations.err_rating'),
            'err_strengths' => __('interviewer.evaluations.err_strengths'),
            'err_concerns' => __('interviewer.evaluations.err_concerns'),
            'err_recommendation' => __('interviewer.evaluations.err_recommendation'),
            'err_attest' => __('interviewer.evaluations.err_attest'),
            'progress' => __('interviewer.evaluations.progress'),
            'submit_blocked' => __('interviewer.evaluations.submit_blocked'),
            'review_ok' => __('interviewer.evaluations.review_ok'),
            'strengths_label' => __('interviewer.evaluations.strengths_label'),
            'concerns_label' => __('interviewer.evaluations.concerns_label'),
            'overall_label' => __('interviewer.evaluations.overall_label'),
        ], JSON_UNESCAPED_UNICODE) }}"
    >
        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_300px]">
            <div class="min-w-0 space-y-6">
                <section class="hb-surface p-5 sm:p-6" aria-labelledby="comp-ratings-heading" data-score-panel="edit">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div>
                            <h2 id="comp-ratings-heading" class="text-lg font-semibold text-hb-navy">{{ __('interviewer.evaluations.competency_heading') }}</h2>
                            <p class="mt-1 text-xs text-hb-muted">{{ $scorecard['scale_legend'] }}</p>
                        </div>
                        <p class="text-xs font-medium text-hb-blue" data-score-progress role="status" aria-live="polite"></p>
                    </div>

                    <ul class="mt-5 space-y-5">
                        @foreach ($scorecard['competencies'] as $comp)
                            <li class="rounded-xl border border-hb-border p-4" data-competency="{{ $comp['id'] }}">
                                <fieldset>
                                    <legend class="font-semibold text-hb-navy">{{ $comp['label'] }}</legend>
                                    <p class="mt-1 text-xs text-hb-muted" data-rating-summary aria-live="polite"></p>
                                    <div class="mt-3 flex flex-wrap gap-2" role="radiogroup" aria-label="{{ $comp['label'] }}">
                                        @foreach ($scorecard['rating_options'] as $opt)
                                            <label class="relative inline-flex min-h-11 cursor-pointer items-center rounded-lg border border-hb-border px-2.5 py-1.5 text-xs font-medium text-hb-text has-[:checked]:border-hb-blue has-[:checked]:bg-hb-info-soft has-[:checked]:text-hb-blue">
                                                <input
                                                    type="radio"
                                                    class="absolute inset-0 z-10 h-full w-full cursor-pointer opacity-0"
                                                    name="rating_{{ $comp['id'] }}"
                                                    value="{{ $opt['value'] }}"
                                                    data-score-rating="{{ $comp['id'] }}"
                                                    @checked(($comp['seed_rating'] ?? '') === $opt['value'])
                                                >
                                                <span class="pointer-events-none" aria-hidden="true">{{ $opt['value'] === 'no' ? 'N/O' : $opt['value'] }} · {{ $opt['label'] }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    <p class="hb-field-error hidden" data-rating-error="{{ $comp['id'] }}" hidden></p>
                                    <label class="mt-3 block">
                                        <span class="mb-1.5 block text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ __('interviewer.evaluations.evidence_label') }}</span>
                                        <textarea
                                            class="hb-input min-h-[3.5rem] w-full resize-y"
                                            rows="2"
                                            dir="auto"
                                            data-score-evidence="{{ $comp['id'] }}"
                                            placeholder="{{ __('interviewer.evaluations.evidence_placeholder') }}"
                                        >{{ $comp['seed_evidence'] ?? '' }}</textarea>
                                    </label>
                                </fieldset>
                            </li>
                        @endforeach
                    </ul>
                </section>

                <section class="hb-surface p-5 sm:p-6" aria-labelledby="rec-heading" data-score-panel="edit">
                    <h2 id="rec-heading" class="text-lg font-semibold text-hb-navy">{{ __('interviewer.evaluations.recommendation_heading') }}</h2>

                    <label class="mt-5 block">
                        <span class="mb-1.5 block text-sm font-semibold text-hb-navy">
                            {{ __('interviewer.evaluations.strengths_label') }}
                            <span class="text-hb-error" aria-hidden="true">*</span>
                        </span>
                        <textarea
                            class="hb-input min-h-[6rem] w-full resize-y"
                            rows="4"
                            dir="auto"
                            data-score-strengths
                            required
                            aria-required="true"
                            placeholder="{{ __('interviewer.evaluations.strengths_placeholder') }}"
                        >{{ $seed['strengths'] ?? '' }}</textarea>
                        <span class="hb-field-error hidden" data-strengths-error hidden></span>
                    </label>

                    <label class="mt-5 block">
                        <span class="mb-1.5 block text-sm font-semibold text-hb-navy">
                            {{ __('interviewer.evaluations.concerns_label') }}
                            <span class="text-hb-error" aria-hidden="true">*</span>
                        </span>
                        <textarea
                            class="hb-input min-h-[6rem] w-full resize-y"
                            rows="4"
                            dir="auto"
                            data-score-concerns
                            required
                            aria-required="true"
                            placeholder="{{ __('interviewer.evaluations.concerns_placeholder') }}"
                        >{{ $seed['concerns'] ?? '' }}</textarea>
                        <span class="hb-field-error hidden" data-concerns-error hidden></span>
                    </label>

                    <label class="mt-5 block">
                        <span class="mb-1.5 block text-sm font-semibold text-hb-navy">
                            {{ __('interviewer.evaluations.overall_label') }}
                            <span class="text-hb-error" aria-hidden="true">*</span>
                        </span>
                        <select class="hb-input w-full" data-score-recommendation required aria-required="true">
                            <option value="">{{ __('interviewer.evaluations.overall_placeholder') }}</option>
                            @foreach ($scorecard['recommendations'] as $rec)
                                <option value="{{ $rec['value'] }}" @selected(($seed['recommendation'] ?? '') === $rec['value'])>
                                    {{ $rec['label'] }}
                                </option>
                            @endforeach
                        </select>
                        <span class="hb-field-error hidden" data-recommendation-error hidden></span>
                    </label>
                </section>

                <section class="hb-surface hidden p-5 sm:p-6" data-score-panel="review" hidden aria-labelledby="review-heading">
                    <h2 id="review-heading" class="text-lg font-semibold text-hb-navy">{{ __('interviewer.evaluations.review_heading') }}</h2>
                    <p class="mt-2 text-sm text-hb-muted">{{ __('interviewer.evaluations.review_intro') }}</p>
                    <div class="mt-5 space-y-4 text-sm" data-score-review-body></div>
                    <div class="mt-5 flex flex-wrap gap-2">
                        <button type="button" class="hb-btn-outline" data-score-edit>
                            {{ __('interviewer.evaluations.return_edit') }}
                        </button>
                        <button type="button" class="hb-btn-primary" data-score-save>
                            {{ __('interviewer.evaluations.save_draft') }}
                        </button>
                    </div>
                </section>

                <div class="hb-surface p-5 sm:p-6" data-score-panel="edit">
                    <label class="flex cursor-pointer items-start gap-3 text-sm">
                        <input
                            type="checkbox"
                            class="mt-0.5 h-4 w-4 rounded border-hb-border text-hb-blue focus-visible:ring-2 focus-visible:ring-hb-blue/40"
                            data-score-attest
                        >
                        <span class="text-hb-text">{{ __('interviewer.evaluations.attest_label') }}</span>
                    </label>
                    <p class="hb-field-error mt-2 hidden" data-attest-error hidden></p>
                    <div class="mt-5 flex flex-wrap items-center gap-2">
                        <button type="button" class="hb-btn-outline" data-score-save>
                            {{ __('interviewer.evaluations.save_draft') }}
                        </button>
                        <button type="button" class="hb-btn-outline" data-score-review>
                            {{ __('interviewer.evaluations.review') }}
                        </button>
                        <button
                            type="button"
                            class="hb-btn-primary"
                            data-score-submit
                            title="{{ __('interviewer.evaluations.submit_blocked') }}"
                        >
                            {{ __('interviewer.evaluations.submit') }}
                        </button>
                    </div>
                    <p class="mt-3 text-xs text-hb-muted" data-score-draft-status role="status" aria-live="polite"></p>
                    <p class="mt-2 text-xs text-hb-muted">{{ __('interviewer.evaluations.submit_footnote') }}</p>
                </div>
            </div>

            <aside class="space-y-4">
                <section class="hb-surface p-5" aria-labelledby="visibility-heading">
                    <h2 id="visibility-heading" class="text-base font-semibold text-hb-navy">{{ __('interviewer.evaluations.visibility_heading') }}</h2>
                    <div class="mt-3">
                        <x-badge variant="pending" :dot="true" data-score-status-badge>{{ __('interviewer.evaluations.badge_draft') }}</x-badge>
                    </div>
                    <p class="mt-3 text-sm text-hb-muted">{{ $scorecard['shared_with'] }}</p>
                    <p class="mt-2 text-sm text-hb-muted">{{ $scorecard['visibility_note'] }}</p>
                    <dl class="mt-4 space-y-2 text-sm">
                        <div>
                            <dt class="text-xs text-hb-muted">{{ __('interviewer.prepare.label_interview_id') }}</dt>
                            <dd class="font-medium text-hb-text" dir="ltr">{{ $interview['id'] }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-hb-muted">{{ __('interviewer.schedule.candidate_role') }}</dt>
                            <dd class="text-hb-text">{{ $interview['applicant'] }} · {{ $interview['job_code'] }}</dd>
                        </div>
                    </dl>
                </section>

                <section class="rounded-xl border border-hb-blue/20 bg-hb-info-soft/50 p-5" aria-labelledby="tip-heading">
                    <h2 id="tip-heading" class="text-sm font-semibold text-hb-navy">{{ $scorecard['tip_title'] }}</h2>
                    <p class="mt-2 text-sm text-hb-text">{{ $scorecard['tip_body'] }}</p>
                </section>
            </aside>
        </div>

        <p class="mt-8 text-center text-xs text-hb-muted">{{ $scorecard['footer'] }}</p>
    </div>

    @include('layouts.modal', [
        'modalId' => 'score-submit-modal',
        'modalTitle' => __('interviewer.evaluations.submit_modal_title'),
        'modalSize' => 'md',
        'modalBody' => '<p class="text-sm text-hb-text">'.e(__('interviewer.evaluations.submit_modal_body')).'</p><p class="mt-3 text-sm text-hb-muted">'.e(__('interviewer.evaluations.submit_modal_note')).'</p>',
        'modalFooter' => '<button type="button" class="hb-btn-outline" data-hb-modal-close>'.e(__('common.actions.close')).'</button><button type="button" class="hb-btn-primary" data-score-save-and-close>'.e(__('interviewer.evaluations.save_draft')).'</button>',
    ])
@endsection

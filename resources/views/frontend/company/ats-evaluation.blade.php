@extends('frontend.layouts.app')

@section('title', __('ats.page_title'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('ats.page_title') }}</h1>
@endsection

@section('page_actions')
    @if ($canRerun)
        <form method="POST" action="{{ route('frontend.company.applications.ats.start', ['applicationId' => $applicationId]) }}">
            @csrf
            <x-button type="submit">{{ $ats ? __('ats.rerun') : __('ats.run') }}</x-button>
        </form>
    @endif
@endsection

@section('content')
{{-- Every value below comes from the AI or from a candidate resume: always print it escaped, never as raw HTML. --}}

@if ($autoRefresh)
    @push('head')
        <meta http-equiv="refresh" content="6">
    @endpush
@endif

<x-alert variant="info" class="mb-4">
    {{ $ats['advisory_note'] ?? __('ats.advisory_note') }}
    {{ __('company.applicants.ai.not_rejection_note') }}
</x-alert>

@if ($ats === null)
    <x-card>
        <x-empty-state :title="__('ats.none_title')">{{ __('ats.none_body') }}</x-empty-state>
    </x-card>

@elseif ($ats['run_status'] === 'pending')
    @if ($ats['stale'])
        <x-alert variant="warning" role="alert">
            <p class="font-semibold">{{ __('ats.pending_stale_title') }}</p>
            <p class="mt-1">{{ __('ats.pending_stale_body') }}</p>
        </x-alert>
    @else
        <x-alert variant="info">
            <p class="font-semibold">{{ __('ats.pending_title') }}</p>
            <p class="mt-1">{{ __('ats.pending_body') }}</p>
        </x-alert>
    @endif

@elseif ($ats['run_status'] === 'failed')
    <x-alert variant="error" role="alert">
        <p class="font-semibold">{{ __('ats.failed_title') }}</p>
        <p class="mt-1">{{ __('ats.failed_body') }}</p>
        @if ($ats['error'])
            <p class="mt-2 text-xs opacity-80">{{ $ats['error'] }}</p>
        @endif
    </x-alert>

@else
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">

            {{-- Skills (deterministic: matched by the backend, not by the AI) --}}
            <x-card>
                <x-slot:header>
                    <h2 class="text-lg font-bold text-hb-navy">{{ __('company.applicants.ai.matched_skills') }} / {{ __('company.applicants.ai.missing_skills') }}</h2>
                </x-slot:header>

                <h3 class="text-sm font-semibold text-hb-navy">{{ __('company.applicants.ai.matched_skills') }}</h3>
                @if ($ats['matched_detail'] === [])
                    <p class="mt-2 text-sm text-hb-muted">{{ __('ats.none_matched') }}</p>
                @else
                    <ul class="mt-2 flex flex-wrap gap-2">
                        @foreach ($ats['matched_detail'] as $skill)
                            <li>
                                <x-badge variant="success" :dot="true">{{ $skill['skill'] }}</x-badge>
                                @if ($skill['note'])
                                    <span class="ms-1 text-xs text-hb-muted">{{ $skill['note'] }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif

                <h3 class="mt-5 text-sm font-semibold text-hb-navy">{{ __('company.applicants.ai.missing_skills') }}</h3>
                @if ($ats['missing_skills'] === [])
                    <p class="mt-2 text-sm text-hb-muted">{{ __('ats.none_missing') }}</p>
                @else
                    <ul class="mt-2 flex flex-wrap gap-2">
                        @foreach ($ats['missing_skills'] as $skill)
                            <li><x-badge variant="pending" :dot="true">{{ $skill }}</x-badge></li>
                        @endforeach
                    </ul>
                @endif

                @if ($ats['skills_source'])
                    <p class="mt-4 text-xs text-hb-muted">
                        {{ $ats['skills_source'] === 'job' ? __('ats.skills_source_job') : __('ats.skills_source_model') }}
                    </p>
                @endif
            </x-card>

            {{-- Strengths (every one has a quote that exists in the resume) --}}
            <x-card>
                <x-slot:header>
                    <h2 class="text-lg font-bold text-hb-navy">{{ __('ats.strengths_heading') }}</h2>
                    <p class="mt-1 text-xs text-hb-muted">{{ __('ats.strengths_hint') }}</p>
                </x-slot:header>

                @forelse ($ats['strengths'] as $strength)
                    <div class="@if (! $loop->first) mt-4 @endif">
                        <p class="text-sm font-medium text-hb-navy">{{ $strength['text'] }}</p>
                        <blockquote class="mt-1 border-s border-hb-border ps-4 text-sm text-hb-muted">{{ $strength['evidence'] }}</blockquote>
                    </div>
                @empty
                    <p class="text-sm text-hb-muted">{{ __('ats.strengths_none') }}</p>
                @endforelse

                @if ($ats['dropped_strengths'] > 0)
                    <p class="mt-4 text-xs text-hb-muted">{{ __('ats.dropped_note', ['count' => $ats['dropped_strengths']]) }}</p>
                @endif
            </x-card>

            {{-- AI opinion (NOT verified) --}}
            <x-card>
                <x-slot:header>
                    <h2 class="text-lg font-bold text-hb-navy">{{ __('ats.opinion_heading') }}</h2>
                    <p class="mt-1 text-xs text-hb-pending">{{ __('ats.opinion_note') }}</p>
                </x-slot:header>

                <dl class="space-y-4 text-sm">
                    @if ($ats['summary'])
                        <div>
                            <dt class="font-semibold text-hb-navy">{{ __('ats.summary_label') }}</dt>
                            <dd class="mt-1 text-hb-text">{{ $ats['summary'] }}</dd>
                        </div>
                    @endif
                    @if ($ats['experience_match'])
                        <div>
                            <dt class="font-semibold text-hb-navy">{{ __('ats.experience_label') }}</dt>
                            <dd class="mt-1 text-hb-text">{{ $ats['experience_match'] }}</dd>
                        </div>
                    @endif
                    @if ($ats['education_match'])
                        <div>
                            <dt class="font-semibold text-hb-navy">{{ __('ats.education_label') }}</dt>
                            <dd class="mt-1 text-hb-text">{{ $ats['education_match'] }}</dd>
                        </div>
                    @endif
                    @if ($ats['weaknesses'] !== [])
                        <div>
                            <dt class="font-semibold text-hb-navy">{{ __('ats.weaknesses_label') }}</dt>
                            <dd class="mt-1">
                                <ul class="space-y-1 text-hb-text">
                                    @foreach ($ats['weaknesses'] as $weakness)
                                        <li class="flex gap-2"><span aria-hidden="true">•</span><span>{{ $weakness }}</span></li>
                                    @endforeach
                                </ul>
                            </dd>
                        </div>
                    @endif
                </dl>

                @if ($ats['cv_truncated'])
                    <p class="mt-4 text-xs text-hb-muted">{{ __('ats.truncated_note') }}</p>
                @endif
            </x-card>
        </div>

        <div class="space-y-6">

            {{-- Score --}}
            <x-card>
                <x-slot:header>
                    <h2 class="text-lg font-bold text-hb-navy">{{ __('company.applicants.ai.score') }}</h2>
                </x-slot:header>

                <p class="text-4xl font-semibold tracking-tight text-hb-navy">{{ $ats['score_label'] ?? '—' }}</p>
                <div class="mt-3 flex flex-wrap gap-2">
                    @if ($ats['tier_label'])
                        <x-badge :variant="$ats['tier_variant']" :dot="true">{{ $ats['tier_label'] }}</x-badge>
                    @endif
                    <x-badge :variant="$ats['badge_variant']">{{ $ats['badge_label'] }}</x-badge>
                </div>
                @if ($ats['recommendation_label'])
                    <p class="mt-3 text-sm text-hb-muted">
                        {{ __('company.applicants.ai.recommendation') }}:
                        <span class="font-medium text-hb-navy">{{ $ats['recommendation_label'] }}</span>
                    </p>
                @endif
                @if ($ats['below_threshold'])
                    <p class="mt-3 text-xs text-hb-muted">{{ __('company.applicants.ai.low_match_note') }}</p>
                @endif
            </x-card>

            {{-- Score breakdown (calculated in code) --}}
            <x-card>
                <x-slot:header>
                    <h2 class="text-lg font-bold text-hb-navy">{{ __('ats.breakdown_heading') }}</h2>
                    <p class="mt-1 text-xs text-hb-muted">{{ __('ats.breakdown_note') }}</p>
                </x-slot:header>

                <div class="space-y-4">
                    @foreach ($ats['breakdown'] as $part)
                        <div>
                            <div class="flex items-baseline justify-between gap-2 text-sm">
                                <span class="font-medium text-hb-navy">{{ $part['label'] }}</span>
                                <span class="text-hb-muted">
                                    @if ($part['percent'] === null)
                                        {{ __('ats.component_excluded') }}
                                    @else
                                        {{ $part['percent'] }}%
                                    @endif
                                </span>
                            </div>
                            @if ($part['percent'] !== null)
                                <div class="mt-1 h-2 rounded-full bg-hb-page" role="presentation">
                                    <div class="h-2 rounded-full bg-hb-accent" style="width: {{ (int) $part['percent'] }}%"></div>
                                </div>
                                @if ($part['weight_percent'] !== null)
                                    <p class="mt-1 text-xs text-hb-muted">{{ __('ats.weight', ['percent' => $part['weight_percent']]) }}</p>
                                @endif
                            @endif
                        </div>
                    @endforeach
                </div>
            </x-card>

            {{-- Human review --}}
            <x-card>
                <x-slot:header>
                    <h2 class="text-lg font-bold text-hb-navy">{{ __('ats.review_heading') }}</h2>
                </x-slot:header>

                @if ($ats['human_decision_label'])
                    <p class="text-sm font-medium text-hb-navy">{{ $ats['human_decision_label'] }}</p>
                    @if ($ats['override_reason'])
                        <p class="mt-1 text-sm text-hb-muted">{{ __('company.applicants.ai.override_reason_label') }}: {{ $ats['override_reason'] }}</p>
                    @endif
                    @if ($ats['decided_at'])
                        <p class="mt-1 text-xs text-hb-muted">{{ \App\Support\Rows::date($ats['decided_at']) }}</p>
                    @endif
                @else
                    <p class="text-sm text-hb-muted">{{ __('company.applicants.ai.hr_none') }}</p>
                @endif

                <form method="POST" action="{{ route('frontend.company.applications.ats.decision', ['applicationId' => $applicationId]) }}" class="mt-5 space-y-4 border-t border-hb-border pt-5">
                    @csrf
                    <p class="text-xs text-hb-muted">{{ __('ats.review_intro') }}</p>

                    <x-form.select name="decision" :label="__('ats.decision_label')" :required="true" :error="$errors->first('decision')">
                        <option value="">{{ __('ats.decision_select') }}</option>
                        <option value="accept" @selected(old('decision') === 'accept')>{{ __('ats.decision_accept') }}</option>
                        <option value="override" @selected(old('decision') === 'override')>{{ __('ats.decision_override') }}</option>
                    </x-form.select>

                    <x-form.textarea name="reason" :label="__('ats.reason_label')" rows="3" :error="$errors->first('reason')">{{ old('reason') }}</x-form.textarea>
                    <p class="-mt-1 text-xs text-hb-muted">{{ __('ats.reason_hint') }}</p>

                    <x-button type="submit">{{ __('ats.save_review') }}</x-button>
                </form>
            </x-card>

            @if ($ats['model_version'])
                <p class="text-xs text-hb-muted">{{ __('ats.meta_model', ['model' => $ats['model_version']]) }}</p>
            @endif
        </div>
    </div>
@endif
@endsection

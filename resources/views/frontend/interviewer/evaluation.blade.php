@extends('frontend.layouts.app')

@section('title', __('Interview scorecard'))

@section('page_header')
    <div>
        <p class="text-sm font-semibold text-hb-blue">{{ __('Interviewer workspace') }}</p>
        <h1 class="mt-1 text-2xl font-semibold tracking-tight text-hb-navy">{{ __('Interview scorecard') }}</h1>
    </div>
@endsection

@section('content')
<div class="mx-auto max-w-4xl space-y-5">
    <section class="rounded-2xl border border-hb-border bg-white p-5 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wide text-hb-muted">{{ __('Interview') }} #{{ $interviewId }}</p>
        <h2 class="mt-1 text-lg font-bold text-hb-navy">{{ data_get($interview, 'job.title') ?? data_get($interview, 'title') ?? __('Interview evaluation') }}</h2>
        <p class="mt-1 text-sm text-hb-muted">
            {{ data_get($interview, 'candidate.name') ?? data_get($interview, 'application.candidate.name') ?? __('Candidate details are limited to your assigned interview scope.') }}
        </p>
    </section>

    @if (session('success_key'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900" role="status">{{ __(session('success_key')) }}</div>
    @endif
    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-900" role="alert">
            <p class="font-semibold">{{ __('Please review the highlighted scorecard fields.') }}</p>
            <ul class="mt-2 list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <section class="rounded-2xl border border-hb-border bg-white p-5 shadow-sm">
        <div class="mb-5">
            <h2 class="text-lg font-bold text-hb-navy">{{ __('Evaluation') }}</h2>
            <p class="mt-1 text-sm text-hb-muted">{{ __('Rate each competency from 1 to 5 or choose N/O if it was not observed.') }}</p>
        </div>
        <form method="POST" action="{{ route('frontend.interviewer.interviews.evaluation.submit', ['interviewId' => $interviewId]) }}" data-scorecard data-interview-id="{{ $interviewId }}" data-has-errors="{{ $errors->any() ? 'true' : 'false' }}" class="space-y-5">
            @csrf
            <fieldset class="space-y-3">
                <legend class="sr-only">{{ __('Competency ratings') }}</legend>
                @foreach ([
                    'technical' => __('Technical expertise'),
                    'communication' => __('Communication'),
                    'problem_solving' => __('Problem solving'),
                    'collaboration' => __('Collaboration'),
                    'role_fit' => __('Role fit'),
                ] as $key => $label)
                    <label class="flex flex-col gap-2 rounded-xl border border-hb-border p-3 sm:flex-row sm:items-center sm:justify-between">
                        <span class="font-medium text-hb-navy">{{ $label }}</span>
                        <select name="ratings[{{ $key }}]" required class="rounded-lg border border-hb-border bg-white px-3 py-2 text-sm text-hb-text sm:min-w-40">
                            <option value="">{{ __('Choose rating') }}</option>
                            @for ($rating = 1; $rating <= 5; $rating++)
                                <option value="{{ $rating }}" @selected(old("ratings.{$key}") == (string) $rating)>{{ $rating }} — {{ $rating === 1 ? __('Needs improvement') : ($rating === 5 ? __('Excellent') : __('Rating :number', ['number' => $rating])) }}</option>
                            @endfor
                            <option value="no" @selected(old("ratings.{$key}") === 'no')>{{ __('N/O — Not observed') }}</option>
                        </select>
                    </label>
                @endforeach
                @error('ratings')
                    <p class="text-sm text-red-700">{{ $message }}</p>
                @enderror
            </fieldset>

            <div class="grid gap-4 md:grid-cols-2">
                <x-form.textarea name="strengths" :label="__('Strengths')" rows="4" :required="true" :error="$errors->first('strengths')">{{ old('strengths') }}</x-form.textarea>
                <x-form.textarea name="concerns" :label="__('Concerns')" rows="4" :required="true" :error="$errors->first('concerns')">{{ old('concerns') }}</x-form.textarea>
            </div>

            <fieldset>
                <legend class="mb-2 text-sm font-semibold text-hb-text">{{ __('Overall recommendation') }}</legend>
                <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach (['strong_yes' => __('Strong yes'), 'yes' => __('Yes'), 'maybe' => __('Maybe'), 'no' => __('No')] as $value => $label)
                        <label class="flex items-center gap-2 rounded-lg border border-hb-border p-3 text-sm text-hb-text">
                            <input type="radio" name="recommendation" value="{{ $value }}" required @checked(old('recommendation') === $value)>
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @error('recommendation')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </fieldset>

            <x-form.textarea name="candidate_feedback" :label="__('Feedback shared with the candidate (optional)')" rows="3" :error="$errors->first('candidate_feedback')">{{ old('candidate_feedback') }}</x-form.textarea>

            <p class="rounded-lg bg-sky-50 p-3 text-sm leading-6 text-sky-950">{{ __('Your private scorecard is submitted only for this interviewer-assigned interview. Strengths, concerns, ratings, and recommendation are internal; only the separate candidate feedback field is marked for candidate visibility.') }}</p>
            <p class="text-xs text-hb-muted">{{ __('Drafts are saved only in this browser and are not submitted until you choose Submit scorecard.') }}</p>
            <p data-draft-status class="min-h-5 text-sm text-emerald-800" role="status" aria-live="polite"></p>

            <div class="flex flex-wrap gap-3 border-t border-hb-border pt-4">
                <button type="button" data-save-scorecard-draft class="rounded-lg border border-hb-border px-4 py-2.5 text-sm font-semibold text-hb-navy hover:bg-slate-50">{{ __('Save draft on this device') }}</button>
                <button type="submit" class="rounded-lg bg-hb-blue px-4 py-2.5 text-sm font-semibold text-white hover:bg-hb-navy">{{ __('Submit scorecard') }}</button>
                <a href="{{ route('frontend.interviewer.interviews.show', ['interviewId' => $interviewId]) }}" class="rounded-lg px-4 py-2.5 text-sm font-semibold text-hb-muted hover:bg-slate-50">{{ __('Cancel') }}</a>
            </div>
        </form>
    </section>
</div>

<script>
(() => {
    const form = document.querySelector('[data-scorecard]');
    if (!form) return;
    const key = `interviewer-scorecard-draft-v1-${form.dataset.interviewId}`;
    const status = form.querySelector('[data-draft-status]');
    const fields = [...form.querySelectorAll('input:not([type="hidden"]), select, textarea')];
    const clearDraft = @json((bool) session('clear_scorecard_draft'));

    try {
        if (clearDraft) {
            localStorage.removeItem(key);
        } else if (form.dataset.hasErrors !== 'true') {
            const draft = localStorage.getItem(key);
            if (draft) {
                const values = JSON.parse(draft);
                fields.forEach((field) => {
                    if (field.type === 'radio') {
                        field.checked = values[field.name] === field.value;
                    } else if (Object.hasOwn(values, field.name)) {
                        field.value = values[field.name];
                    }
                });
            }
        }
    } catch {
        status.textContent = @json(__('Could not load the saved local draft.'));
    }

    form.querySelector('[data-save-scorecard-draft]').addEventListener('click', () => {
        const values = {};
        fields.forEach((field) => {
            if (field.type === 'radio') {
                if (field.checked) values[field.name] = field.value;
            } else {
                values[field.name] = field.value;
            }
        });
        try {
            localStorage.setItem(key, JSON.stringify(values));
            status.textContent = @json(__('Draft saved on this device.'));
        } catch {
            status.textContent = @json(__('Could not save this draft in browser storage.'));
        }
    });
})();
</script>
@endsection

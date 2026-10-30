@extends('frontend.layouts.app')

@section('title', __('candidate.apply_form.page_title'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('candidate.apply_form.title') }}</h1>
    @if (filled(data_get($job ?? [], 'title')))
        <p class="mt-1 text-sm text-hb-muted">
            <span class="font-medium text-hb-navy">{{ data_get($job, 'title') }}</span>
            @php
                $company = data_get($job, 'company.name', data_get($job, 'company_name'));
            @endphp
            @if (filled($company))
                <span aria-hidden="true"> · </span>{{ $company }}
            @endif
        </p>
    @endif
@endsection

@section('content')
@php
    $cvs = \App\Support\Rows::from($cvs ?? []);
    $hasResumes = ! empty($cvs);
    $vacancyId = $vacancyId ?? data_get($job ?? [], 'id');
    $uploadHref = route('frontend.candidate.resume.upload', array_filter([
        'vacancy_id' => $vacancyId,
    ]));
    $cancelHref = filled($vacancyId)
        ? route('frontend.candidate.vacancies.show', ['vacancyId' => $vacancyId])
        : route('frontend.candidate.vacancies.index');
@endphp

<x-card class="max-w-2xl">
    <x-slot:header>
        <div>
            <h2 class="text-lg font-bold text-hb-navy">{{ __('candidate.apply_form.title') }}</h2>
            @if (filled(data_get($job ?? [], 'title')))
                <p class="mt-1 text-sm text-hb-muted">
                    {{ data_get($job, 'title') }}
                    @if (filled(data_get($job, 'company.name', data_get($job, 'company_name'))))
                        · {{ data_get($job, 'company.name', data_get($job, 'company_name')) }}
                    @endif
                </p>
            @endif
        </div>
    </x-slot:header>

    @if (! $hasResumes)
        <div class="rounded-xl border border-dashed border-hb-border bg-hb-page/40 px-5 py-8 text-center" role="status">
            <p class="text-sm font-semibold text-hb-navy">{{ __('candidate.apply_form.no_resume_title') }}</p>
            <p class="mt-2 text-sm text-hb-muted">{{ __('candidate.apply_form.no_resume_body') }}</p>
            <div class="mt-5 flex flex-wrap items-center justify-center gap-3">
                <x-button :href="$uploadHref">{{ __('candidate.apply_form.upload_resume') }}</x-button>
                <x-button variant="light" :href="$cancelHref">{{ __('candidate.apply_form.cancel') }}</x-button>
            </div>
        </div>
    @else
        <form
            method="POST"
            action="{{ route('frontend.candidate.vacancies.apply.submit', ['vacancyId' => $vacancyId]) }}"
            class="space-y-5"
            data-apply-form
        >
            @csrf
            <x-form.select name="cv_id" :label="__('candidate.apply_form.resume')" :required="true" :error="$errors->first('cv_id')">
                <option value="">{{ __('candidate.apply_form.select_resume') }}</option>
                @foreach ($cvs as $opt)
                    <option
                        value="{{ data_get($opt, 'id') }}"
                        @selected((string) old('cv_id') === (string) data_get($opt, 'id'))
                    >
                        {{ data_get($opt, 'original_filename', __('candidate.apply_form.resume')) }}
                    </option>
                @endforeach
            </x-form.select>

            <p class="text-xs text-hb-muted">
                {{ __('candidate.apply_form.need_another_resume') }}
                <a class="font-semibold text-hb-blue hover:underline" href="{{ $uploadHref }}">{{ __('candidate.apply_form.upload_resume') }}</a>
            </p>

            <x-form.textarea
                name="cover_note"
                :label="__('candidate.apply_form.cover_note')"
                :hint="__('candidate.apply_form.cover_note_hint')"
                rows="5"
                maxlength="3000"
                :error="$errors->first('cover_note')"
            >{{ old('cover_note') }}</x-form.textarea>

            <x-form.checkbox
                name="consent"
                :label="__('candidate.apply_form.consent')"
                value="1"
                :checked="(bool) (old('consent'))"
            />
            @error('consent')
                <p class="hb-field-error" role="alert">{{ $message }}</p>
            @enderror

            <div class="flex flex-wrap items-center gap-3 pt-1">
                <x-button type="submit" data-apply-submit>{{ __('candidate.apply_form.submit') }}</x-button>
                <x-button variant="light" :href="$cancelHref">{{ __('candidate.apply_form.cancel') }}</x-button>
            </div>
        </form>
    @endif
</x-card>
@endsection
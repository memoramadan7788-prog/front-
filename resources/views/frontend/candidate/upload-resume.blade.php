@extends('frontend.layouts.app')

@section('title', __('candidate.resume_upload.page_title'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('candidate.resume_upload.title') }}</h1>
    <p class="mt-1 text-sm text-hb-muted">
        @if (! empty($vacancyId))
            {{ __('candidate.resume_upload.context_apply') }}
        @else
            {{ __('candidate.resume_upload.context_profile') }}
        @endif
    </p>
@endsection

@section('content')
@php
    $maxBytes = (int) ($maxBytes ?? 10 * 1024 * 1024);
    $maxMb = (int) round($maxBytes / (1024 * 1024));
    $extensions = $allowedExtensions ?? ['pdf', 'doc', 'docx'];
    $accept = collect($extensions)->map(fn ($ext) => '.'.$ext)->implode(',');
    $cancelHref = ! empty($returnTo)
        ? $returnTo
        : route('frontend.candidate.profile');
@endphp

<x-card class="max-w-2xl">
    <x-slot:header>
        <h2 class="text-lg font-bold text-hb-navy">{{ __('candidate.resume_upload.title') }}</h2>
    </x-slot:header>

    <form
        method="POST"
        action="{{ route('frontend.candidate.resume.upload.submit') }}"
        enctype="multipart/form-data"
        class="space-y-5"
        data-resume-upload
        data-max-bytes="{{ $maxBytes }}"
        data-accept-ext="{{ implode(',', $extensions) }}"
        novalidate
    >
        @csrf
        @if (! empty($vacancyId))
            <input type="hidden" name="vacancy_id" value="{{ (int) $vacancyId }}">
        @endif
        @if (! empty($returnTo))
            <input type="hidden" name="return_to" value="{{ $returnTo }}">
        @endif

        <div class="w-full" data-resume-field>
            <label for="resume-file" class="hb-label">
                {{ __('candidate.resume_upload.file_label', ['max' => $maxMb]) }}
                <span class="text-hb-error" aria-hidden="true">*</span>
            </label>

            <div
                class="mt-2 rounded-xl border border-dashed border-hb-border bg-hb-page/40 px-4 py-6 transition-colors focus-within:border-hb-blue focus-within:ring-2 focus-within:ring-hb-blue/20"
                data-resume-dropzone
            >
                <input
                    id="resume-file"
                    type="file"
                    name="resume"
                    required
                    accept="{{ $accept }},application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                    class="sr-only"
                    data-resume-input
                    @if ($errors->first('resume')) aria-invalid="true" aria-describedby="resume-file-error" @endif
                >

                <div class="flex flex-col items-start gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-hb-navy" data-resume-filename>
                            {{ __('candidate.resume_upload.choose_file') }}
                        </p>
                        <p class="mt-1 text-xs text-hb-muted" data-resume-meta>
                            {{ __('candidate.resume_upload.formats', ['max' => $maxMb]) }}
                        </p>
                    </div>
                    <label for="resume-file" class="inline-flex min-h-11 cursor-pointer items-center justify-center rounded-full border border-hb-border bg-white px-4 py-2.5 text-sm font-semibold text-hb-navy hover:bg-hb-page">
                        {{ __('candidate.resume_upload.browse') }}
                    </label>
                </div>
            </div>

            <p class="mt-1.5 hidden text-xs text-hb-muted" data-resume-selected aria-live="polite"></p>
            <p id="resume-file-error" class="hb-field-error {{ $errors->first('resume') ? '' : 'hidden' }}" role="alert" data-resume-error>
                {{ $errors->first('resume') }}
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3 pt-1">
            <x-button type="submit" data-resume-submit>
                <span data-resume-submit-label>{{ __('candidate.resume_upload.submit') }}</span>
                <span class="hidden" data-resume-submit-loading>{{ __('candidate.resume_upload.uploading') }}</span>
            </x-button>
            <x-button variant="light" :href="$cancelHref">{{ __('candidate.resume_upload.cancel') }}</x-button>
        </div>
    </form>
</x-card>
@endsection

@push('scripts')
<script>
(function () {
    var form = document.querySelector('[data-resume-upload]');
    if (!form) return;

    var input = form.querySelector('[data-resume-input]');
    var filenameEl = form.querySelector('[data-resume-filename]');
    var metaEl = form.querySelector('[data-resume-meta]');
    var selectedEl = form.querySelector('[data-resume-selected]');
    var errorEl = form.querySelector('[data-resume-error]');
    var submitBtn = form.querySelector('[data-resume-submit]');
    var labelIdle = form.querySelector('[data-resume-submit-label]');
    var labelBusy = form.querySelector('[data-resume-submit-loading]');
    var maxBytes = parseInt(form.getAttribute('data-max-bytes') || '10485760', 10);
    var acceptExt = (form.getAttribute('data-accept-ext') || 'pdf,doc,docx')
        .split(',')
        .map(function (s) { return s.trim().toLowerCase(); })
        .filter(Boolean);
    var submitting = false;

    function t(key, replace) {
        return typeof window.__ === 'function' ? window.__(key, replace) : key;
    }

    function showError(message) {
        if (!errorEl) return;
        errorEl.textContent = message || '';
        errorEl.classList.toggle('hidden', !message);
        if (input) input.setAttribute('aria-invalid', message ? 'true' : 'false');
    }

    function formatSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
    }

    function extensionOf(name) {
        var parts = String(name || '').toLowerCase().split('.');
        return parts.length > 1 ? parts.pop() : '';
    }

    function validateFile(file) {
        if (!file) {
            return t('resume.required');
        }
        var ext = extensionOf(file.name);
        if (acceptExt.indexOf(ext) === -1) {
            return t('resume.invalid_type');
        }
        if (file.size > maxBytes) {
            return t('resume.too_large', { max: String(Math.round(maxBytes / (1024 * 1024))) });
        }
        return null;
    }

    function paintSelection(file) {
        if (!file) {
            if (filenameEl) filenameEl.textContent = t('resume.choose_file');
            if (selectedEl) {
                selectedEl.textContent = '';
                selectedEl.classList.add('hidden');
            }
            return;
        }
        if (filenameEl) filenameEl.textContent = file.name;
        if (selectedEl) {
            selectedEl.textContent = t('resume.selected', {
                name: file.name,
                size: formatSize(file.size),
            });
            selectedEl.classList.remove('hidden');
        }
        if (metaEl) metaEl.textContent = formatSize(file.size);
    }

    function setBusy(busy) {
        submitting = busy;
        if (submitBtn) {
            submitBtn.disabled = busy;
            submitBtn.setAttribute('aria-busy', busy ? 'true' : 'false');
            submitBtn.classList.toggle('opacity-50', busy);
            submitBtn.classList.toggle('cursor-not-allowed', busy);
        }
        if (labelIdle) labelIdle.classList.toggle('hidden', busy);
        if (labelBusy) labelBusy.classList.toggle('hidden', !busy);
        if (input) input.disabled = busy;
    }

    if (input) {
        input.addEventListener('change', function () {
            var file = input.files && input.files[0];
            var err = validateFile(file);
            paintSelection(file || null);
            showError(err);
        });
    }

    form.addEventListener('submit', function (event) {
        if (submitting) {
            event.preventDefault();
            return;
        }
        var file = input && input.files ? input.files[0] : null;
        var err = validateFile(file);
        if (err) {
            event.preventDefault();
            showError(err);
            return;
        }
        setBusy(true);
    });
})();
</script>
@endpush

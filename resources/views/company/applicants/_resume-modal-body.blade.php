<p class="text-sm text-hb-text" dir="ltr">
    {{ __('company.applicants.resume_preview_body', [
        'filename' => $applicant['resume']['filename'] ?? '',
        'size' => $applicant['resume']['size'] ?? '',
        'updated' => $applicant['resume']['updated'] ?? '',
    ]) }}
</p>
<p class="mt-2 text-xs text-hb-muted">{{ __('company.applicants.resume_preview_note') }}</p>

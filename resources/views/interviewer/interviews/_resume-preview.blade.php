<div class="space-y-4">
    <p class="text-sm text-hb-muted">{{ __('interviewer.prepare.resume_preview_note') }}</p>
    <div class="rounded-xl border border-hb-border bg-hb-page/50 p-4">
        <p class="text-base font-semibold text-hb-navy">{{ $applicant['name'] ?? $interview['applicant'] }}</p>
        <p class="mt-1 text-sm text-hb-muted">{{ $applicant['headline'] ?? $interview['job_title'] }}</p>
        @if (! empty($applicant['email']))
            <p class="mt-2 text-sm text-hb-text" dir="ltr">{{ $applicant['email'] }}</p>
        @endif
        @if (! empty($applicant['location']))
            <p class="text-sm text-hb-muted">{{ $applicant['location'] }}</p>
        @endif
        @if (! empty($applicant['years_experience']))
            <p class="mt-2 text-sm text-hb-text">{{ __('interviewer.prepare.years_experience', ['years' => $applicant['years_experience']]) }}</p>
        @endif
    </div>
    <ul class="space-y-3">
        @forelse (($applicant['experience'] ?? []) as $role)
            <li class="border-b border-hb-border pb-3 last:border-0 last:pb-0">
                <p class="font-semibold text-hb-navy">{{ $role['title'] }} · {{ $role['company'] }}</p>
                <p class="mt-0.5 text-xs text-hb-muted">{{ $role['dates'] }}</p>
                <p class="mt-1 text-sm text-hb-text">{{ $role['summary'] }}</p>
            </li>
        @empty
            <li class="text-sm text-hb-muted">{{ __('interviewer.prepare.experience_empty') }}</li>
        @endforelse
    </ul>
</div>

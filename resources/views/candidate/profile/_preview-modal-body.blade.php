<div class="space-y-3 text-sm">
    <p class="text-lg font-semibold text-hb-navy" data-profile-preview-name>{{ $profile['name'] }}</p>
    <p class="text-hb-muted" data-profile-preview-meta>
        {{ $profile['headline'] }} · {{ $profile['location_short'] ?? $profile['location'] }}
    </p>
    <p class="leading-relaxed text-hb-text" data-profile-preview-about>{{ $profile['summary'] }}</p>
    <p class="text-xs text-hb-muted">{{ __('candidate.profile.preview_local_note') }}</p>
</div>

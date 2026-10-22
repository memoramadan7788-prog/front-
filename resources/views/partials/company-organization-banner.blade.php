@php
    $organizationContext = $organizationContext ?? null;
    $banner = is_array($organizationContext) ? ($organizationContext['status_banner'] ?? null) : null;
    $organization = is_array($organizationContext) ? ($organizationContext['organization'] ?? []) : [];
    $capabilities = is_array($organizationContext) ? ($organizationContext['capabilities'] ?? []) : [];
@endphp

@if (is_array($banner))
    @php
        $variant = $banner['variant'] ?? 'warning';
    @endphp
    <x-alert :variant="$variant" class="mb-6" data-org-status-banner data-org-status="{{ $organization['status'] ?? '' }}">
        <p class="font-semibold text-hb-navy">{{ __($banner['title_key']) }}</p>
        <p class="mt-1 text-hb-text">{{ __($banner['body_key']) }}</p>
        @if (! empty($organization['rejection_reason']))
            <p class="mt-2 text-sm text-hb-muted">
                <span class="font-medium text-hb-text">{{ __('company.organization.rejection_reason_label') }}:</span>
                {{ $organization['rejection_reason'] }}
            </p>
        @endif
        <p class="mt-2 text-xs text-hb-muted">{{ __('company.organization.capabilities_note') }}</p>
        <ul class="mt-2 list-inside list-disc text-xs text-hb-muted">
            <li>{{ __('company.organization.cap_create_jobs') }}:
                {{ ! empty($capabilities['can_create_jobs']) ? __('company.organization.available') : __('company.organization.unavailable') }}
            </li>
            <li>{{ __('company.organization.cap_publish_jobs') }}:
                {{ ! empty($capabilities['can_publish_jobs']) ? __('company.organization.available') : __('company.organization.unavailable') }}
            </li>
            <li>{{ __('company.organization.cap_assign_roles') }}:
                {{ ! empty($capabilities['can_assign_roles']) ? __('company.organization.available') : __('company.organization.unavailable') }}
            </li>
        </ul>
        <p class="mt-2 text-xs text-hb-muted">
            <a href="{{ route('onboarding.company.status', ['status' => $organization['status'] ?? 'pending']) }}" class="font-semibold text-hb-blue hover:underline">
                {{ __('company.organization.view_review_status') }}
            </a>
            ·
            <span>{{ __('company.organization.demo_hint') }}</span>
        </p>
    </x-alert>
@endif

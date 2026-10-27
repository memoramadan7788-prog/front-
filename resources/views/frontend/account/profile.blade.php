@extends('frontend.layouts.app')

@section('title', __('common.profile_settings'))

@section('page_header')
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('common.profile_settings') }}</h1>
            <p class="mt-1 text-sm text-hb-muted">{{ __('Account details from the Backend. Editing is unavailable until an update API exists.') }}</p>
        </div>
        <x-button variant="light" :href="route('frontend.account.change-password')">{{ __('common.change_password') }}</x-button>
    </div>
@endsection

@section('content')
@php
    $user = is_array($user ?? null) ? $user : [];
    $roles = collect(data_get($user, 'roles', []));
    $permissions = collect(data_get($user, 'permissions', []))->filter()->values();
    $roleLabels = $roles->map(function ($role) {
        if (is_string($role)) {
            return $role;
        }
        if (is_array($role)) {
            return data_get($role, 'name');
        }

        return null;
    })->filter()->values();
@endphp

@if (! empty($loadError))
    <div class="mb-6 rounded-2xl border border-hb-error/30 bg-hb-error-soft px-5 py-4 text-sm text-hb-error" role="alert">
        {{ __('Could not refresh profile from the Backend.') }}
        <span class="opacity-80">{{ trim($loadError) }}</span>
    </div>
@endif

<div class="grid gap-4 lg:grid-cols-2">
    <x-card>
        <x-slot:header>
            <h2 class="text-base font-bold text-hb-navy">{{ __('Identity') }}</h2>
        </x-slot:header>
        <dl class="space-y-4 text-sm">
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-hb-muted">{{ __('Name') }}</dt>
                <dd class="mt-1 font-semibold text-hb-navy">{{ data_get($user, 'name') ?: '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-hb-muted">{{ __('Email') }}</dt>
                <dd class="mt-1 text-hb-text">{{ data_get($user, 'email') ?: '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-hb-muted">{{ __('Account type') }}</dt>
                <dd class="mt-1">
                    @php $type = (string) data_get($user, 'account_type', ''); @endphp
                    @if ($type !== '')
                        <x-badge variant="info" :dot="true">{{ ucfirst($type) }}</x-badge>
                    @else
                        —
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-hb-muted">{{ __('Status') }}</dt>
                <dd class="mt-1">
                    @php $status = data_get($user, 'account_status'); @endphp
                    @if ($status)
                        <x-badge :variant="\App\Support\Rows::badgeVariant($status)" :dot="true">{{ $status }}</x-badge>
                    @else
                        —
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-hb-muted">{{ __('Member since') }}</dt>
                <dd class="mt-1 text-hb-muted">{{ \App\Support\Rows::date(data_get($user, 'created_at'), 'Y-m-d') }}</dd>
            </div>
        </dl>
        @unless ($profileEditAvailable ?? false)
            <p class="mt-6 rounded-xl border border-hb-border bg-hb-page px-4 py-3 text-sm text-hb-muted">
                {{ __('Profile editing is not available. The Backend exposes read-only identity via GET /auth/me.') }}
            </p>
        @endunless
    </x-card>

    <x-card>
        <x-slot:header>
            <h2 class="text-base font-bold text-hb-navy">{{ __('Roles & permissions') }}</h2>
        </x-slot:header>
        <div class="space-y-4 text-sm">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-hb-muted">{{ __('Roles') }}</p>
                @if ($roleLabels->isEmpty())
                    <p class="mt-2 text-hb-muted">—</p>
                @else
                    <ul class="mt-2 flex flex-wrap gap-1.5">
                        @foreach ($roleLabels as $label)
                            <li><x-badge variant="neutral">{{ $label }}</x-badge></li>
                        @endforeach
                    </ul>
                @endif
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-hb-muted">{{ __('Permissions') }}</p>
                @if ($permissions->isEmpty())
                    <p class="mt-2 text-hb-muted">—</p>
                @else
                    <div class="mt-2 flex max-h-48 flex-wrap gap-1.5 overflow-y-auto">
                        @foreach ($permissions as $key)
                            <span class="inline-flex rounded-md bg-hb-page px-2 py-0.5 text-[11px] font-medium text-hb-navy">{{ $key }}</span>
                        @endforeach
                    </div>
                    <p class="mt-2 text-xs text-hb-muted">{{ __(':count permission(s)', ['count' => $permissions->count()]) }}</p>
                @endif
            </div>
        </div>
    </x-card>
</div>

<div class="mt-4">
    <x-card class="max-w-2xl">
        <x-slot:header>
            <h2 class="text-base font-bold text-hb-navy">{{ __('common.change_password') }}</h2>
        </x-slot:header>
        @if ($passwordChangeAvailable ?? false)
            <p class="mb-3 text-sm text-hb-muted">{{ __('common.password_change.hint_available') }}</p>
            <x-button :href="route('frontend.account.change-password')">{{ __('common.change_password') }}</x-button>
        @else
            <p class="text-sm text-hb-muted">
                {{ __('common.password_change.unavailable_role') }}
            </p>
            <div class="mt-4">
                <x-button variant="light" :href="route('frontend.account.change-password')">{{ __('Open password form') }}</x-button>
            </div>
        @endif
    </x-card>
</div>
@endsection

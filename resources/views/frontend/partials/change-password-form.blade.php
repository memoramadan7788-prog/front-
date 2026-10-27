@php
    // $action: form route name, $cancel: where "Cancel" goes
    $passwordChangeAvailable = $passwordChangeAvailable ?? false;
@endphp
<x-card class="max-w-xl">
    <x-slot:header>
        <h1 class="text-lg font-bold text-hb-navy">{{ __('common.change_password') }}</h1>
        <p class="mt-1 text-sm text-hb-muted">
            @if ($passwordChangeAvailable)
                {{ __('common.password_change.hint_available') }}
            @else
                {{ __('common.password_change.hint_unavailable') }}
            @endif
        </p>
    </x-slot:header>

    @unless ($passwordChangeAvailable)
        <div class="mb-5 rounded-xl border border-hb-pending/30 bg-hb-pending-soft px-4 py-3 text-sm text-hb-pending" role="status" data-password-change-unavailable>
            {{ __('common.password_change.unavailable_role') }}
        </div>
    @endunless

    <form
        method="POST"
        action="{{ route($action) }}"
        class="space-y-5"
        autocomplete="off"
        data-password-change-form
        data-password-change-available="{{ $passwordChangeAvailable ? '1' : '0' }}"
    >
        @csrf

        @foreach ([
            ['current_password', __('common.password_change.current'), 'current-password'],
            ['new_password', __('common.password_change.new'), 'new-password'],
            ['new_password_confirmation', __('common.password_change.confirm'), 'new-password'],
        ] as [$name, $label, $autocomplete])
            <div>
                <label for="{{ $name }}" class="mb-1.5 block text-sm font-semibold text-hb-text">{{ $label }}</label>
                <input
                    id="{{ $name }}"
                    name="{{ $name }}"
                    type="password"
                    autocomplete="{{ $autocomplete }}"
                    required
                    @if ($name === 'new_password') minlength="8" @endif
                    @error($name) aria-invalid="true" @enderror
                    class="min-h-11 w-full rounded-md border bg-hb-surface px-3 text-sm text-hb-text focus:border-hb-blue focus:outline-none focus:ring-2 focus:ring-hb-blue/30 {{ $errors->has($name) ? 'border-hb-error' : 'border-hb-border' }}"
                >
                @error($name)
                    <p class="mt-1.5 text-sm text-hb-error" role="alert">{{ $message }}</p>
                @enderror
            </div>
        @endforeach

        <div class="flex flex-wrap items-center gap-3 pt-1">
            <x-button type="submit" data-password-change-submit>
                <span data-password-change-idle>{{ __('common.password_change.submit') }}</span>
                <span class="hidden items-center gap-2" data-password-change-loading aria-hidden="true">
                    <span class="inline-block h-3.5 w-3.5 animate-spin rounded-full border-2 border-current border-r-transparent"></span>
                    {{ __('common.password_change.submitting') }}
                </span>
            </x-button>
            <x-button variant="light" :href="route($cancel)">{{ __('Cancel') }}</x-button>
        </div>
    </form>
</x-card>

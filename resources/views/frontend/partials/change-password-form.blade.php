@php
    // $action: form route name, $cancel: where "Cancel" goes
    $passwordChangeAvailable = $passwordChangeAvailable ?? false;
@endphp
<x-card class="max-w-xl">
    <x-slot:header>
        <h1 class="text-lg font-bold text-hb-navy">{{ __('common.change_password') }}</h1>
        <p class="mt-1 text-sm text-hb-muted">{{ __('Use at least 8 characters. You will stay signed in on this device.') }}</p>
    </x-slot:header>

    @unless ($passwordChangeAvailable)
        <div class="mb-5 rounded-xl border border-hb-pending/30 bg-hb-pending-soft px-4 py-3 text-sm text-hb-pending" role="status">
            {{ __('Authenticated password change is not connected to the Backend yet. Submitting this form will not change any credentials.') }}
        </div>
    @endunless

    <form method="POST" action="{{ route($action) }}" class="space-y-5" autocomplete="off">
        @csrf

        @foreach ([
            ['current_password', __('Current password'), 'current-password'],
            ['new_password', __('New password'), 'new-password'],
            ['new_password_confirmation', __('Confirm new password'), 'new-password'],
        ] as [$name, $label, $autocomplete])
            <div>
                <label for="{{ $name }}" class="mb-1.5 block text-sm font-semibold text-hb-text">{{ $label }}</label>
                <input
                    id="{{ $name }}"
                    name="{{ $name }}"
                    type="password"
                    autocomplete="{{ $autocomplete }}"
                    required
                    @error($name) aria-invalid="true" @enderror
                    class="min-h-11 w-full rounded-md border bg-hb-surface px-3 text-sm text-hb-text focus:border-hb-blue focus:outline-none focus:ring-2 focus:ring-hb-blue/30 {{ $errors->has($name) ? 'border-hb-error' : 'border-hb-border' }}"
                >
                @error($name)
                    <p class="mt-1.5 text-sm text-hb-error" role="alert">{{ $message }}</p>
                @enderror
            </div>
        @endforeach

        <div class="flex flex-wrap items-center gap-3 pt-1">
            <x-button type="submit">{{ __('Update password') }}</x-button>
            <x-button variant="light" :href="route($cancel)">{{ __('Cancel') }}</x-button>
        </div>
    </form>
</x-card>

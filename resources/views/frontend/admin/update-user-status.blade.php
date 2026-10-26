@extends('frontend.layouts.app')

@section('title', __('Change user status'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('Change user status') }}</h1>
@endsection

@section('content')
@php $current = strtolower((string) data_get($user ?? [], 'account_status', '')); @endphp
<x-card class="max-w-2xl">
    <x-slot:header>
        <h2 class="text-lg font-bold text-hb-navy">{{ data_get($user ?? [], 'name') ?: __('Change user status') }}</h2>
        @if (data_get($user ?? [], 'email'))
            <p class="mt-0.5 text-sm text-hb-muted">{{ data_get($user, 'email') }}</p>
        @endif
    </x-slot:header>

    <form method="POST" action="{{ route('frontend.admin.users.status.submit', ['userId' => $userId]) }}" class="space-y-5">
        @csrf
        @if ($current !== '')
            <p class="text-sm text-hb-muted">{{ __('Current status') }}: <x-badge :variant="\App\Support\Rows::badgeVariant($current)" :dot="true">{{ $current }}</x-badge></p>
        @endif
        <x-form.select name="account_status" :label="__('New status')" :required="true" :error="$errors->first('account_status')">
            <option value="">{{ __('Select…') }}</option>
            @foreach (['active' => __('Active'), 'deactivated' => __('Deactivated'), 'blocked' => __('Blocked')] as $v => $label)
                <option value="{{ $v }}" @selected(old('account_status', $current) === $v)>{{ $label }}</option>
            @endforeach
        </x-form.select>
        <x-form.textarea name="reason" :label="__('Reason (optional)')" rows="3" :error="$errors->first('reason')">{{ old('reason') }}</x-form.textarea>
        <p class="text-xs text-hb-muted">{{ __('Deactivating or blocking a user signs them out everywhere immediately.') }}</p>
        <div class="flex flex-wrap items-center gap-3 pt-1">
            <x-button type="submit">{{ __('Save') }}</x-button>
            <x-button variant="light" :href="route('frontend.admin.users.index')">{{ __('Cancel') }}</x-button>
        </div>
    </form>
</x-card>
@endsection

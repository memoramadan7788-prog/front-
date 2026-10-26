@php
    use App\Support\Frontend\ProfileAvatar;
    use App\Support\Frontend\TopbarAccount;

    $avatarName = (string) ($avatarName ?? '');
    $avatarUrl = ProfileAvatar::urlFromProfile($profile ?? null) ?? ($avatarUrl ?? null);
    $avatarUrl = TopbarAccount::safeAvatarUrl($avatarUrl);
    $initials = TopbarAccount::initials($avatarName !== '' ? $avatarName : __('common.account_fallback_name'));
    $uploadAvailable = ProfileAvatar::uploadAvailable();
    $maxMb = ProfileAvatar::maxMb();
    $accept = collect(ProfileAvatar::acceptedExtensions())->map(fn ($e) => '.'.$e)->implode(',');
    $acceptAttr = $accept.','.implode(',', ProfileAvatar::acceptedMimeTypes());
@endphp

<section
    class="rounded-xl border border-hb-border bg-hb-page/30 px-4 py-4 sm:px-5"
    data-profile-avatar
    data-upload-available="{{ $uploadAvailable ? '1' : '0' }}"
    data-max-bytes="{{ ProfileAvatar::maxBytes() }}"
    data-accept-ext="{{ implode(',', ProfileAvatar::acceptedExtensions()) }}"
    data-msg-invalid="{{ __('common.avatar.invalid_type') }}"
    data-msg-too-large="{{ __('common.avatar.too_large', ['max' => $maxMb]) }}"
    data-msg-preview-ready="{{ __('common.avatar.preview_ready') }}"
    data-msg-preview-cleared="{{ __('common.avatar.preview_cleared') }}"
>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
        <div class="relative shrink-0">
            <div
                class="flex h-20 w-20 items-center justify-center overflow-hidden rounded-full bg-hb-navy text-xl font-semibold text-white dark:bg-hb-blue dark:text-hb-page"
                data-avatar-preview-wrap
                aria-hidden="{{ filled($avatarUrl) ? 'false' : 'true' }}"
            >
                @if (filled($avatarUrl))
                    <img
                        src="{{ $avatarUrl }}"
                        alt=""
                        class="h-full w-full object-cover"
                        data-avatar-image
                        referrerpolicy="no-referrer"
                    >
                @else
                    <span data-avatar-initials>{{ $initials }}</span>
                    <img src="" alt="" class="hidden h-full w-full object-cover" data-avatar-image referrerpolicy="no-referrer">
                @endif
            </div>
        </div>

        <div class="min-w-0 flex-1">
            <h3 class="text-sm font-semibold text-hb-navy">{{ __('common.avatar.title') }}</h3>
            @if ($uploadAvailable)
                <p class="mt-1 text-xs text-hb-muted">{{ __('common.avatar.supported_hint', ['max' => $maxMb]) }}</p>
            @else
                <p class="mt-1 text-xs text-hb-muted" data-avatar-unavailable>
                    {{ __('common.avatar.unavailable_body', ['max' => $maxMb]) }}
                </p>
            @endif

            <div class="mt-3 flex flex-wrap items-center gap-2">
                <label class="inline-flex cursor-pointer items-center justify-center gap-2 rounded-full border border-hb-border bg-white px-3 py-1.5 text-xs font-semibold text-hb-navy hover:bg-hb-page {{ $uploadAvailable ? '' : 'opacity-70' }}">
                    <span>{{ __('common.avatar.choose') }}</span>
                    <input
                        type="file"
                        class="sr-only"
                        accept="{{ $acceptAttr }}"
                        data-avatar-input
                        @if (! $uploadAvailable) data-preview-only="1" @endif
                    >
                </label>
                <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-full border border-hb-border bg-white px-3 py-1.5 text-xs font-semibold text-hb-navy hover:bg-hb-page disabled:cursor-not-allowed disabled:opacity-50"
                    data-avatar-clear
                    disabled
                >
                    {{ __('common.avatar.clear_preview') }}
                </button>
                @if ($uploadAvailable)
                    <button
                        type="button"
                        class="inline-flex items-center justify-center rounded-full bg-hb-accent px-3 py-1.5 text-xs font-semibold text-white hover:bg-hb-accent-dark disabled:cursor-not-allowed disabled:opacity-50"
                        data-avatar-upload
                        disabled
                    >
                        {{ __('common.avatar.upload') }}
                    </button>
                    <button
                        type="button"
                        class="inline-flex items-center justify-center rounded-full border border-hb-error/40 px-3 py-1.5 text-xs font-semibold text-hb-error hover:bg-hb-error/5 disabled:cursor-not-allowed disabled:opacity-50"
                        data-avatar-remove
                        @disabled(! filled($avatarUrl))
                    >
                        {{ __('common.avatar.remove') }}
                    </button>
                @else
                    <span class="text-[11px] font-medium uppercase tracking-wide text-hb-muted">{{ __('common.avatar.preview_only_badge') }}</span>
                @endif
            </div>
            <p class="mt-2 hidden text-xs text-hb-error" role="alert" data-avatar-error></p>
            <p class="mt-2 hidden text-xs text-hb-muted" role="status" data-avatar-status></p>
        </div>
    </div>
</section>

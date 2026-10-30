@extends('frontend.layouts.app')

@section('title', __('candidate.preferences.title'))

@section('page_header')
    <div>
        <p class="text-xs font-medium uppercase tracking-wide text-hb-muted">{{ __('candidate.preferences.breadcrumb') }}</p>
        <h1 class="mt-1 text-2xl font-semibold tracking-tight text-hb-navy">{{ __('candidate.preferences.title') }}</h1>
        <p class="mt-1 max-w-2xl text-sm text-hb-muted">{{ __('candidate.preferences.intro') }}</p>
    </div>
@endsection

@section('content')
@php
    $prefs = is_array($preferences ?? null) ? $preferences : [];
    $notifs = is_array($notifications ?? null) ? $notifications : [];
    $priv = is_array($privacy ?? null) ? $privacy : [];
    $searches = is_array($savedSearches ?? null) ? $savedSearches : [];
    $empTypes = is_array($employmentTypes ?? null) ? $employmentTypes : [];
    $models = is_array($workModels ?? null) ? $workModels : [];
    $visOpts = is_array($visibilityOptions ?? null) ? $visibilityOptions : [];
    $prefsLoaded = (bool) ($preferencesLoaded ?? false);
    $notifsLoaded = (bool) ($notificationsLoaded ?? false);
    $privacyLoaded = (bool) ($privacyLoaded ?? false);
    $searchesLoaded = (bool) ($savedSearchesLoaded ?? false);
@endphp

<div class="space-y-6" data-preferences-page>
    {{-- Job preferences --}}
    <section id="preferences" class="hb-surface rounded-2xl p-5 sm:p-6" aria-labelledby="prefs-job-heading" data-prefs-section="job" data-prefs-loaded="{{ $prefsLoaded ? '1' : '0' }}">
        <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 id="prefs-job-heading" class="text-lg font-semibold text-hb-navy">{{ __('candidate.preferences.next_role') }}</h2>
                <p class="mt-1 text-sm text-hb-muted">{{ __('candidate.preferences.job_section_intro') }}</p>
            </div>
        </div>

        @if (! $prefsLoaded)
            <div class="rounded-xl border border-hb-error/30 bg-hb-error/5 px-4 py-3 text-sm text-hb-navy" role="alert" data-prefs-error="job">
                <p class="font-medium">{{ __('candidate.preferences.section_unavailable_title') }}</p>
                <p class="mt-1">{{ $preferencesError ?: __('candidate.preferences.server_error') }}</p>
                <p class="mt-2 text-xs text-hb-muted">{{ __('candidate.preferences.section_unavailable_hint') }}</p>
                <div class="mt-3">
                    <x-button variant="light" size="sm" :href="route('frontend.candidate.preferences').'#preferences'" data-prefs-retry="job">
                        {{ __('candidate.preferences.retry') }}
                    </x-button>
                </div>
            </div>
        @else
            <form method="POST" action="{{ route('frontend.candidate.preferences.job') }}" class="grid gap-4 sm:grid-cols-2" data-prefs-form="job" data-submit-once>
                @csrf
                <div class="sm:col-span-2">
                    <label class="hb-label" for="desired_titles">{{ __('candidate.preferences.desired_titles') }}</label>
                    <input id="desired_titles" name="desired_titles" type="text" class="hb-input" value="{{ old('desired_titles', $prefs['desired_titles_text'] ?? '') }}" placeholder="{{ __('candidate.preferences.desired_titles_hint') }}" autocomplete="off">
                    <p class="mt-1 text-xs text-hb-muted">{{ __('candidate.preferences.desired_titles_help') }}</p>
                    @error('desired_titles')<p class="hb-field-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="hb-label" for="location">{{ __('candidate.preferences.location') }}</label>
                    <input id="location" name="location" type="text" class="hb-input" value="{{ old('location', $prefs['location'] ?? '') }}" autocomplete="address-level2">
                    @error('location')<p class="hb-field-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="hb-label" for="employment_type">{{ __('candidate.preferences.employment_type') }}</label>
                    <select id="employment_type" name="employment_type" class="hb-input">
                        <option value="">{{ __('candidate.preferences.any_option') }}</option>
                        @foreach ($empTypes as $type)
                            <option value="{{ $type }}" @selected(old('employment_type', $prefs['employment_type'] ?? '') === $type)>{{ __('candidate.preferences.employment.'.$type) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="hb-label" for="work_model">{{ __('candidate.preferences.work_model') }}</label>
                    <select id="work_model" name="work_model" class="hb-input">
                        <option value="">{{ __('candidate.preferences.any_option') }}</option>
                        @foreach ($models as $model)
                            <option value="{{ $model }}" @selected(old('work_model', $prefs['work_model'] ?? '') === $model)>{{ __('candidate.preferences.work.'.$model) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="hb-label" for="min_salary">{{ __('candidate.preferences.minimum_salary') }}</label>
                    <input id="min_salary" name="min_salary" type="number" min="0" step="1" class="hb-input" value="{{ old('min_salary', $prefs['min_salary'] ?? '') }}">
                    @error('min_salary')<p class="hb-field-error">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2 flex flex-col gap-3">
                    <label class="inline-flex items-start gap-3 text-sm text-hb-text">
                        <input type="hidden" name="open_to_relocation" value="0">
                        <input class="hb-checkbox mt-0.5" type="checkbox" name="open_to_relocation" value="1" @checked(old('open_to_relocation', $prefs['open_to_relocation'] ?? false))>
                        <span>{{ __('candidate.preferences.willing_relocate') }}</span>
                    </label>
                    <label class="inline-flex items-start gap-3 text-sm text-hb-text">
                        <input type="hidden" name="include_agency" value="0">
                        <input class="hb-checkbox mt-0.5" type="checkbox" name="include_agency" value="1" @checked(old('include_agency', $prefs['include_agency'] ?? false))>
                        <span>{{ __('candidate.preferences.include_agency') }}</span>
                    </label>
                </div>
                <div class="sm:col-span-2">
                    <x-button type="submit" data-prefs-submit>{{ __('candidate.preferences.save_job') }}</x-button>
                </div>
            </form>
        @endif
    </section>

    {{-- Notifications --}}
    <section id="notifications" class="hb-surface rounded-2xl p-5 sm:p-6" aria-labelledby="prefs-notif-heading" data-prefs-section="notifications" data-prefs-loaded="{{ $notifsLoaded ? '1' : '0' }}">
        <h2 id="prefs-notif-heading" class="text-lg font-semibold text-hb-navy">{{ __('candidate.preferences.notifications_heading') }}</h2>
        <p class="mt-1 text-sm text-hb-muted">{{ __('candidate.preferences.security_emails') }}</p>

        @if (! $notifsLoaded)
            <div class="mt-4 rounded-xl border border-hb-error/30 bg-hb-error/5 px-4 py-3 text-sm text-hb-navy" role="alert" data-prefs-error="notifications">
                <p class="font-medium">{{ __('candidate.preferences.section_unavailable_title') }}</p>
                <p class="mt-1">{{ $notificationsError ?: __('candidate.preferences.server_error') }}</p>
                <p class="mt-2 text-xs text-hb-muted">{{ __('candidate.preferences.section_unavailable_hint') }}</p>
                <div class="mt-3">
                    <x-button variant="light" size="sm" :href="route('frontend.candidate.preferences').'#notifications'" data-prefs-retry="notifications">
                        {{ __('candidate.preferences.retry') }}
                    </x-button>
                </div>
            </div>
        @else
            <form method="POST" action="{{ route('frontend.candidate.preferences.notifications') }}" class="mt-4 space-y-3" data-prefs-form="notifications" data-submit-once>
                @csrf
                @foreach ([
                    'weekly_digest' => 'notif_job_digest',
                    'status_updates' => 'notif_app_status',
                    'interview_reminders' => 'notif_interview_reminders',
                    'messages' => 'notif_hiring_messages',
                    'marketing' => 'notif_marketing',
                ] as $field => $labelKey)
                    <label class="flex items-start justify-between gap-4 rounded-xl border border-hb-border bg-hb-page/40 px-4 py-3">
                        <span class="text-sm text-hb-navy">{{ __('candidate.preferences.'.$labelKey) }}</span>
                        <span class="inline-flex items-center gap-2">
                            <input type="hidden" name="{{ $field }}" value="0">
                            <input class="hb-checkbox" type="checkbox" name="{{ $field }}" value="1" @checked(old($field, $notifs[$field] ?? false)) aria-label="{{ __('candidate.preferences.'.$labelKey) }}">
                        </span>
                    </label>
                @endforeach
                <x-button type="submit" data-prefs-submit>{{ __('candidate.preferences.save_notifications') }}</x-button>
            </form>
        @endif
    </section>

    {{-- Privacy --}}
    <section id="privacy" class="hb-surface rounded-2xl p-5 sm:p-6" aria-labelledby="prefs-privacy-heading" data-prefs-section="privacy" data-prefs-loaded="{{ $privacyLoaded ? '1' : '0' }}">
        <h2 id="prefs-privacy-heading" class="text-lg font-semibold text-hb-navy">{{ __('candidate.preferences.privacy_controls') }}</h2>
        <p class="mt-1 text-sm text-hb-muted">{{ __('candidate.preferences.privacy_note') }}</p>

        @if (! $privacyLoaded)
            <div class="mt-4 rounded-xl border border-hb-error/30 bg-hb-error/5 px-4 py-3 text-sm text-hb-navy" role="alert" data-prefs-error="privacy">
                <p class="font-medium">{{ __('candidate.preferences.section_unavailable_title') }}</p>
                <p class="mt-1">{{ $privacyError ?: __('candidate.preferences.server_error') }}</p>
                <p class="mt-2 text-xs text-hb-muted">{{ __('candidate.preferences.section_unavailable_hint') }}</p>
                <div class="mt-3">
                    <x-button variant="light" size="sm" :href="route('frontend.candidate.preferences').'#privacy'" data-prefs-retry="privacy">
                        {{ __('candidate.preferences.retry') }}
                    </x-button>
                </div>
            </div>
        @else
            <form method="POST" action="{{ route('frontend.candidate.preferences.privacy') }}" class="mt-4 space-y-4" data-prefs-form="privacy" data-submit-once>
                @csrf
                <div>
                    <label class="hb-label" for="visibility">{{ __('candidate.preferences.visibility_label') }}</label>
                    <select id="visibility" name="visibility" class="hb-input" required>
                        @foreach ($visOpts as $opt)
                            <option value="{{ $opt }}" @selected(old('visibility', $priv['visibility'] ?? 'employers_only') === $opt)>{{ __('candidate.preferences.visibility.'.$opt) }}</option>
                        @endforeach
                    </select>
                    @error('visibility')<p class="hb-field-error">{{ $message }}</p>@enderror
                </div>
                <label class="flex items-start justify-between gap-4 rounded-xl border border-hb-border bg-hb-page/40 px-4 py-3">
                    <span class="text-sm text-hb-navy">{{ __('candidate.preferences.privacy_agency') }}</span>
                    <span>
                        <input type="hidden" name="allow_agency_recruiters" value="0">
                        <input class="hb-checkbox" type="checkbox" name="allow_agency_recruiters" value="1" @checked(old('allow_agency_recruiters', $priv['allow_agency_recruiters'] ?? false))>
                    </span>
                </label>
                <label class="flex items-start justify-between gap-4 rounded-xl border border-hb-border bg-hb-page/40 px-4 py-3">
                    <span class="text-sm text-hb-navy">{{ __('candidate.preferences.privacy_public_search') }}</span>
                    <span>
                        <input type="hidden" name="show_in_public_search" value="0">
                        <input class="hb-checkbox" type="checkbox" name="show_in_public_search" value="1" @checked(old('show_in_public_search', $priv['show_in_public_search'] ?? false))>
                    </span>
                </label>
                <p class="text-xs text-hb-muted">{{ __('candidate.preferences.privacy_unsupported_note') }}</p>
                <x-button type="submit" data-prefs-submit>{{ __('candidate.preferences.save_privacy') }}</x-button>
            </form>
        @endif
    </section>

    {{-- Saved searches --}}
    <section id="saved-searches" class="hb-surface rounded-2xl p-5 sm:p-6" aria-labelledby="prefs-searches-heading" data-prefs-section="saved-searches" data-prefs-loaded="{{ $searchesLoaded ? '1' : '0' }}">
        <h2 id="prefs-searches-heading" class="text-lg font-semibold text-hb-navy">{{ __('candidate.preferences.manage_saved_searches') }}</h2>
        <p class="mt-1 text-sm text-hb-muted">{{ __('candidate.preferences.saved_searches_intro') }}</p>
        <p class="mt-2 rounded-lg border border-hb-pending/30 bg-hb-pending-soft/30 px-3 py-2 text-xs text-hb-navy" role="note">
            {{ __('candidate.preferences.alerts_delivery_note') }}
        </p>

        @if (! $searchesLoaded)
            <div class="mt-4 rounded-xl border border-hb-error/30 bg-hb-error/5 px-4 py-3 text-sm text-hb-navy" role="alert" data-prefs-error="saved-searches">
                <p class="font-medium">{{ __('candidate.preferences.section_unavailable_title') }}</p>
                <p class="mt-1">{{ $savedSearchesError ?: __('candidate.preferences.server_error') }}</p>
                <p class="mt-2 text-xs text-hb-muted">{{ __('candidate.preferences.section_unavailable_hint') }}</p>
                <div class="mt-3">
                    <x-button variant="light" size="sm" :href="route('frontend.candidate.preferences').'#saved-searches'" data-prefs-retry="saved-searches">
                        {{ __('candidate.preferences.retry') }}
                    </x-button>
                </div>
            </div>
        @else
            @if ($searches === [])
                <div class="mt-4 rounded-xl border border-dashed border-hb-border px-4 py-8 text-center" role="status" data-saved-searches-empty>
                    <p class="text-sm font-semibold text-hb-navy">{{ __('candidate.preferences.saved_searches_empty_title') }}</p>
                    <p class="mt-1 text-sm text-hb-muted">{{ __('candidate.preferences.saved_searches_empty_body') }}</p>
                    <div class="mt-4">
                        <x-button variant="light" size="sm" :href="route('frontend.candidate.vacancies.index')">{{ __('candidate.preferences.browse_jobs_cta') }}</x-button>
                    </div>
                </div>
            @else
                <ul class="mt-4 space-y-3" data-saved-searches-list>
                    @foreach ($searches as $search)
                        <li class="rounded-xl border border-hb-border bg-hb-page/30 p-4" data-saved-search-id="{{ $search['id'] }}">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <h3 class="font-semibold text-hb-navy">{{ $search['name'] }}</h3>
                                    <p class="mt-1 text-xs text-hb-muted">
                                        @foreach ($search['filters'] as $fkey => $fval)
                                            <span class="me-2 inline-block">{{ $fkey }}: {{ is_array($fval) ? implode(', ', $fval) : $fval }}</span>
                                        @endforeach
                                    </p>
                                    <p class="mt-1 text-xs text-hb-muted">
                                        {{ $search['weekly_alerts'] ? __('candidate.preferences.alerts_on') : __('candidate.preferences.alerts_off') }}
                                        ·
                                        {{ $search['is_paused'] ? __('candidate.preferences.alerts_paused') : __('candidate.preferences.alerts_active') }}
                                    </p>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <form method="POST" action="{{ route('frontend.candidate.preferences.saved-searches.update', ['searchId' => $search['id']]) }}" data-submit-once>
                                        @csrf
                                        <input type="hidden" name="weekly_alerts" value="{{ $search['weekly_alerts'] ? '0' : '1' }}">
                                        <x-button type="submit" size="sm" variant="light">{{ $search['weekly_alerts'] ? __('candidate.preferences.disable_alerts') : __('candidate.preferences.enable_alerts') }}</x-button>
                                    </form>
                                    <form method="POST" action="{{ route('frontend.candidate.preferences.saved-searches.update', ['searchId' => $search['id']]) }}" data-submit-once>
                                        @csrf
                                        <input type="hidden" name="is_paused" value="{{ $search['is_paused'] ? '0' : '1' }}">
                                        <x-button type="submit" size="sm" variant="light">{{ $search['is_paused'] ? __('candidate.preferences.resume_alerts') : __('candidate.preferences.pause_alerts') }}</x-button>
                                    </form>
                                    <form method="POST" action="{{ route('frontend.candidate.preferences.saved-searches.destroy', ['searchId' => $search['id']]) }}" data-submit-once onsubmit="return confirm(@js(__('candidate.preferences.delete_search_confirm')))">
                                        @csrf
                                        <x-button type="submit" size="sm" variant="destructive">{{ __('candidate.preferences.delete_search') }}</x-button>
                                    </form>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif

            <form method="POST" action="{{ route('frontend.candidate.preferences.saved-searches.store') }}" class="mt-6 grid gap-3 border-t border-hb-border pt-5 sm:grid-cols-2" data-prefs-form="saved-search-create" data-submit-once>
                @csrf
                <div class="sm:col-span-2">
                    <h3 class="text-sm font-semibold text-hb-navy">{{ __('candidate.preferences.create_saved_search') }}</h3>
                </div>
                <div class="sm:col-span-2">
                    <label class="hb-label" for="search_name">{{ __('candidate.preferences.search_name') }}</label>
                    <input id="search_name" name="name" type="text" class="hb-input" value="{{ old('name') }}" required maxlength="255">
                    @error('name')<p class="hb-field-error">{{ $message }}</p>@enderror
                    @error('filters')<p class="hb-field-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="hb-label" for="search_q">{{ __('candidate.preferences.search_keyword') }}</label>
                    <input id="search_q" name="search_q" type="search" class="hb-input" value="{{ old('search_q') }}">
                </div>
                <div>
                    <label class="hb-label" for="search_location">{{ __('candidate.preferences.location') }}</label>
                    <input id="search_location" name="search_location" type="text" class="hb-input" value="{{ old('search_location') }}">
                </div>
                <div>
                    <label class="hb-label" for="search_employment_type">{{ __('candidate.preferences.employment_type') }}</label>
                    <select id="search_employment_type" name="search_employment_type" class="hb-input">
                        <option value="">{{ __('candidate.preferences.any_option') }}</option>
                        @foreach ($empTypes as $type)
                            <option value="{{ $type }}" @selected(old('search_employment_type') === $type)>{{ __('candidate.preferences.employment.'.$type) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="hb-label" for="search_work_mode">{{ __('candidate.preferences.work_model') }}</label>
                    <select id="search_work_mode" name="search_work_mode" class="hb-input">
                        <option value="">{{ __('candidate.preferences.any_option') }}</option>
                        @foreach ($models as $model)
                            <option value="{{ $model }}" @selected(old('search_work_mode') === $model)>{{ __('candidate.preferences.work.'.$model) }}</option>
                        @endforeach
                    </select>
                </div>
                <label class="sm:col-span-2 inline-flex items-center gap-3 text-sm text-hb-text">
                    <input type="hidden" name="weekly_alerts" value="0">
                    <input class="hb-checkbox" type="checkbox" name="weekly_alerts" value="1" @checked(old('weekly_alerts', true))>
                    <span>{{ __('candidate.preferences.weekly_alerts_label') }}</span>
                </label>
                <div class="sm:col-span-2">
                    <x-button type="submit" data-prefs-submit>{{ __('candidate.preferences.create_search') }}</x-button>
                </div>
            </form>
        @endif
    </section>
</div>
@endsection

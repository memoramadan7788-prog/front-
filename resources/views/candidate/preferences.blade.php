@extends('layouts.app')

@section('title', $pageTitle ?? 'Job preferences & alerts · Hirebridge')
@section('breadcrumb', '23 / My workspace / Preferences & alerts')

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">Job preferences &amp; alerts</h1>
    <p class="mt-1 text-sm text-hb-muted">Shape your recommendations. Choose what reaches your inbox.</p>
@endsection

@section('content')
    @php $prefs = $preferences; @endphp

    <div class="hb-mock-banner mb-4 rounded-lg text-start">
        Frontend UI only — preference saves stay in this browser. They are not account settings.
    </div>

    <div
        class="mb-4 hidden rounded-lg border border-hb-success/30 bg-hb-success-soft px-4 py-3 text-sm text-hb-success"
        data-prefs-saved-banner
        role="status"
        hidden
    >
        {{ $prefs['saved_banner'] }}
        <span class="mt-1 block text-xs font-medium">Browser-local preview — not synced to Hirebridge.</span>
    </div>

    <form
        class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_300px]"
        data-candidate-preferences
        data-storage-key="hb_candidate_preferences_v1"
        novalidate
    >
        <div class="space-y-6">
            <section class="hb-surface p-5 sm:p-6" aria-labelledby="next-role-heading">
                <h2 id="next-role-heading" class="text-lg font-semibold text-hb-navy">Your next role</h2>

                <div class="mt-5">
                    <label class="hb-label" for="pref-titles">Desired titles</label>
                    <input
                        id="pref-titles"
                        name="titles"
                        type="text"
                        class="hb-input"
                        value="{{ $prefs['titles'] }}"
                        data-pref-field="titles"
                        required
                    >
                    <p class="hb-field-error hidden" data-error-for="titles" role="alert"></p>
                </div>

                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="hb-label" for="pref-location">Location</label>
                        <input
                            id="pref-location"
                            name="location"
                            type="text"
                            class="hb-input"
                            value="{{ $prefs['location'] }}"
                            data-pref-field="location"
                            required
                        >
                        <p class="hb-field-error hidden" data-error-for="location" role="alert"></p>
                    </div>
                    <div>
                        <label class="hb-label" for="pref-employment">Employment type</label>
                        <select id="pref-employment" name="employment" class="hb-input" data-pref-field="employment">
                            @foreach ($prefs['employment_options'] as $option)
                                <option value="{{ $option }}" @selected($option === $prefs['employment'])>{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="hb-label" for="pref-salary">Minimum base salary · USD</label>
                        <input
                            id="pref-salary"
                            name="salary"
                            type="text"
                            class="hb-input"
                            value="{{ $prefs['salary'] }}"
                            data-pref-field="salary"
                            required
                        >
                        <p class="hb-field-error hidden" data-error-for="salary" role="alert"></p>
                    </div>
                    <div>
                        <label class="hb-label" for="pref-work-model">Preferred work model</label>
                        <select id="pref-work-model" name="work_model" class="hb-input" data-pref-field="work_model">
                            @foreach ($prefs['work_model_options'] as $option)
                                <option value="{{ $option }}" @selected($option === $prefs['work_model'])>{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-5 space-y-3">
                    <label class="flex items-start gap-3 text-sm text-hb-text">
                        <input type="checkbox" class="hb-checkbox mt-0.5" name="include_agency" value="1" data-pref-field="include_agency" @checked($prefs['include_agency'])>
                        <span>Include agency-managed opportunities</span>
                    </label>
                    <label class="flex items-start gap-3 text-sm text-hb-text">
                        <input type="checkbox" class="hb-checkbox mt-0.5" name="willing_relocate" value="1" data-pref-field="willing_relocate" @checked($prefs['willing_relocate'])>
                        <span>Willing to relocate</span>
                    </label>
                </div>
            </section>

            <section class="hb-surface p-5 sm:p-6" aria-labelledby="notif-prefs-heading">
                <h2 id="notif-prefs-heading" class="text-lg font-semibold text-hb-navy">Notifications</h2>
                <div class="mt-4 space-y-3">
                    @foreach ($prefs['notifications'] as $item)
                        <label class="flex items-start gap-3 text-sm text-hb-text">
                            <input
                                type="checkbox"
                                class="hb-checkbox mt-0.5"
                                name="{{ $item['name'] }}"
                                value="1"
                                data-pref-field="{{ $item['name'] }}"
                                @checked($item['checked'])
                            >
                            <span>{{ $item['label'] }}</span>
                        </label>
                    @endforeach
                </div>
                <p class="mt-4 text-xs text-hb-muted">Security and account-access emails are always sent.</p>
            </section>

            <div class="flex flex-wrap items-center gap-3">
                <button type="submit" class="hb-btn-primary" data-prefs-save>Save preferences</button>
                <button type="button" class="hb-btn-outline" data-prefs-cancel>Cancel changes</button>
                <p class="hidden text-sm text-hb-pending" data-prefs-dirty role="status" hidden>Unsaved changes</p>
            </div>
            <p class="hidden text-sm text-hb-error" data-prefs-form-error role="alert"></p>
        </div>

        <aside class="space-y-4">
            <section class="hb-surface p-5" aria-labelledby="privacy-heading">
                <h2 id="privacy-heading" class="text-base font-semibold text-hb-navy">Privacy controls</h2>
                <div class="mt-4 space-y-3">
                    @foreach ($prefs['privacy'] as $item)
                        <label class="flex items-start gap-3 text-sm text-hb-text">
                            <input
                                type="checkbox"
                                class="hb-checkbox mt-0.5"
                                name="{{ $item['name'] }}"
                                value="1"
                                data-pref-field="{{ $item['name'] }}"
                                @checked($item['checked'])
                            >
                            <span>{{ $item['label'] }}</span>
                        </label>
                    @endforeach
                </div>
                <p class="mt-4 text-xs leading-relaxed text-hb-muted">{{ $prefs['privacy_note'] }}</p>
            </section>

            <section class="hb-surface p-5" aria-labelledby="saved-search-heading">
                <h2 id="saved-search-heading" class="text-base font-semibold text-hb-navy">Saved-search alert</h2>
                <p class="mt-3 text-sm font-medium text-hb-navy">{{ $prefs['saved_search']['title'] }}</p>
                <p class="mt-1 text-sm text-hb-muted">{{ $prefs['saved_search']['schedule'] }}</p>
                <p class="mt-1 text-sm text-hb-muted">Sent to {{ $prefs['saved_search']['email'] }}</p>
                <button type="button" class="hb-btn-outline mt-4 w-full text-hb-blue" disabled title="Saved searches — Backend Team">
                    Manage saved searches
                </button>
            </section>
        </aside>
    </form>

    <p class="mt-8 text-xs text-hb-muted">{{ $prefs['footer'] }}</p>
@endsection

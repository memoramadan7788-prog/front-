@extends('frontend.layouts.app')

@section('title', __('company.interviews.schedule_page_title'))

@section('page_header')
    <div>
        <p class="text-xs font-medium uppercase tracking-wide text-hb-muted">{{ __('company.interviews.schedule_breadcrumb') }}</p>
        <h1 class="mt-1 text-2xl font-semibold tracking-tight text-hb-navy">{{ __('company.interviews.schedule_title') }}</h1>
        @if (($mode ?? 'empty') === 'form')
            <p class="mt-1 text-sm text-hb-muted">
                {{ data_get($application, 'candidate.user.name')
                    ?? data_get($application, 'candidate.name')
                    ?? data_get($application, 'candidate_name')
                    ?? __('company.interviews.candidate_label') }}
                · {{ data_get($application, 'job.title') ?? data_get($application, 'job_title') ?? '—' }}
                · {{ data_get($application, 'status') ?? '—' }}
            </p>
        @endif
    </div>
@endsection

@section('content')
@php
    $fields = is_array($fields ?? null) ? $fields : [];
    $val = fn (string $key, $default = '') => old($key, $fields[$key] ?? $default);
    $tz = (string) $val('display_timezone', 'Africa/Cairo');
    $selectedSlot = $selectedSlot ?? null;
    $conflict = is_array($conflict ?? null) ? $conflict : ['state' => 'unverified', 'label' => '', 'has_conflict' => null];
@endphp

<div class="mx-auto max-w-3xl space-y-6" data-schedule-interview data-mode="{{ $mode ?? 'empty' }}">
    @if ($errors->any())
        <div class="rounded-xl border border-hb-error/30 bg-hb-error/5 px-4 py-3 text-sm text-hb-navy" role="alert">
            <p class="font-medium">{{ __('company.interviews.form_error_title') }}</p>
            <ul class="mt-2 list-disc ps-5">
                @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (($mode ?? 'empty') !== 'form')
        <div class="hb-surface rounded-2xl p-5 sm:p-6" @if (! empty($unauthorized)) data-unauthorized="1" @endif>
            <p class="text-sm text-hb-navy">{{ $emptyMessage }}</p>
            <p class="mt-3 text-xs text-hb-muted">{{ __('company.interviews.schedule_entry_hint') }}</p>
            <div class="mt-4 flex flex-wrap gap-2">
                <x-button variant="light" :href="route('frontend.company.vacancies.index')">{{ __('company.interviews.back_to_jobs') }}</x-button>
                <x-button variant="light" :href="route('frontend.company.interview-calendar')">{{ __('company.interviews.back_to_calendar') }}</x-button>
            </div>
        </div>
    @else
        <form method="POST" action="{{ route('frontend.company.interviews.schedule.submit') }}" class="hb-surface space-y-5 rounded-2xl p-5 sm:p-6" data-submit-once data-schedule-form>
            @csrf
            <input type="hidden" name="application_id" value="{{ $applicationId }}">

            <section class="space-y-3" aria-labelledby="context-heading">
                <h2 id="context-heading" class="text-lg font-semibold text-hb-navy">{{ __('company.interviews.context_heading') }}</h2>
                <dl class="grid gap-2 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-hb-muted">{{ __('company.interviews.candidate_label') }}</dt>
                        <dd class="font-medium text-hb-navy">{{ data_get($application, 'candidate.user.name') ?? data_get($application, 'candidate.name') ?? data_get($application, 'candidate_name') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-hb-muted">{{ __('company.interviews.job_label') }}</dt>
                        <dd class="font-medium text-hb-navy">{{ data_get($application, 'job.title') ?? data_get($application, 'job_title') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-hb-muted">{{ __('company.interviews.application_id_label') }}</dt>
                        <dd class="font-medium text-hb-navy">#{{ $applicationId }}</dd>
                    </div>
                    <div>
                        <dt class="text-hb-muted">{{ __('company.interviews.stage_field') }}</dt>
                        <dd class="font-medium text-hb-navy">{{ data_get($application, 'status') ?? '—' }}</dd>
                    </div>
                </dl>
            </section>

            <section class="space-y-4 border-t border-hb-border pt-4" aria-labelledby="details-heading">
                <h2 id="details-heading" class="text-lg font-semibold text-hb-navy">{{ __('company.interviews.form_heading') }}</h2>

                <div>
                    <label class="hb-label" for="interview_type">{{ __('company.interviews.type_field') }} <span class="text-hb-error">*</span></label>
                    <select id="interview_type" name="interview_type" class="hb-input" required>
                        @foreach ($interviewTypes as $type)
                            <option value="{{ $type }}" @selected($val('interview_type') === $type)>{{ __('company.interviews.types.'.$type) }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-hb-muted">{{ __('company.interviews.type_hint') }}</p>
                    @error('interview_type')<p class="hb-field-error">{{ $message }}</p>@enderror
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="hb-label" for="interviewer_id">{{ __('company.interviews.interviewer_field') }} <span class="text-hb-error">*</span></label>
                        @if ($interviewersAvailable)
                            <select id="interviewer_id" name="interviewer_id" class="hb-input" required>
                                <option value="">{{ __('company.interviews.select_option') }}</option>
                                @foreach ($interviewers as $person)
                                    <option value="{{ $person['user_id'] }}" @selected((string) $val('interviewer_id') === (string) $person['user_id'])>
                                        {{ $person['name'] }}@if ($person['role'] !== '') ({{ $person['role'] }})@endif
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-hb-muted">{{ __('company.interviews.interviewer_scope_hint') }}</p>
                        @else
                            <p class="rounded-lg border border-dashed border-hb-border px-3 py-2 text-sm text-hb-muted" data-interviewers-blocked>{{ $interviewersMessage }}</p>
                            <input type="hidden" name="interviewer_id" value="">
                        @endif
                        @error('interviewer_id')<p class="hb-field-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="hb-label" for="slot_date">{{ __('company.interviews.date_field') }}</label>
                        <input id="slot_date" name="slot_date" type="date" class="hb-input" value="{{ $val('slot_date') }}">
                        <p class="mt-1 text-xs text-hb-muted">{{ __('company.interviews.date_filter_hint') }}</p>
                    </div>
                </div>

                <div>
                    <label class="hb-label" for="display_timezone">{{ __('company.interviews.timezone_field') }}</label>
                    <select id="display_timezone" name="display_timezone" class="hb-input">
                        @foreach ($displayTimezones as $zone)
                            <option value="{{ $zone }}" @selected($val('display_timezone') === $zone)>{{ $zone }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-hb-muted">{{ __('company.interviews.timezone_display_only') }}</p>
                </div>

                <div>
                    <p class="hb-label">{{ __('company.interviews.slot_field') }} <span class="text-hb-error">*</span></p>
                    @if (! $slotsAvailable)
                        <p class="mt-2 text-sm text-hb-muted">{{ $slotsMessage }}</p>
                    @elseif ($slots === [])
                        <p class="mt-2 text-sm text-hb-muted">{{ $slotsMessage ?? __('company.interviews.no_open_slots') }}</p>
                    @else
                        <ul class="mt-2 space-y-2" data-slot-list>
                            @foreach ($slots as $slot)
                                @php $sid = data_get($slot, 'id'); @endphp
                                <li>
                                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-hb-border px-3 py-2 text-sm hover:bg-hb-page/60">
                                        <input
                                            type="radio"
                                            name="availability_id"
                                            value="{{ $sid }}"
                                            class="mt-1"
                                            @checked((string) $val('availability_id') === (string) $sid)
                                            required
                                        >
                                        <span>
                                            <span class="font-medium text-hb-navy">{{ \App\Frontend\Company\CompanyScheduleInterviewController::formatSlot($slot, $tz) }}</span>
                                            <span class="mt-0.5 block text-xs text-hb-muted">
                                                {{ __('company.interviews.duration_field') }}:
                                                {{ data_get($slot, 'duration_minutes') ?? '—' }}
                                                {{ __('company.interviews.minutes') }}
                                            </span>
                                        </span>
                                    </label>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                    <p class="mt-2 text-xs text-hb-muted">{{ __('company.interviews.duration_from_slot') }}</p>
                    @error('availability_id')<p class="hb-field-error">{{ $message }}</p>@enderror
                </div>

                <div class="rounded-xl border border-hb-border px-4 py-3 text-sm" data-conflict-state="{{ $conflict['state'] }}">
                    <p class="font-medium text-hb-navy">{{ __('company.interviews.availability_heading') }}</p>
                    <p class="mt-1 text-hb-muted">{{ $conflict['label'] }}</p>
                    <p class="mt-2 text-xs text-hb-muted">{{ __('company.interviews.availability_authority') }}</p>
                    <p class="mt-2 text-xs text-hb-muted">{{ __('company.interviews.invitation_honest') }}</p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="hb-label" for="round_no">{{ __('company.interviews.round_field') }}</label>
                        <input id="round_no" name="round_no" type="number" min="1" class="hb-input" value="{{ $val('round_no', 1) }}">
                    </div>
                    <div>
                        <label class="hb-label" for="location">{{ __('company.interviews.location_field') }}</label>
                        <input id="location" name="location" type="text" class="hb-input" maxlength="255" value="{{ $val('location') }}">
                    </div>
                </div>
                <div>
                    <label class="hb-label" for="meeting_link">{{ __('company.interviews.meeting_link_field') }}</label>
                    <input id="meeting_link" name="meeting_link" type="url" class="hb-input" maxlength="500" value="{{ $val('meeting_link') }}" placeholder="https://">
                    <p class="mt-1 text-xs text-hb-muted">{{ __('company.interviews.meeting_link_hint') }}</p>
                </div>
                <div>
                    <label class="hb-label" for="instructions">{{ __('company.interviews.instructions') }}</label>
                    <textarea id="instructions" name="instructions" rows="3" class="hb-input" maxlength="5000">{{ $val('instructions') }}</textarea>
                </div>
            </section>

            @if ($review && $selectedSlot)
                <section class="space-y-3 rounded-xl border border-hb-accent/40 bg-hb-accent/5 p-4" aria-labelledby="review-heading" data-schedule-review>
                    <h2 id="review-heading" class="text-lg font-semibold text-hb-navy">{{ __('company.interviews.summary_heading') }}</h2>
                    <dl class="grid gap-2 text-sm sm:grid-cols-2">
                        <div>
                            <dt class="text-hb-muted">{{ __('company.interviews.type_field') }}</dt>
                            <dd class="text-hb-navy">{{ __('company.interviews.types.'.$val('interview_type')) }}</dd>
                        </div>
                        <div>
                            <dt class="text-hb-muted">{{ __('company.interviews.interviewer_field') }}</dt>
                            <dd class="text-hb-navy">
                                @php
                                    $iname = collect($interviewers)->firstWhere('user_id', (int) $val('interviewer_id'));
                                @endphp
                                {{ $iname['name'] ?? ('#'.$val('interviewer_id')) }}
                            </dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-hb-muted">{{ __('company.interviews.when_label') }}</dt>
                            <dd class="text-hb-navy">{{ \App\Frontend\Company\CompanyScheduleInterviewController::formatSlot($selectedSlot, $tz) }}</dd>
                        </div>
                        <div>
                            <dt class="text-hb-muted">{{ __('company.interviews.availability_heading') }}</dt>
                            <dd class="text-hb-navy" data-review-conflict="{{ $conflict['state'] }}">{{ $conflict['label'] }}</dd>
                        </div>
                    </dl>
                    <p class="text-xs text-hb-muted">{{ __('company.interviews.invitation_honest') }}</p>
                </section>
            @endif

            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-hb-border pt-4">
                <x-button variant="light" :href="route('frontend.company.interview-calendar')">{{ __('company.interviews.cancel') }}</x-button>
                <div class="flex flex-wrap gap-2">
                    <x-button type="submit" name="schedule_action" value="refresh" variant="light">{{ __('company.interviews.refresh_slots') }}</x-button>
                    <x-button type="submit" name="schedule_action" value="review" variant="light">{{ __('company.interviews.review_action') }}</x-button>
                    <x-button
                        type="submit"
                        name="schedule_action"
                        value="confirm"
                        data-prefs-submit
                        :disabled="($conflict['state'] ?? '') === 'conflict' || ! $interviewersAvailable"
                    >{{ __('company.interviews.confirm') }}</x-button>
                </div>
            </div>
        </form>
    @endif
</div>
@endsection

@extends('frontend.layouts.app')

@section('title', __('Team interview calendar'))

@section('page_header')
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-hb-blue">{{ __('Hiring team') }}</p>
            <h1 class="mt-1 text-2xl font-semibold tracking-tight text-hb-navy">{{ __('Team interview calendar') }}</h1>
        </div>
        <x-button variant="primary" :href="route('frontend.company.interviews.schedule')">
            {{ __('company.interviews.schedule_interview') }}
        </x-button>
    </div>
@endsection

@section('content')
<div class="space-y-5">
    <div class="flex flex-col gap-3 rounded-2xl border border-sky-200 bg-sky-50 p-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="font-semibold text-sky-950">{{ __('Local planning view') }}</p>
            <p class="mt-1 text-sm text-sky-900">{{ __('Calendar slots and panel choices are stored in this browser only. They are not sent to the hiring API.') }}</p>
            <p class="mt-2 text-sm text-sky-900">{{ __('company.interviews.calendar_schedule_hint') }}</p>
        </div>
        <span class="w-fit rounded-full bg-white px-3 py-1 text-xs font-bold text-sky-900">{{ __('Not synced') }}</span>
    </div>

    <div class="grid gap-5 xl:grid-cols-[minmax(0,1.5fr)_minmax(18rem,0.65fr)]">
        <section class="rounded-2xl border border-hb-border bg-white p-4 shadow-sm sm:p-5" aria-labelledby="week-heading">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 id="week-heading" class="text-lg font-bold text-hb-navy">{{ __('Weekly schedule') }}</h2>
                    <p class="text-sm text-hb-muted" data-week-label></p>
                </div>
                <div class="flex gap-2">
                    <button type="button" class="rounded-lg border border-hb-border px-3 py-2 text-sm font-semibold hover:bg-slate-50" data-week-shift="-1">{{ __('Previous week') }}</button>
                    <button type="button" class="rounded-lg border border-hb-border px-3 py-2 text-sm font-semibold hover:bg-slate-50" data-week-today>{{ __('Today') }}</button>
                    <button type="button" class="rounded-lg border border-hb-border px-3 py-2 text-sm font-semibold hover:bg-slate-50" data-week-shift="1">{{ __('Next week') }}</button>
                </div>
            </div>
            <div class="mt-4 grid gap-3 sm:grid-cols-2 2xl:grid-cols-3" data-calendar-days aria-live="polite"></div>
            <p class="mt-3 text-xs text-hb-muted">{{ __('Availability shown here is a local planning aid, not interviewer availability confirmed by the server.') }}</p>
        </section>

        <aside class="space-y-5">
            <section class="rounded-2xl border border-hb-border bg-white p-5 shadow-sm" aria-labelledby="slot-heading">
                <h2 id="slot-heading" class="text-lg font-bold text-hb-navy">{{ __('Add availability slot') }}</h2>
                <form class="mt-4 space-y-3" data-calendar-form>
                    <label class="block text-sm font-medium text-hb-text">{{ __('Date') }}
                        <input type="date" name="date" required class="mt-1.5 w-full rounded-lg border border-hb-border px-3 py-2">
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="block text-sm font-medium text-hb-text">{{ __('Start time') }}
                            <input type="time" name="start" required class="mt-1.5 w-full rounded-lg border border-hb-border px-3 py-2">
                        </label>
                        <label class="block text-sm font-medium text-hb-text">{{ __('End time') }}
                            <input type="time" name="end" required class="mt-1.5 w-full rounded-lg border border-hb-border px-3 py-2">
                        </label>
                    </div>
                    <label class="block text-sm font-medium text-hb-text">{{ __('Selected interview panel') }}
                        <input type="text" name="panel" required maxlength="500" class="mt-1.5 w-full rounded-lg border border-hb-border px-3 py-2" placeholder="{{ __('Names, separated by commas') }}">
                    </label>
                    <p class="text-xs text-hb-muted">{{ __('Enter the interviewers assigned to this planning slot.') }}</p>
                    <button type="submit" class="w-full rounded-lg bg-hb-blue px-4 py-2.5 text-sm font-semibold text-white hover:bg-hb-navy">{{ __('Add slot to this device') }}</button>
                </form>
                <p class="mt-3 text-sm text-hb-muted" data-calendar-status role="status" aria-live="polite"></p>
            </section>

            <section class="rounded-2xl border border-hb-border bg-white p-5 shadow-sm" aria-labelledby="panel-heading">
                <h2 id="panel-heading" class="text-lg font-bold text-hb-navy">{{ __('Selected interview panel') }}</h2>
                <p class="mt-1 text-sm text-hb-muted">{{ __('Panel names used by saved local slots.') }}</p>
                <ul class="mt-3 flex flex-wrap gap-2" data-panel-list></ul>
                <p class="mt-3 text-xs text-hb-muted" data-panel-empty>{{ __('No panel members have been added to a slot.') }}</p>
            </section>
        </aside>
    </div>
</div>

<script>
    (() => {
        const key = 'company-team-interview-calendar-v1';
        const form = document.querySelector('[data-calendar-form]');
        const daysContainer = document.querySelector('[data-calendar-days]');
        const status = document.querySelector('[data-calendar-status]');
        const panelList = document.querySelector('[data-panel-list]');
        const panelEmpty = document.querySelector('[data-panel-empty]');
        const weekLabel = document.querySelector('[data-week-label]');
        let slots = [];
        let weekOffset = 0;

        function localDate(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        function mondayOf(date) {
            const monday = new Date(date.getFullYear(), date.getMonth(), date.getDate());
            monday.setDate(monday.getDate() - ((monday.getDay() + 6) % 7));
            return monday;
        }

        function save() {
            try {
                localStorage.setItem(key, JSON.stringify(slots));
                return true;
            } catch {
                status.textContent = @json(__('Your browser could not save this calendar.'));
                return false;
            }
        }

        function renderPanel() {
            const names = [...new Set(slots.flatMap((slot) => slot.panel))].sort((a, b) => a.localeCompare(b));
            panelList.replaceChildren(...names.map((name) => {
                const item = document.createElement('li');
                item.className = 'rounded-full bg-sky-50 px-3 py-1 text-xs font-semibold text-sky-900';
                item.textContent = name;
                return item;
            }));
            panelEmpty.classList.toggle('hidden', names.length > 0);
        }

        function renderWeek() {
            const start = mondayOf(new Date());
            start.setDate(start.getDate() + weekOffset * 7);
            const end = new Date(start);
            end.setDate(start.getDate() + 6);
            weekLabel.textContent = `${start.toLocaleDateString()} – ${end.toLocaleDateString()}`;
            const dayNodes = [];
            for (let index = 0; index < 7; index += 1) {
                const date = new Date(start);
                date.setDate(start.getDate() + index);
                const dateKey = localDate(date);
                const daySlots = slots.filter((slot) => slot.date === dateKey).sort((a, b) => a.start.localeCompare(b.start));
                const section = document.createElement('section');
                section.className = 'min-h-36 rounded-xl border border-hb-border bg-slate-50 p-3';
                const heading = document.createElement('h3');
                heading.className = 'text-sm font-bold text-hb-navy';
                heading.textContent = date.toLocaleDateString(undefined, { weekday: 'short', month: 'short', day: 'numeric' });
                section.append(heading);
                if (!daySlots.length) {
                    const empty = document.createElement('p');
                    empty.className = 'mt-3 text-xs text-hb-muted';
                    empty.textContent = @json(__('No local slots'));
                    section.append(empty);
                }
                daySlots.forEach((slot) => {
                    const card = document.createElement('article');
                    card.className = 'mt-2 rounded-lg border border-sky-200 bg-white p-2.5';
                    const time = document.createElement('p');
                    time.className = 'text-xs font-bold text-sky-900';
                    time.textContent = `${slot.start} – ${slot.end}`;
                    const names = document.createElement('p');
                    names.className = 'mt-1 text-xs text-slate-600';
                    names.textContent = slot.panel.join(', ');
                    const remove = document.createElement('button');
                    remove.type = 'button';
                    remove.className = 'mt-2 text-xs font-semibold text-red-700 hover:underline';
                    remove.textContent = @json(__('Remove'));
                    remove.addEventListener('click', () => {
                        slots = slots.filter((item) => item.id !== slot.id);
                        save();
                        renderWeek();
                        renderPanel();
                    });
                    card.append(time, names, remove);
                    section.append(card);
                });
                dayNodes.push(section);
            }
            daysContainer.replaceChildren(...dayNodes);
        }

        try {
            const saved = JSON.parse(localStorage.getItem(key) || '[]');
            slots = Array.isArray(saved) ? saved.filter((slot) =>
                slot && typeof slot.id === 'string' && typeof slot.date === 'string' &&
                typeof slot.start === 'string' && typeof slot.end === 'string' && Array.isArray(slot.panel)
            ) : [];
        } catch {
            status.textContent = @json(__('Saved calendar data could not be read.'));
        }

        form.addEventListener('submit', (event) => {
            event.preventDefault();
            if (!form.reportValidity()) return;
            const date = form.elements.date.value;
            const start = form.elements.start.value;
            const end = form.elements.end.value;
            const panel = [...new Set(form.elements.panel.value.split(',').map((name) => name.trim()).filter(Boolean))];
            if (end <= start) {
                form.elements.end.setCustomValidity(@json(__('End time must be after start time.')));
                form.elements.end.reportValidity();
                form.elements.end.setCustomValidity('');
                return;
            }
            const overlaps = slots.some((slot) => slot.date === date && slot.start < end && start < slot.end && slot.panel.some((name) => panel.includes(name)));
            if (overlaps) {
                status.textContent = @json(__('This slot overlaps an existing slot for a selected interviewer.'));
                return;
            }
            slots.push({ id: crypto.randomUUID(), date, start, end, panel });
            if (save()) {
                status.textContent = @json(__('Slot saved on this device only.'));
                form.reset();
                renderWeek();
                renderPanel();
            }
        });

        document.querySelectorAll('[data-week-shift]').forEach((button) => button.addEventListener('click', () => {
            weekOffset += Number(button.dataset.weekShift);
            renderWeek();
        }));
        document.querySelector('[data-week-today]').addEventListener('click', () => {
            weekOffset = 0;
            renderWeek();
        });
        renderWeek();
        renderPanel();
    })();
</script>
@endsection

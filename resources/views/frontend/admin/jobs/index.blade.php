@extends('frontend.layouts.app')

@section('title', __('Job moderation preview'))

@section('page_header')
    <div>
        <p class="text-sm font-semibold text-hb-blue">{{ __('Platform administration') }}</p>
        <h1 class="mt-1 text-2xl font-semibold tracking-tight text-hb-navy">{{ __('Job moderation') }}</h1>
    </div>
@endsection

@section('content')
<div class="space-y-5">
    <section class="rounded-2xl border border-amber-300 bg-amber-50 p-4" role="status">
        <p class="font-bold text-amber-950">{{ __('Preview only — no moderation API is available.') }}</p>
        <p class="mt-1 text-sm leading-6 text-amber-900">{{ __('The queue below contains demonstration records. Approve, Request changes, and Reject actions are saved only in this browser and do not change platform jobs.') }}</p>
    </section>

    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-hb-muted">{{ __('Demonstration pending-job queue') }}</p>
        <button type="button" data-reset-moderation class="rounded-lg border border-hb-border px-4 py-2 text-sm font-semibold text-hb-navy hover:bg-slate-50">{{ __('Reset local preview') }}</button>
    </div>

    <section class="grid gap-4 xl:grid-cols-2" aria-label="{{ __('Pending job moderation preview') }}">
        @foreach ([
            ['id' => 'demo-101', 'title' => 'Senior Product Designer', 'company' => 'Northstar Studio', 'location' => 'Cairo · Hybrid', 'submitted' => '2026-10-19', 'summary' => 'Lead product design across hiring workflows and candidate experience.'],
            ['id' => 'demo-102', 'title' => 'Backend Engineer', 'company' => 'Cedar Labs', 'location' => 'Remote', 'submitted' => '2026-10-21', 'summary' => 'Build reliable APIs for a growing recruitment platform.'],
            ['id' => 'demo-103', 'title' => 'Talent Acquisition Partner', 'company' => 'Bright Path', 'location' => 'Alexandria · On-site', 'submitted' => '2026-10-22', 'summary' => 'Partner with teams to deliver inclusive, high-quality hiring.'],
        ] as $job)
            <article data-moderation-job="{{ $job['id'] }}" class="rounded-2xl border border-hb-border bg-white p-5 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-hb-muted">{{ __('Demo record') }} · {{ $job['id'] }}</p>
                        <h2 class="mt-1 text-lg font-bold text-hb-navy">{{ $job['title'] }}</h2>
                        <p class="mt-1 text-sm font-medium text-hb-text">{{ $job['company'] }}</p>
                    </div>
                    <span data-job-status class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-950">{{ __('Pending demo review') }}</span>
                </div>
                <p class="mt-3 text-sm text-hb-muted">{{ $job['location'] }} <span aria-hidden="true">·</span> {{ __('Submitted') }} {{ $job['submitted'] }}</p>
                <p class="mt-3 text-sm leading-6 text-hb-text">{{ $job['summary'] }}</p>
                <div class="mt-5 flex flex-wrap gap-2 border-t border-hb-border pt-4">
                    <button type="button" data-moderation-action="approve" class="rounded-lg bg-emerald-700 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-800">{{ __('Approve') }} · {{ __('local preview') }}</button>
                    <button type="button" data-moderation-action="changes" class="rounded-lg border border-amber-400 px-3 py-2 text-sm font-semibold text-amber-950 hover:bg-amber-50">{{ __('Request changes') }}</button>
                    <button type="button" data-moderation-action="reject" class="rounded-lg border border-red-300 px-3 py-2 text-sm font-semibold text-red-800 hover:bg-red-50">{{ __('Reject') }}</button>
                </div>
                <p data-job-reason class="mt-3 hidden whitespace-pre-line rounded-lg bg-slate-50 p-3 text-sm text-hb-muted"></p>
            </article>
        @endforeach
    </section>
    <p data-moderation-status class="text-sm text-hb-muted" role="status" aria-live="polite"></p>
</div>

<dialog data-reason-dialog class="w-[min(34rem,calc(100%-2rem))] rounded-2xl border border-hb-border p-0 shadow-2xl backdrop:bg-slate-950/40">
    <form method="dialog" data-reason-form class="space-y-4 p-5">
        <div>
            <h2 data-reason-title class="text-lg font-bold text-hb-navy"></h2>
            <p class="mt-1 text-sm text-hb-muted">{{ __('This decision is saved to this browser preview only.') }}</p>
        </div>
        <label class="block text-sm font-medium text-hb-text">{{ __('Reason') }}
            <textarea name="reason" required minlength="3" maxlength="1000" rows="4" class="mt-1.5 w-full rounded-lg border border-hb-border p-3"></textarea>
        </label>
        <div class="flex justify-end gap-2">
            <button type="button" data-close-dialog class="rounded-lg border border-hb-border px-4 py-2 text-sm font-semibold text-hb-navy">{{ __('Cancel') }}</button>
            <button type="submit" class="rounded-lg bg-hb-blue px-4 py-2 text-sm font-semibold text-white">{{ __('Save local decision') }}</button>
        </div>
    </form>
</dialog>

<script>
(() => {
    const storageKey = 'super-admin-job-moderation-preview-v1';
    const statusLabels = {
        approve: @json(__('Approved locally')),
        changes: @json(__('Changes requested locally')),
        reject: @json(__('Rejected locally')),
    };
    const dialog = document.querySelector('[data-reason-dialog]');
    const form = document.querySelector('[data-reason-form]');
    const pageStatus = document.querySelector('[data-moderation-status]');
    let activeJob = null;
    let activeAction = null;
    let decisions = {};

    const showDecision = (job, decision) => {
        const row = document.querySelector(`[data-moderation-job="${CSS.escape(job)}"]`);
        if (!row) return;
        const status = row.querySelector('[data-job-status]');
        status.textContent = statusLabels[decision.status] ?? @json(__('Pending demo review'));
        status.className = decision.status === 'approve'
            ? 'rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-950'
            : decision.status === 'reject'
                ? 'rounded-full bg-red-100 px-3 py-1 text-xs font-bold text-red-950'
                : 'rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-950';
        const reason = row.querySelector('[data-job-reason]');
        if (decision.reason) {
            reason.textContent = `${decision.status === 'changes' ? @json(__('Change request')) : @json(__('Reason'))}: ${decision.reason}`;
            reason.classList.remove('hidden');
        } else {
            reason.textContent = '';
            reason.classList.add('hidden');
        }
    };

    try {
        decisions = JSON.parse(localStorage.getItem(storageKey) || '{}');
        Object.entries(decisions).forEach(([job, decision]) => showDecision(job, decision));
    } catch {
        pageStatus.textContent = @json(__('Could not read the saved local preview. Reset it to continue.'));
    }

    document.querySelectorAll('[data-moderation-job]').forEach((row) => {
        row.querySelectorAll('[data-moderation-action]').forEach((button) => {
            button.addEventListener('click', () => {
                activeJob = row.dataset.moderationJob;
                activeAction = button.dataset.moderationAction;
                if (activeAction === 'approve') {
                    if (!window.confirm(@json(__('Record this approval in the local preview?')))) return;
                    saveDecision({ status: activeAction, reason: '' });
                    return;
                }
                document.querySelector('[data-reason-title]').textContent = activeAction === 'reject'
                    ? @json(__('Reject demo job'))
                    : @json(__('Request changes for demo job'));
                form.elements.reason.value = '';
                dialog.showModal();
            });
        });
    });

    function saveDecision(decision) {
        decisions[activeJob] = decision;
        try {
            localStorage.setItem(storageKey, JSON.stringify(decisions));
            showDecision(activeJob, decision);
            pageStatus.textContent = @json(__('Local preview updated. No server job was changed.'));
            if (dialog.open) dialog.close();
        } catch {
            pageStatus.textContent = @json(__('Could not save this decision in browser storage.'));
        }
    }

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        if (!form.reportValidity()) return;
        const reason = form.elements.reason.value.trim();
        if (reason.length < 3) {
            form.elements.reason.setCustomValidity(@json(__('Please provide a reason of at least 3 characters.')));
            form.reportValidity();
            form.elements.reason.setCustomValidity('');
            return;
        }
        saveDecision({ status: activeAction, reason });
    });

    document.querySelector('[data-close-dialog]').addEventListener('click', () => dialog.close());
    document.querySelector('[data-reset-moderation]').addEventListener('click', () => {
        if (!window.confirm(@json(__('Reset all local demo decisions?')))) return;
        try {
            localStorage.removeItem(storageKey);
            window.location.reload();
        } catch {
            pageStatus.textContent = @json(__('Could not reset the local moderation preview.'));
        }
    });
})();
</script>
@endsection

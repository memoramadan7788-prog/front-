@php
    /** @var array<string, mixed> $flow */
    $job = $flow['job'];
    $role = $flow['role_summary'];
@endphp

<div
    id="candidate-apply-modal"
    class="fixed inset-0 z-50 hidden items-end justify-center p-0 sm:items-center sm:p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="candidate-apply-modal-title"
    data-hb-modal
    data-hb-modal-focus-trap
>
    <div class="absolute inset-0 bg-hb-navy/50" data-hb-modal-overlay tabindex="-1"></div>

    <div class="relative z-10 flex max-h-[92vh] w-full max-w-3xl flex-col overflow-hidden rounded-t-2xl border border-hb-border bg-hb-surface shadow-hb-md sm:rounded-hb">
        <div class="flex items-start justify-between gap-4 border-b border-hb-border px-5 py-4">
            <div class="min-w-0">
                <h2 id="candidate-apply-modal-title" class="text-base font-semibold text-hb-navy">
                    {{ __('candidate.applications.apply_title', ['company' => $job['company']]) }}
                </h2>
                <p class="mt-1 text-sm text-hb-muted">
                    {{ $job['title'] }} · {{ $job['code'] }}
                </p>
            </div>
            <button
                type="button"
                class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-hb-muted hover:bg-hb-page hover:text-hb-text"
                data-hb-modal-close
                aria-label="{{ __('common.close_dialog') }}"
            >
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <div class="overflow-y-auto px-5 py-4">
            <div class="mb-4 rounded-lg border border-hb-blue/20 bg-hb-info-soft px-3 py-2 text-xs text-hb-blue" role="status">
                {{ __('candidate.applications.modal_escape_hint') }}
            </div>

            <div class="mb-4 flex flex-wrap items-center gap-2 text-sm text-hb-muted">
                <x-badge variant="info" :dot="true">{{ $role['source'] }}</x-badge>
                <span>{{ $role['meta'] }}</span>
                <span>·</span>
                <span>{{ $role['salary'] }}</span>
            </div>

            @include('candidate.applications._apply-form', [
                'flow' => $flow,
                'formId' => 'candidate-apply-modal-form',
            ])
        </div>
    </div>
</div>

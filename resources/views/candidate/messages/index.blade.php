@extends('layouts.app')

@section('title', $pageTitle ?? 'Messages · Hirebridge')
@section('breadcrumb', '21 / My workspace / Messages')

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">Messages</h1>
    <p class="mt-1 text-sm text-hb-muted">Keep hiring conversations and useful context together.</p>
@endsection

@section('content')
    @php
        $threads = $inbox['threads'] ?? [];
        $active = collect($threads)->firstWhere('selected', true) ?? ($threads[0] ?? null);
    @endphp

    <div class="hb-mock-banner mb-6 rounded-lg text-start">
        Frontend UI only — messages stay in this browser. Nothing is delivered to hiring teams.
    </div>

    <div
        class="grid gap-4 lg:grid-cols-[240px_minmax(0,1fr)] xl:grid-cols-[240px_minmax(0,1fr)_260px]"
        data-candidate-messages
        data-active-thread="{{ $active['id'] ?? '' }}"
    >
        {{-- Inbox --}}
        <section class="hb-surface flex flex-col overflow-hidden" aria-labelledby="inbox-heading">
            <div class="border-b border-hb-border px-4 py-3">
                <h2 id="inbox-heading" class="text-base font-semibold text-hb-navy">Inbox</h2>
                <label class="mt-3 block">
                    <span class="sr-only">Search conversations</span>
                    <input
                        type="search"
                        class="hb-input"
                        placeholder="Search conversations…"
                        data-message-search
                        autocomplete="off"
                    >
                </label>
            </div>
            <ul class="flex-1 divide-y divide-hb-border overflow-y-auto" data-thread-list role="listbox" aria-label="Conversations">
                @foreach ($threads as $thread)
                    <li>
                        <button
                            type="button"
                            class="w-full px-4 py-3 text-start hover:bg-hb-page {{ ! empty($thread['selected']) ? 'bg-hb-info-soft' : '' }}"
                            role="option"
                            aria-selected="{{ ! empty($thread['selected']) ? 'true' : 'false' }}"
                            data-thread-id="{{ $thread['id'] }}"
                            data-thread-search="{{ strtolower($thread['contact'].' '.$thread['company'].' '.$thread['snippet'].' '.$thread['code']) }}"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <p class="text-sm font-semibold text-hb-navy">{{ $thread['contact'] }}</p>
                                <p class="shrink-0 text-xs text-hb-muted">{{ $thread['time'] }}</p>
                            </div>
                            <p class="mt-0.5 text-xs text-hb-muted">{{ $thread['company_short'] }}</p>
                            @if (! empty($thread['unread']))
                                <p class="mt-1 text-xs font-medium text-hb-blue">
                                    <span aria-hidden="true">●</span>
                                    Selected · {{ $thread['unread'] }} unread
                                </p>
                            @endif
                            <p class="mt-1 truncate text-sm text-hb-muted">{{ $thread['snippet'] }}</p>
                        </button>
                    </li>
                @endforeach
            </ul>
            <div class="hidden border-t border-hb-border px-4 py-6 text-center" data-thread-empty role="status" hidden>
                <p class="text-sm font-semibold text-hb-navy">No conversations match</p>
                <p class="mt-1 text-xs text-hb-muted">Try another search.</p>
            </div>
        </section>

        {{-- Conversation --}}
        <section class="hb-surface flex min-h-[28rem] flex-col overflow-hidden" aria-labelledby="conversation-heading" data-conversation-panel>
            @if ($active)
                <div class="border-b border-hb-border px-5 py-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 id="conversation-heading" class="text-base font-semibold text-hb-navy" data-conversation-contact>
                                {{ $active['contact'] }}
                            </h2>
                            <p class="mt-1 text-sm text-hb-muted" data-conversation-meta>
                                {{ $active['company'] }} · {{ $active['role'] }} · {{ $active['code'] }}
                            </p>
                        </div>
                        <button
                            type="button"
                            class="hb-btn-outline px-3 py-2 text-xs lg:hidden"
                            data-toggle-context
                            aria-expanded="false"
                        >
                            Context
                        </button>
                    </div>
                </div>

                <div class="flex-1 space-y-4 overflow-y-auto px-5 py-4" data-message-list>
                    <p class="text-center text-xs text-hb-muted" data-date-divider>{{ $active['date_divider'] }}</p>
                    @foreach ($active['messages'] as $message)
                        <div
                            @class([
                                'max-w-[90%] sm:max-w-[80%]',
                                'ms-auto' => ($message['from'] ?? '') === 'you',
                            ])
                            data-message-bubble
                        >
                            <p class="mb-1 text-xs text-hb-muted">
                                {{ $message['time'] }} · {{ $message['role'] }}
                            </p>
                            <div
                                @class([
                                    'rounded-lg px-4 py-3 text-sm leading-relaxed',
                                    'bg-hb-info-soft text-hb-navy' => ($message['from'] ?? '') === 'you',
                                    'bg-hb-page text-hb-text' => ($message['from'] ?? '') !== 'you',
                                ])
                            >
                                {{ $message['body'] }}
                            </div>
                            @if (! empty($message['attachment']))
                                <p class="mt-2 text-sm">
                                    <span class="font-semibold text-hb-blue">
                                        ↳ {{ $message['attachment']['name'] }} · {{ $message['attachment']['size'] }}
                                    </span>
                                    <span class="sr-only">(attachment preview — not downloadable)</span>
                                </p>
                            @endif
                            @if (! empty($message['status']))
                                <p class="mt-1 text-end text-xs text-hb-muted">{{ $message['status'] }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="border-t border-hb-border px-5 py-4">
                    <label class="hb-label" for="message-compose">Reply to <span data-compose-name>{{ $active['contact'] }}</span></label>
                    <textarea
                        id="message-compose"
                        class="hb-input"
                        rows="3"
                        placeholder="Write your message…"
                        data-message-compose
                    ></textarea>
                    <p class="hb-field-error hidden" data-compose-error role="alert"></p>
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <button type="button" class="hb-btn-outline" data-message-attach disabled title="Attachments — Backend Team">
                            Attach file
                        </button>
                        <button type="button" class="hb-btn-primary" data-message-send>
                            Send message
                        </button>
                    </div>
                    <p class="mt-2 text-xs text-hb-muted">
                        Messages are visible to the authorized hiring team for this application.
                        <span class="font-medium">Local preview only — not delivered.</span>
                    </p>
                    <p class="mt-2 hidden text-sm text-hb-success" data-compose-status role="status"></p>
                </div>
            @else
                <div class="flex flex-1 items-center justify-center px-5 py-10 text-center" role="status">
                    <div>
                        <p class="font-semibold text-hb-navy">No conversation selected</p>
                        <p class="mt-2 text-sm text-hb-muted">Choose a thread from your inbox.</p>
                    </div>
                </div>
            @endif
        </section>

        {{-- Context --}}
        <aside
            class="hb-surface hidden p-5 xl:block"
            aria-labelledby="context-heading"
            data-message-context
        >
            @if ($active)
                <h2 id="context-heading" class="text-base font-semibold text-hb-navy">Application context</h2>
                <div class="mt-3">
                    <x-badge variant="info" :dot="true" data-context-stage>{{ $active['stage'] }}</x-badge>
                </div>
                <dl class="mt-4 space-y-2 text-sm">
                    <div>
                        <dt class="text-hb-muted">Company</dt>
                        <dd class="font-medium text-hb-navy" data-context-company>{{ $active['company'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-hb-muted">Role</dt>
                        <dd class="font-medium text-hb-navy" data-context-role>{{ $active['role'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-hb-muted">ID / type</dt>
                        <dd class="font-medium text-hb-navy" data-context-code>{{ $active['code'] }} · {{ $active['source'] }}</dd>
                    </div>
                </dl>
                <p class="mt-4 border-t border-hb-border pt-4 text-sm text-hb-text" data-context-event>
                    {{ $active['event'] }}
                </p>
                <a
                    href="{{ route('candidate.applications.show', $active['application_id']) }}"
                    class="hb-btn-outline mt-4 inline-flex w-full"
                    data-context-application
                >
                    View application
                </a>
            @endif
        </aside>
    </div>

    {{-- Thread payloads for client switching --}}
    <script type="application/json" data-message-threads-json>
        @json($threads)
    </script>

    <p class="mt-8 text-xs text-hb-muted">{{ $inbox['footer'] }}</p>
@endsection

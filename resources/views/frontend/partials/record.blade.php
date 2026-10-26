@php
    // Generic read-only view of an API record (scalars as a definition list, nested arrays recursively).
    $record = is_array($record ?? null) ? $record : [];
    $depth = $depth ?? 0;
    $hidden = [
        'password', 'token', 'remember_token', 'deleted_at',
        'storage_path', 'doc_path', 'photo_path',
        'id', 'user_id', 'candidate_id', 'company_id',
    ];
@endphp

@if ($depth > 3)
    <span class="text-hb-muted">…</span>
@elseif (array_is_list($record))
    @if (empty($record))
        <span class="text-hb-muted">—</span>
    @elseif (collect($record)->every(fn ($v) => ! is_array($v)))
        <span>{{ implode(', ', array_map('strval', $record)) }}</span>
    @else
        <div class="space-y-3">
            @foreach ($record as $item)
                <div class="rounded-lg border border-hb-border bg-hb-page/50 p-3">
                    @include('frontend.partials.record', ['record' => is_array($item) ? $item : ['value' => $item], 'depth' => $depth + 1])
                </div>
            @endforeach
        </div>
    @endif
@else
    <dl class="grid gap-x-6 gap-y-3 sm:grid-cols-2">
        @foreach ($record as $key => $value)
            @continue(in_array($key, $hidden, true))
            <div @class(['sm:col-span-2' => is_array($value)])>
                <dt class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ \Illuminate\Support\Str::headline((string) $key) }}</dt>
                <dd class="mt-1 text-sm text-hb-text">
                    @if (is_array($value))
                        @include('frontend.partials.record', ['record' => $value, 'depth' => $depth + 1])
                    @elseif (is_bool($value))
                        {{ $value ? __('Yes') : __('No') }}
                    @elseif ($value === null || $value === '')
                        —
                    @elseif (is_string($key) && str_ends_with($key, '_at'))
                        {{ \App\Support\Rows::date($value) }}
                    @elseif (is_string($key) && $key === 'status')
                        <x-badge :variant="\App\Support\Rows::badgeVariant((string) $value)" :dot="true">{{ $value }}</x-badge>
                    @else
                        {{ $value }}
                    @endif
                </dd>
            </div>
        @endforeach
    </dl>
@endif

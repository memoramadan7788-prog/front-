@php $onDark = $onDark ?? false; @endphp
<span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white p-1 shadow-sm ring-1 ring-black/5" aria-hidden="true">
    <img src="{{ asset('images/brand/logo-mark.png') }}" alt="" class="h-full w-full object-contain">
</span>
<span class="text-lg font-extrabold tracking-tight {{ $onDark ? 'text-white' : 'text-hb-navy' }}">Talent<span class="{{ $onDark ? 'text-sky-300' : 'text-hb-blue' }}">Bridge</span></span>

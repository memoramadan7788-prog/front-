@props(['name', 'label', 'type' => 'text', 'icon' => null, 'hint' => null])
@php
    $icons = [
        'mail' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
        'lock' => '<rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/>',
        'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21v-1a6 6 0 0 1 6-6h4a6 6 0 0 1 6 6v1"/>',
    ];
    $isPassword = $type === 'password';
@endphp
<div>
  <label for="{{ $name }}" class="mb-1.5 block text-sm font-semibold text-slate-700">{{ $label }}</label>
  <div class="relative">
    @if ($icon && isset($icons[$icon]))
      <svg class="pointer-events-none absolute start-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons[$icon] !!}</svg>
    @endif

    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}"
           value="{{ $isPassword ? '' : old($name) }}"
           @error($name) aria-invalid="true" @enderror
           {{ $attributes->merge(['class' => 'w-full rounded-xl border bg-white py-3 text-sm text-slate-900 placeholder:text-slate-400 transition focus:outline-none focus:ring-4 '
               . ($icon ? 'ps-11 ' : 'ps-4 ')
               . ($isPassword ? 'pe-11 ' : 'pe-4 ')
               . ($errors->has($name) ? 'border-red-400 focus:border-red-500 focus:ring-red-500/15' : 'border-slate-300 focus:border-sky-500 focus:ring-sky-500/15')]) }}>

    @if ($isPassword)
      <button type="button" data-toggle-password="{{ $name }}" aria-label="Show password"
              class="absolute end-3 top-1/2 -translate-y-1/2 rounded-md p-1 text-slate-400 transition hover:text-sky-600">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
      </button>
    @endif
  </div>

  @error($name)
    <p class="mt-1.5 text-sm text-red-600" role="alert">{{ $message }}</p>
  @elseif ($hint)
    <p class="mt-1.5 text-xs text-slate-500">{{ $hint }}</p>
  @enderror
</div>

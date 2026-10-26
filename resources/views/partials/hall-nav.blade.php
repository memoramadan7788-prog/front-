@php use App\Support\Hall; @endphp
<nav class="flex h-12 items-center justify-between gap-4 bg-brand-700 px-4 text-white shadow md:px-8" aria-label="Hall navigation">
  <a href="{{ route('frontend.hall.lobby') }}" class="text-lg font-bold">TalentBridge</a>

  <ul class="flex items-center gap-1 overflow-x-auto text-sm font-semibold">
    @foreach (config('hall.nav') as $item)
      @php($ready = Hall::ready($item['target']))
      <li>
        @if ($ready)
          <a href="{{ Hall::url($item['target']) }}"
             class="flex items-center gap-1.5 rounded-md px-3 py-1.5 whitespace-nowrap hover:bg-white/15 {{ Hall::active($item['target']) ? 'bg-white/20' : '' }}">
            <span aria-hidden="true">{{ $item['icon'] }}</span>{{ $item['label'] }}
          </a>
        @else
          <span class="flex cursor-not-allowed items-center gap-1.5 rounded-md px-3 py-1.5 whitespace-nowrap opacity-50" title="{{ __('hall.nav.coming_soon') }}">
            <span aria-hidden="true">{{ $item['icon'] }}</span>{{ $item['label'] }}
          </span>
        @endif
      </li>
    @endforeach
  </ul>

  <div class="flex items-center gap-3 text-sm">
    <a href="{{ route('frontend.workspace') }}" class="rounded-md bg-white px-3 py-1.5 font-semibold text-brand-700 hover:bg-sky-50">{{ __('hall.nav.workspace') }}</a>
    <span class="hidden md:inline">{{ auth()->user()->name }}</span>
    <form method="POST" action="{{ route('frontend.account.logout') }}">@csrf
      <button class="rounded-md bg-white/15 px-3 py-1.5 font-semibold hover:bg-white/25">{{ __('hall.nav.log_out') }}</button>
    </form>
  </div>
</nav>

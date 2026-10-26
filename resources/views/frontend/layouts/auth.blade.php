@php
  $locale = str_replace('_', '-', app()->getLocale());
  $isRtl = in_array(app()->getLocale(), ['ar'], true);
@endphp
<!doctype html>
<html lang="{{ $locale }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'TalentBridge') | TalentBridge</title>
  @include('frontend.partials.theme-boot')
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  @include('frontend.partials.js-i18n')
  <style>
    @keyframes auth-zoom { from { transform: scale(1); } to { transform: scale(1.1); } }
    .auth-bg { animation: auth-zoom 20s ease-in-out infinite alternate; }
    @keyframes auth-rise { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: none; } }
    .auth-rise { animation: auth-rise .6s cubic-bezier(.22, 1, .36, 1) both; }
    .auth-rise-2 { animation-delay: .15s; }
    @media (prefers-reduced-motion: reduce) { .auth-bg, .auth-rise { animation: none; } }
  </style>
</head>
<body class="bg-slate-900 text-slate-900 antialiased">
  <div class="relative isolate min-h-svh overflow-hidden bg-gradient-to-br from-sky-700 via-sky-500 to-slate-800">

    {{-- Background --}}
    <img src="{{ asset('images/halls/talentbridge-hero.webp') }}" alt=""
         class="auth-bg absolute inset-0 -z-20 h-full w-full object-cover" onerror="this.style.display='none'">
    <div class="absolute inset-0 -z-10 bg-gradient-to-b from-slate-900/70 via-sky-900/55 to-slate-900/85"></div>

    <div class="mx-auto flex min-h-svh max-w-5xl flex-col px-5 py-6 sm:px-8">

      {{-- Top bar --}}
      <header class="flex items-center justify-between gap-3">
        <a href="{{ route('frontend.home') }}" class="text-sm font-semibold text-white/80 transition hover:text-white">&larr; {{ __('auth.back_home') }}</a>
        <div class="flex items-center gap-2">
          @include('frontend.partials.theme-switch')
          @include('frontend.partials.language-switch')
        </div>
      </header>

      {{-- Form card (centered) --}}
      <main class="flex flex-1 flex-col items-center justify-center py-8">
        <div class="auth-rise w-full max-w-md rounded-3xl bg-white p-7 shadow-2xl shadow-slate-950/30 sm:p-10">
          <a href="{{ route('frontend.home') }}" class="mx-auto mb-6 block w-52 sm:w-56" aria-label="TalentBridge home">
            <img src="{{ asset('images/brand/logo-full.png') }}" alt="TalentBridge, Better Talent. Greater Opportunities." class="h-auto w-full">
          </a>

          @if (session('status'))
            <div class="mb-5 rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800" role="status">{{ session('status') }}</div>
          @endif

          @if (session('success_key'))
            <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">{{ __(session('success_key'), session('success_replace', [])) }}</div>
          @elseif (session('success'))
            <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">{{ session('success') }}</div>
          @endif

          @if ($errors->has('error'))
            <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">{{ $errors->first('error') }}</div>
          @endif

          @yield('content')
        </div>

        {{-- Pitch (below the card) --}}
        <section class="auth-rise auth-rise-2 mt-10 max-w-2xl text-center text-white">
          <h2 class="text-2xl font-black leading-tight sm:text-3xl">{{ __('auth.pitch.headline') }}</h2>
          <p class="mt-3 text-sm leading-7 text-sky-50/90 sm:text-base">
            {{ __('auth.pitch.body') }}
          </p>
          <ul class="mt-6 flex flex-wrap justify-center gap-x-6 gap-y-3 text-sm font-medium">
            @foreach (__('auth.pitch.points') as $point)
              <li class="flex items-center gap-2.5">
                <span class="grid h-6 w-6 place-items-center rounded-full bg-white/20">
                  <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>
                </span>
                {{ $point }}
              </li>
            @endforeach
          </ul>
        </section>
      </main>

      <p class="text-center text-xs text-white/60">&copy; {{ date('Y') }} TalentBridge. {{ __('auth.pitch.rights') }}</p>
    </div>
  </div>

  <script>
    // show / hide password
    document.querySelectorAll('[data-toggle-password]').forEach((btn) => {
      btn.addEventListener('click', () => {
        const input = document.getElementById(btn.dataset.togglePassword);
        if (!input) return;
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        btn.classList.toggle('text-sky-600', show);
      });
    });
  </script>
</body>
</html>

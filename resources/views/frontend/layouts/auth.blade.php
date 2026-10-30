@php
  $locale = str_replace('_', '-', app()->getLocale());
  $isRtl = in_array(app()->getLocale(), ['ar'], true);
  $hasBranding = $__env->hasSection('branding');
  $isSplitAuth = trim($__env->yieldContent('auth-split-screen')) === 'true';
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
    @keyframes auth-panel-in { from { opacity: 0; transform: translateX(-12px); } to { opacity: 1; transform: none; } }
    [dir="rtl"] .auth-panel-in { animation-name: auth-panel-in-rtl; }
    @keyframes auth-panel-in-rtl { from { opacity: 0; transform: translateX(12px); } to { opacity: 1; transform: none; } }
    .auth-panel-in { animation: auth-panel-in .7s cubic-bezier(.22, 1, .36, 1) both; }
    @media (prefers-reduced-motion: reduce) { .auth-bg, .auth-rise, .auth-panel-in { animation: none; } }
  </style>
</head>
<body class="bg-slate-900 text-slate-900 antialiased dark:text-[var(--hb-text)]">
  @if ($isSplitAuth)
    <div class="min-h-svh bg-white lg:grid lg:h-svh lg:min-h-0 lg:grid-cols-2 lg:overflow-hidden">
      <aside class="relative isolate overflow-hidden bg-slate-950 px-6 py-7 text-white sm:px-10 lg:flex lg:h-svh lg:flex-col lg:justify-between lg:px-14 lg:py-10" aria-label="{{ __('auth.login.branding_aria') }}">
        <div class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(ellipse_at_18%_15%,rgba(14,165,233,0.3),transparent_42%),linear-gradient(145deg,#020617,#0f172a_55%,#164e63)]"></div>
        <div class="pointer-events-none absolute -bottom-40 -end-24 -z-10 size-[30rem] rounded-full border border-white/10"></div>
        <div class="pointer-events-none absolute -bottom-24 -end-8 -z-10 size-[22rem] rounded-full border border-white/10"></div>
        
        <div>
          <a href="{{ route('frontend.home') }}" class="inline-flex w-36 rounded-lg bg-white p-2" aria-label="TalentBridge home">
            <img src="{{ asset('images/brand/logo-full.png') }}" alt="TalentBridge" class="h-auto w-full">
          </a>
          <div class="mt-8 max-w-xl sm:mt-12 lg:mt-20">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-sky-300">{{ __('auth.brand_tagline') }}</p>
            <h2 class="mt-4 text-3xl font-bold leading-tight tracking-tight sm:text-4xl lg:text-5xl">{{ __('auth.brand_headline') }}</h2>
            <p class="mt-4 max-w-lg text-sm leading-7 text-slate-300 sm:text-base">{{ __('auth.brand_copy') }}</p>
            <ul class="mt-6 grid gap-3 text-sm text-slate-200 sm:grid-cols-2 lg:grid-cols-1">
              @foreach (__('auth.brand_points') as $point)
                <li class="flex items-center gap-3">
                  <span class="grid size-6 shrink-0 place-items-center rounded-full bg-sky-400/15 text-sky-300" aria-hidden="true">✓</span>
                  <span>{{ $point }}</span>
                </li>
              @endforeach
            </ul>
          </div>
        </div>
        <p class="mt-8 hidden text-xs text-slate-400 lg:block">&copy; {{ date('Y') }} TalentBridge. {{ __('auth.pitch.rights') }}</p>
      </aside>

      <section class="flex min-h-svh min-w-0 flex-col bg-slate-50 px-5 py-5 sm:px-8 lg:h-svh lg:min-h-0 lg:overflow-hidden lg:px-12 lg:py-6">
        <header class="mx-auto flex w-full max-w-xl items-center justify-between gap-3">
          <a href="{{ route('frontend.home') }}" class="text-sm font-semibold text-slate-600 transition hover:text-slate-950">{{ __('auth.back_home') }}</a>
          <div class="flex items-center gap-2">
            @include('frontend.partials.theme-switch')
            @include('frontend.partials.language-switch')
          </div>
        </header>

        <main class="flex flex-1 items-start justify-center py-6 sm:py-8 lg:min-h-0 lg:overflow-y-auto lg:py-5">
          <div class="auth-rise my-auto w-full @yield('auth-card-width', 'max-w-xl')">
            @if (session('status'))
              <div class="mb-4 rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800" role="status">{{ session('status') }}</div>
            @endif
            @if (session('success_key'))
              <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">{{ __(session('success_key'), session('success_replace', [])) }}</div>
            @elseif (session('success'))
              <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">{{ session('success') }}</div>
            @endif
            @if ($errors->has('error'))
              <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">{{ $errors->first('error') }}</div>
            @endif

            @yield('content')
          </div>
        </main>

        <p class="mx-auto w-full max-w-xl pb-2 text-center text-xs text-slate-400 lg:hidden">&copy; {{ date('Y') }} TalentBridge. {{ __('auth.pitch.rights') }}</p>
      </section>
    </div>
  @else
    <div class="relative isolate min-h-svh overflow-hidden bg-gradient-to-br from-sky-700 via-sky-500 to-slate-800">
      <img src="{{ asset('images/halls/talentbridge-hero.webp') }}" alt="" class="auth-bg absolute inset-0 -z-20 h-full w-full object-cover" onerror="this.style.display='none'">
      <div class="absolute inset-0 -z-10 bg-gradient-to-b from-slate-900/70 via-sky-900/55 to-slate-900/85 dark:from-slate-950/80 dark:via-sky-950/70 dark:to-slate-950/90"></div>

      <div class="mx-auto flex min-h-svh max-w-5xl flex-col px-5 py-6 sm:px-8">
        {{-- Top bar --}}
        <header class="flex items-center justify-between gap-3">
          <a href="{{ route('frontend.home') }}" class="text-sm font-semibold text-white/80 transition hover:text-white">&larr; {{ __('auth.back_home') }}</a>
          <div class="flex items-center gap-2">
            @include('frontend.partials.theme-switch')
            @include('frontend.partials.language-switch')
          </div>
        </header>

        @if ($hasBranding)
          {{-- Split branding + form (Welcome Back Login) --}}
          <main class="flex flex-1 flex-col justify-center py-8 lg:py-10">
            <div class="grid items-stretch gap-6 lg:grid-cols-2 lg:gap-0 lg:overflow-hidden lg:rounded-[2rem] lg:bg-white/10 lg:shadow-2xl lg:shadow-slate-950/40 lg:ring-1 lg:ring-white/15 lg:backdrop-blur-sm dark:lg:bg-slate-950/40">
              <aside class="auth-panel-in order-2 hidden flex-col justify-between p-8 text-white lg:order-1 lg:flex lg:p-10 xl:p-12" aria-label="{{ __('auth.login.branding_aria') }}">
                @yield('branding')
              </aside>

              <div class="auth-rise order-1 flex items-center justify-center lg:order-2 lg:bg-[var(--hb-surface)] lg:p-8 xl:p-10">
                <div class="w-full max-w-md rounded-3xl bg-[var(--hb-surface)] p-7 text-[var(--hb-text)] shadow-2xl shadow-slate-950/30 sm:p-10 lg:rounded-none lg:bg-transparent lg:p-0 lg:shadow-none">
                  <a href="{{ route('frontend.home') }}" class="mx-auto mb-6 block w-52 sm:w-56 lg:mx-0" aria-label="TalentBridge home">
                    <img src="{{ asset('images/brand/logo-full.png') }}" alt="TalentBridge" class="h-auto w-full">
                  </a>

                  @include('frontend.account.auth.partials.flash')

                  @yield('content')
                </div>
              </div>
            </div>

            {{-- Mobile branding strip --}}
            <section class="auth-rise auth-rise-2 mt-8 space-y-3 text-center text-white lg:hidden" aria-label="{{ __('auth.login.branding_aria') }}">
              @yield('branding_mobile')
            </section>
          </main>
        @else
          {{-- Centered card (register / admin login) --}}
          <main class="flex flex-1 flex-col items-center justify-center py-8">
            <div class="auth-rise w-full max-w-md rounded-3xl bg-white p-7 shadow-2xl shadow-slate-950/30 sm:p-10">
              <a href="{{ route('frontend.home') }}" class="mx-auto mb-6 block w-52 sm:w-56" aria-label="TalentBridge home">
                <img src="{{ asset('images/brand/logo-full.png') }}" alt="TalentBridge" class="h-auto w-full">
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

            @unless (trim($__env->yieldContent('auth-hide-pitch')))
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
            @endunless
          </main>
        @endif

        <p class="text-center text-xs text-white/60">&copy; {{ date('Y') }} TalentBridge. {{ __('auth.pitch.rights') }}</p>
      </div>
    </div>
  @endif

  <script>
    (function () {
      var showLabel = @json(__('auth.login.show_password'));
      var hideLabel = @json(__('auth.login.hide_password'));
      document.querySelectorAll('[data-toggle-password]').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var input = document.getElementById(btn.getAttribute('data-toggle-password'));
          if (!input) return;
          var show = input.type === 'password';
          input.type = show ? 'text' : 'password';
          btn.setAttribute('aria-label', show ? hideLabel : showLabel);
          btn.setAttribute('aria-pressed', show ? 'true' : 'false');
          btn.classList.toggle('text-sky-600', show);
        });
      });
    })();
  </script>
</body>
</html>

@php
    $locale = str_replace('_', '-', app()->getLocale());
    $isRtl = in_array(app()->getLocale(), ['ar'], true);
@endphp
<!doctype html>
<html lang="{{ $locale }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  {{-- العنوان الموحد كما اتفقنا --}}
  <title>@yield('title', 'ACUD Management System')</title>
  
  @include('frontend.partials.theme-boot')
  
  {{-- خط Cairo للعربي و Inter للإنغليزي --}}
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  @include('frontend.partials.js-i18n')
</head>
<body class="bg-[#0D111B] text-slate-300 font-['Cairo','Inter',sans-serif]" @auth style="--hall-nav: 3rem" @endauth>
  {{-- أدوات التحكم باللغة والثيم --}}
  <div class="pointer-events-none fixed inset-x-0 top-0 z-[60] flex justify-end p-3 sm:p-4">
    <div class="pointer-events-auto flex items-center gap-2">
      @include('frontend.partials.theme-switch')
      @include('frontend.partials.language-switch')
    </div>
  </div>

  @auth
    @unless (View::hasSection('no-nav'))
      @include('partials.hall-nav')
    @endunless
  @endauth
  
  @yield('content')
</body>
</html>
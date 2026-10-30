@if (session('status'))
  <div class="mb-5 rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800 dark:border-sky-800 dark:bg-sky-950/50 dark:text-sky-200" role="status">{{ session('status') }}</div>
@endif

@if (session('success_key'))
  <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200" role="status">{{ __(session('success_key'), session('success_replace', [])) }}</div>
@elseif (session('success'))
  <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200" role="status">{{ session('success') }}</div>
@endif

@if ($errors->has('error'))
  <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300" role="alert">{{ $errors->first('error') }}</div>
@endif

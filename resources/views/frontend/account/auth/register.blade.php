@extends('frontend.layouts.auth')

@section('title', __('auth.register.page_title'))

@php
    $selected = old('account_type', request('type', 'candidate'));
    $selected = in_array($selected, ['candidate', 'company'], true) ? $selected : 'candidate';
@endphp

@section('auth-card-width', 'max-w-xl')
@section('auth-split-screen', 'true')

@section('content')
    <p class="text-xs font-bold uppercase tracking-[0.18em] text-sky-600">{{ __('auth.register.eyebrow') }}</p>
    <h1 class="mt-2 text-2xl font-black tracking-tight">{{ __('auth.register.title') }}</h1>
    <p class="mt-1 text-sm leading-5 text-slate-600">{{ __('auth.register.subtitle') }}</p>

    <form
        method="POST"
        action="{{ route('frontend.account.register.submit') }}"
        class="mt-5 space-y-4"
        aria-label="{{ __('auth.register.form_aria') }}"
        data-register-form
    >
        @csrf

        <fieldset>
            <legend class="mb-2 block text-sm font-semibold text-slate-700">{{ __('auth.register.account_type_aria') }}</legend>
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach ([
                    'candidate' => ['title' => __('auth.register.looking_job'), 'text' => __('auth.register.continue_candidate')],
                    'company' => ['title' => __('auth.register.hiring'), 'text' => __('auth.register.continue_company')],
                ] as $value => $type)
                    <label class="cursor-pointer">
                        <input
                            type="radio"
                            name="account_type"
                            value="{{ $value }}"
                            class="peer sr-only"
                            @checked($selected === $value)
                            required
                        >
                        <span class="flex h-full min-w-0 items-center gap-3 rounded-xl border border-slate-300 bg-white p-3 transition peer-checked:border-sky-500 peer-checked:bg-sky-50 peer-checked:ring-4 peer-checked:ring-sky-500/15 peer-focus-visible:ring-4 peer-focus-visible:ring-sky-500/30">
                            <span class="grid size-8 shrink-0 place-items-center rounded-full bg-sky-100 text-xs font-bold text-sky-700" aria-hidden="true">{{ $loop->first ? '01' : '02' }}</span>
                            <span>
                                <span class="block text-sm font-bold text-slate-800">{{ $type['title'] }}</span>
                                <span class="mt-1 block text-xs leading-5 text-slate-500">{{ $type['text'] }}</span>
                            </span>
                        </span>
                    </label>
                @endforeach
            </div>
            @error('account_type')
                <p class="mt-1.5 text-sm text-red-600" role="alert">{{ $message }}</p>
            @enderror
        </fieldset>

        <div class="grid gap-4 sm:grid-cols-2">
            <x-auth-field dense name="name" :label="__('auth.register.full_name')" icon="user" :placeholder="__('auth.register.full_name')" autocomplete="name" required />
            <x-auth-field dense name="email" :label="__('auth.register.email_address')" type="email" icon="mail" placeholder="you@example.com" autocomplete="email" required />
        </div>

        <div
            id="company-fields"
            class="{{ $selected === 'company' ? '' : 'hidden ' }}overflow-hidden space-y-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 transition-[max-height,opacity] duration-300 ease-in-out"
            @if ($selected !== 'company') hidden @endif
            @if ($selected !== 'company') inert @endif
            aria-hidden="{{ $selected !== 'company' ? 'true' : 'false' }}"
            data-expanded="{{ $selected === 'company' ? 'true' : 'false' }}"
        >
            <p class="text-sm font-semibold text-slate-800">{{ __('auth.register.hiring') }}</p>
            <x-auth-field
                name="company_name"
                :label="__('auth.register.company_name')"
                icon="user"
                :placeholder="__('auth.register.company_name')"
                autocomplete="organization"
                :required="$selected === 'company'"
            />
            <div class="grid gap-4 sm:grid-cols-2">
                <x-auth-field dense name="industry" :label="__('auth.register.industry')" :placeholder="__('auth.register.industry')" />
                <x-auth-field dense name="website" :label="__('auth.register.website')" type="url" placeholder="https://example.com" autocomplete="url" />
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <x-auth-field
                dense
                name="password"
                :label="__('auth.register.create_password')"
                type="password"
                icon="lock"
                placeholder="••••••••"
                autocomplete="new-password"
                required
                minlength="8"
                :hint="__('auth.register.password_hint')"
                data-register-password
                data-mismatch-message="{{ __('auth.register.password_mismatch') }}"
            />
            <x-auth-field
                dense
                name="password_confirmation"
                :label="__('auth.register.confirm_password')"
                type="password"
                icon="lock"
                placeholder="••••••••"
                autocomplete="new-password"
                required
                minlength="8"
                data-register-password-confirmation
            />
        </div>

        <section aria-labelledby="password-strength-title" class="rounded-xl border border-slate-200 bg-slate-50 p-3">
            <div class="flex items-center justify-between gap-3">
                <h2 id="password-strength-title" class="text-sm font-semibold text-slate-800">{{ __('auth.register.password_strength') }}</h2>
                <span class="text-xs font-semibold text-slate-500" data-password-strength-label aria-live="polite">{{ __('auth.register.strength_weak') }}</span>
            </div>
            <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-200" role="progressbar" aria-label="{{ __('auth.register.password_strength') }}" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                <span class="block h-full w-0 rounded-full bg-red-500 transition-all" data-password-strength-bar></span>
            </div>
            <ul class="mt-3 grid gap-1 text-xs text-slate-500 sm:grid-cols-2" aria-label="{{ __('auth.register.password_requirements') }}">
                <li data-password-rule="length"><span aria-hidden="true">○</span> {{ __('auth.register.strength_length') }}</li>
                <li data-password-rule="case"><span aria-hidden="true">○</span> {{ __('auth.register.strength_case') }}</li>
                <li data-password-rule="number"><span aria-hidden="true">○</span> {{ __('auth.register.strength_number') }}</li>
                <li data-password-rule="symbol"><span aria-hidden="true">○</span> {{ __('auth.register.strength_symbol') }}</li>
            </ul>
            <p class="mt-2 text-xs text-red-600" data-password-match hidden role="status"></p>
        </section>

        <div class="space-y-1">
            <label class="flex items-start gap-3 text-sm leading-6 text-slate-700">
                <input
                    type="checkbox"
                    name="terms_accepted"
                    value="1"
                    class="mt-1 size-4 shrink-0 rounded border-slate-300 text-sky-600 focus:ring-sky-500"
                    @checked(old('terms_accepted'))
                    required
                    @error('terms_accepted') aria-invalid="true" @enderror
                >
                <span>
                    {{ __('auth.register.terms_prefix') }}
                    <span class="font-semibold text-sky-700">{{ __('auth.register.terms_of_service') }}</span>
                    {{ __('auth.register.terms_and') }}
                    <span class="font-semibold text-sky-700">{{ __('auth.register.privacy_notice') }}</span>
                </span>
            </label>
            @error('terms_accepted')
                <p class="mt-1.5 text-sm text-red-600" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <label class="flex items-start gap-3 text-sm leading-5 text-slate-600">
            <input
                type="checkbox"
                name="marketing_opt_in"
                value="1"
                class="mt-1 size-4 shrink-0 rounded border-slate-300 text-sky-600 focus:ring-sky-500"
                @checked(old('marketing_opt_in'))
            >
            <span>{{ __('auth.register.marketing') }}</span>
        </label>
        @error('marketing_opt_in')
            <p class="text-sm text-red-600" role="alert">{{ $message }}</p>
        @enderror

        <button
            type="submit"
            class="w-full rounded-full bg-sky-500 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-sky-500/25 transition hover:-translate-y-0.5 hover:bg-sky-600 focus:outline-none focus-visible:ring-4 focus-visible:ring-sky-500/30 disabled:cursor-wait disabled:opacity-70"
            data-register-submit
            data-submit-candidate="{{ __('auth.register.submit_candidate') }}"
            data-submit-company="{{ __('auth.register.submit_company') }}"
            data-submitting="{{ __('auth.register.submitting') }}"
        >
            {{ $selected === 'company' ? __('auth.register.submit_company') : __('auth.register.submit_candidate') }}
        </button>
    </form>

    <p class="mt-5 text-center text-sm text-slate-500">
        {{ __('auth.register.have_account') }}
        <a href="{{ route('frontend.account.login') }}" class="font-bold text-sky-600 hover:underline">{{ __('auth.register.sign_in') }}</a>
    </p>

    <script>
        (() => {
            const form = document.querySelector('[data-register-form]');
            if (!form) return;

            const companyFields = document.getElementById('company-fields');
            const companyName = document.getElementById('company_name');
            const submit = form.querySelector('[data-register-submit]');
            const password = form.querySelector('[data-register-password]');
            const confirmation = form.querySelector('[data-register-password-confirmation]');
            const meter = form.querySelector('[role="progressbar"]');
            const meterBar = form.querySelector('[data-password-strength-bar]');
            const strengthLabel = form.querySelector('[data-password-strength-label]');
            const matchMessage = form.querySelector('[data-password-match]');
            const strengthLabels = [
                @json(__('auth.register.strength_weak')),
                @json(__('auth.register.strength_fair')),
                @json(__('auth.register.strength_good')),
                @json(__('auth.register.strength_strong')),
            ];
            const mismatchMessage = password.dataset.mismatchMessage;

            function syncAccountType() {
                const selected = form.querySelector('input[name="account_type"]:checked')?.value || 'candidate';
                const isCompany = selected === 'company';
                const isExpanded = companyFields.dataset.expanded === 'true';
                companyName.required = isCompany;
                submit.textContent = isCompany ? submit.dataset.submitCompany : submit.dataset.submitCandidate;

                if (isCompany && !isExpanded) {
                    companyFields.hidden = false;
                    companyFields.inert = false;
                    companyFields.classList.remove('hidden');
                    companyFields.style.maxHeight = '0px';
                    companyFields.style.opacity = '0';
                    requestAnimationFrame(() => {
                        companyFields.style.maxHeight = `${companyFields.scrollHeight}px`;
                        companyFields.style.opacity = '1';
                    });
                    companyFields.addEventListener('transitionend', (event) => {
                        if (event.propertyName === 'max-height' && companyFields.dataset.expanded === 'true') {
                            companyFields.style.maxHeight = 'none';
                        }
                    }, { once: true });
                } else if (!isCompany && isExpanded) {
                    companyFields.style.maxHeight = `${companyFields.scrollHeight}px`;
                    companyFields.style.opacity = '1';
                    companyFields.inert = true;
                    requestAnimationFrame(() => {
                        companyFields.style.maxHeight = '0px';
                        companyFields.style.opacity = '0';
                    });
                    companyFields.addEventListener('transitionend', (event) => {
                        if (event.propertyName === 'max-height' && companyFields.dataset.expanded === 'false') {
                            companyFields.hidden = true;
                            companyFields.classList.add('hidden');
                        }
                    }, { once: true });
                }

                companyFields.dataset.expanded = String(isCompany);
                companyFields.setAttribute('aria-hidden', String(!isCompany));
            }

            function syncPasswordStrength() {
                const value = password.value;
                const checks = {
                    length: value.length >= 8,
                    case: /[a-z]/.test(value) && /[A-Z]/.test(value),
                    number: /\d/.test(value),
                    symbol: /[^A-Za-z0-9]/.test(value),
                };
                const score = Object.values(checks).filter(Boolean).length;
                const percent = score * 25;

                form.querySelectorAll('[data-password-rule]').forEach((item) => {
                    const met = checks[item.dataset.passwordRule];
                    item.classList.toggle('font-semibold', met);
                    item.classList.toggle('text-emerald-700', met);
                    item.querySelector('span').textContent = met ? '✓' : '○';
                });

                meter.setAttribute('aria-valuenow', String(percent));
                meterBar.style.width = `${percent}%`;
                meterBar.classList.toggle('bg-red-500', score < 2);
                meterBar.classList.toggle('bg-amber-500', score === 2);
                meterBar.classList.toggle('bg-sky-500', score === 3);
                meterBar.classList.toggle('bg-emerald-500', score === 4);
                strengthLabel.textContent = strengthLabels[Math.max(0, score - 1)];

                syncPasswordMatch();
            }

            function syncPasswordMatch() {
                const hasConfirmation = confirmation.value !== '';
                const matches = password.value === confirmation.value;
                confirmation.setCustomValidity(hasConfirmation && !matches ? mismatchMessage : '');
                matchMessage.hidden = !hasConfirmation || matches;
                matchMessage.textContent = !matches ? mismatchMessage : '';
            }

            form.querySelectorAll('input[name="account_type"]').forEach((input) => {
                input.addEventListener('change', syncAccountType);
            });
            password.addEventListener('input', syncPasswordStrength);
            confirmation.addEventListener('input', syncPasswordMatch);
            form.addEventListener('submit', () => {
                submit.disabled = true;
                submit.textContent = submit.dataset.submitting;
            });

            syncAccountType();
            syncPasswordStrength();
        })();
    </script>
@endsection

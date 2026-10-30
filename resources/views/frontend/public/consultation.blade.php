@extends('frontend.layouts.public')

@section('title', __('public.consultation.page_title'))

@section('content')
    <section class="relative overflow-hidden bg-hb-page">
        <div class="pointer-events-none absolute -top-32 start-1/2 h-[26rem] w-[52rem] -translate-x-1/2 rounded-full bg-hb-blue/10 blur-3xl" aria-hidden="true"></div>
        <div class="hb-container relative py-12 lg:py-20">
            <p class="inline-flex rounded-full border border-hb-border bg-hb-surface px-3.5 py-1.5 text-xs font-semibold text-hb-blue uppercase">{{ __('public.consultation.eyebrow') }}</p>
            <div class="mt-4 grid gap-10 lg:grid-cols-[minmax(0,1fr)_360px]">
                <div>
                    <h1 class="text-4xl font-semibold tracking-tight text-hb-navy sm:text-5xl">{{ __('public.consultation.title') }}</h1>
                    <p class="mt-4 max-w-2xl text-base leading-relaxed text-hb-muted">{{ __('public.consultation.intro') }}</p>

                    <form id="consultation-form" method="POST" action="{{ route('frontend.consultation.submit') }}" class="mt-8 hb-surface space-y-5 p-6 shadow-hb-md lg:p-9" novalidate data-public-form>
                        @csrf
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="text-lg font-semibold text-hb-navy">{{ __('public.consultation.form_title') }}</h2>
                            <span class="rounded-full bg-hb-pending-soft px-2 py-0.5 text-[10px] font-semibold text-hb-pending uppercase">{{ __('public.contact.badge') }}</span>
                        </div>
                        @guest
                            <div class="grid gap-5 sm:grid-cols-2">
                                <div>
                                    <label for="full_name" class="hb-label">{{ __('public.contact.name') }} <span class="text-hb-error">*</span></label>
                                    <input id="full_name" name="full_name" type="text" required autocomplete="name" value="{{ old('full_name') }}" class="hb-input mt-1.5" aria-describedby="full_name-error">
                                    <p id="full_name-error" class="mt-1 text-xs text-hb-error" data-error-for="full_name">{{ $errors->first('full_name') }}</p>
                                </div>
                                <div>
                                    <label for="work_email" class="hb-label">{{ __('public.contact.email') }} <span class="text-hb-error">*</span></label>
                                    <input id="work_email" name="work_email" type="email" required autocomplete="email" value="{{ old('work_email') }}" class="hb-input mt-1.5" aria-describedby="work_email-error">
                                    <p id="work_email-error" class="mt-1 text-xs text-hb-error" data-error-for="work_email">{{ $errors->first('work_email') }}</p>
                                </div>
                            </div>
                        @else
                            <div class="rounded-lg bg-hb-page p-4 text-sm text-hb-text">
                                <p class="font-semibold">{{ auth()->user()->name }}</p>
                                <p class="mt-1 text-hb-muted">{{ auth()->user()->email }}</p>
                            </div>
                        @endguest
                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label for="company" class="hb-label">{{ __('public.contact.company') }} <span class="text-hb-error">*</span></label>
                                <input id="company" name="company" type="text" required autocomplete="organization" value="{{ old('company') }}" class="hb-input mt-1.5" aria-describedby="company-error">
                                <p id="company-error" class="mt-1 text-xs text-hb-error" data-error-for="company">{{ $errors->first('company') }}</p>
                            </div>
                            <div>
                                <label for="role_title" class="hb-label">{{ __('public.consultation.role') }} <span class="text-hb-error">*</span></label>
                                <input id="role_title" name="role_title" type="text" required value="{{ old('role_title') }}" class="hb-input mt-1.5" aria-describedby="role_title-error">
                                <p id="role_title-error" class="mt-1 text-xs text-hb-error" data-error-for="role_title">{{ $errors->first('role_title') }}</p>
                            </div>
                            <div>
                                <label for="hiring_model" class="hb-label">{{ __('public.consultation.hiring_model') }} <span class="text-hb-error">*</span></label>
                                <select id="hiring_model" name="hiring_model" required class="hb-input mt-1.5" aria-describedby="hiring_model-error">
                                    <option value="">{{ __('common.select_placeholder') }}</option>
                                    @foreach (__('public.contact.hiring_options') as $key => $label)
                                        <option value="{{ $key }}" @selected(old('hiring_model') === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <p id="hiring_model-error" class="mt-1 text-xs text-hb-error" data-error-for="hiring_model">{{ $errors->first('hiring_model') }}</p>
                            </div>
                            <div>
                                <label for="expected_hires" class="hb-label">{{ __('public.consultation.expected_hires') }}</label>
                                <select id="expected_hires" name="expected_hires" class="hb-input mt-1.5">
                                    <option value="">{{ __('common.select_placeholder') }}</option>
                                    @foreach (__('public.contact.hire_counts') as $count)
                                        <option value="{{ $count }}" @selected(old('expected_hires') === $count)>{{ $count }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div>
                            <label for="needs" class="hb-label">{{ __('public.consultation.needs') }} <span class="text-hb-error">*</span></label>
                            <textarea id="needs" name="needs" rows="5" required class="hb-input mt-1.5" aria-describedby="needs-error" placeholder="{{ __('public.consultation.needs_placeholder') }}">{{ old('needs') }}</textarea>
                            <p id="needs-error" class="mt-1 text-xs text-hb-error" data-error-for="needs">{{ $errors->first('needs') }}</p>
                        </div>
                        <div>
                            <label for="preferred_contact" class="hb-label">{{ __('public.consultation.preferred_contact') }}</label>
                            <select id="preferred_contact" name="preferred_contact" class="hb-input mt-1.5 sm:max-w-xs">
                                <option value="">{{ __('common.select_placeholder') }}</option>
                                @foreach (__('public.contact.contact_options') as $option)
                                    <option value="{{ $option }}" @selected(old('preferred_contact') === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="flex items-start gap-2 text-sm">
                                <input id="privacy_consent" name="privacy_consent" type="checkbox" value="1" required @checked(old('privacy_consent')) class="hb-checkbox mt-1" aria-describedby="privacy_consent-error">
                                <span>{{ __('public.contact.privacy_consent') }} <span class="text-hb-error">*</span></span>
                            </label>
                            <p id="privacy_consent-error" class="mt-1 text-xs text-hb-error" data-error-for="privacy_consent">{{ $errors->first('privacy_consent') }}</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            <x-button type="submit" variant="primary" data-submit-button>{{ __('public.consultation.submit') }}</x-button>
                            <p class="text-xs text-hb-muted">{{ __('common.required_fields') }}</p>
                        </div>
                        <p class="rounded-lg border border-hb-pending/40 bg-hb-pending-soft px-4 py-3 text-sm text-hb-pending" role="status">{{ session('submission_unavailable', __('public.consultation.unavailable')) }}</p>
                    </form>
                </div>
                <aside class="hb-surface h-fit p-6 lg:sticky lg:top-28">
                    <h2 class="text-lg font-semibold text-hb-navy">{{ __('public.contact.next_title') }}</h2>
                    <ol class="mt-4 space-y-4 text-sm text-hb-muted">
                        @foreach (__('public.contact.next_steps') as $step)
                            <li class="flex gap-3"><span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-hb-info-soft text-xs font-bold text-hb-blue">{{ $loop->iteration }}</span><span><span class="font-semibold text-hb-navy">{{ $step['title'] }}</span><br>{{ $step['body'] }}</span></li>
                        @endforeach
                    </ol>
                    <p class="mt-4 text-xs text-hb-muted">{{ __('public.contact.next_footer') }}</p>
                </aside>
            </div>
            <p class="mt-6 text-sm text-hb-muted"><a href="{{ route('frontend.contact') }}" class="font-semibold text-hb-blue hover:underline">{{ __('public.consultation.general_contact_link') }}</a></p>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('consultation-form');
            if (!form) return;
            form.addEventListener('submit', (event) => {
                let valid = true;
                form.querySelectorAll('[required]').forEach((field) => {
                    let message = '';
                    if (field.type === 'checkbox' && !field.checked) message = @json(__('js.validation.consent_continue'));
                    else if (field.type === 'email' && field.value.trim() && !field.validity.valid) message = @json(__('public.contact.validation_work_email'));
                    else if (field.type !== 'checkbox' && !field.value.trim()) message = @json(__('js.validation.required'));
                    const error = form.querySelector(`[data-error-for="${field.name}"]`);
                    if (error) error.textContent = message;
                    field.setAttribute('aria-invalid', message ? 'true' : 'false');
                    valid = valid && !message;
                });
                if (!valid) {
                    event.preventDefault();
                    form.querySelector('[aria-invalid="true"]')?.focus();
                    return;
                }
                const button = form.querySelector('[data-submit-button]');
                if (button) {
                    button.disabled = true;
                    button.setAttribute('aria-busy', 'true');
                }
            });
        });
    </script>
@endpush

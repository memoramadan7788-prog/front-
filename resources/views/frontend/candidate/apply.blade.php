@extends('frontend.layouts.app')

@section('title', __('candidate.apply_form.page_title'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('candidate.apply_form.title') }}</h1>
    @if (filled(data_get($job ?? [], 'title')))
        <p class="mt-1 text-sm text-hb-muted">
            <span class="font-medium text-hb-navy">{{ data_get($job, 'title') }}</span>
            @php
                $company = data_get($job, 'company.name', data_get($job, 'company_name'));
            @endphp
            @if (filled($company))
                <span aria-hidden="true"> · </span>{{ $company }}
            @endif
        </p>
    @endif
@endsection

@section('content')
@php
    $cvs = \App\Support\Rows::from($cvs ?? []);
    $hasResumes = ! empty($cvs);
    $vacancyId = $vacancyId ?? data_get($job ?? [], 'id');
    $uploadHref = route('frontend.candidate.resume.upload', array_filter([
        'vacancy_id' => $vacancyId,
    ]));
    $cancelHref = filled($vacancyId)
        ? route('frontend.candidate.vacancies.show', ['vacancyId' => $vacancyId])
        : route('frontend.candidate.vacancies.index');
    $questions = $questions ?? data_get($job ?? [], 'questions', []);
@endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 max-w-5xl mx-auto" x-data="{ step: 1, consent: {{ old('consent') ? 'true' : 'false' }}, talentPoolConsent: false }">
    
    <!-- 1. Role Summary Card (بطاقة ملخص الدور) -->
    <div class="lg:col-span-1">
        <x-card class="sticky top-6">
            <x-slot:header>
                <h3 class="text-base font-bold text-hb-navy">{{ __('candidate.apply_form.role_summary') ?? 'ملخص الوظيفة' }}</h3>
            </x-slot:header>
            
            <div class="space-y-3 text-sm text-hb-muted">
                <div>
                    <span class="block text-xs font-medium text-hb-navy">{{ __('candidate.apply_form.job_title') ?? 'المسمى الوظيفي' }}</span>
                    <p class="font-semibold text-hb-navy mt-0.5">{{ data_get($job, 'title', '-') }}</p>
                </div>
                @if(filled(data_get($job, 'company.name', data_get($job, 'company_name'))))
                <div>
                    <span class="block text-xs font-medium text-hb-navy">{{ __('candidate.apply_form.company') ?? 'الشركة' }}</span>
                    <p class="mt-0.5">{{ data_get($job, 'company.name', data_get($job, 'company_name')) }}</p>
                </div>
                @endif
                @if(filled(data_get($job, 'type')))
                <div>
                    <span class="block text-xs font-medium text-hb-navy">{{ __('candidate.apply_form.employment_type') ?? 'نوع الدوام' }}</span>
                    <p class="mt-0.5">{{ data_get($job, 'type') }}</p>
                </div>
                @endif
                @if(filled(data_get($job, 'location')))
                <div>
                    <span class="block text-xs font-medium text-hb-navy">{{ __('candidate.apply_form.location') ?? 'الموقع' }}</span>
                    <p class="mt-0.5">{{ data_get($job, 'location') }}</p>
                </div>
                @endif
            </div>
        </x-card>
    </div>

    <!-- 2. Application Form Stepper (النموذج متعدد الخطوات) -->
    <div class="lg:col-span-2">
        <x-card>
            <x-slot:header>
                <div>
                    <h2 class="text-lg font-bold text-hb-navy">{{ __('candidate.apply_form.title') }}</h2>
                    <!-- Stepper Header (مؤشر الخطوات) -->
                    <div class="mt-4 flex items-center justify-between border-t border-hb-border pt-4">
                        <div class="flex items-center space-x-2 space-x-reverse">
                            <span :class="step >= 1 ? 'bg-hb-navy text-white' : 'bg-gray-200 text-hb-muted'" class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">1</span>
                            <span class="text-xs font-medium" :class="step >= 1 ? 'text-hb-navy font-bold' : 'text-hb-muted'">الملف الشخصي</span>
                        </div>
                        <div class="flex-1 h-0.5 mx-2 bg-gray-200"></div>
                        <div class="flex items-center space-x-2 space-x-reverse">
                            <span :class="step >= 2 ? 'bg-hb-navy text-white' : 'bg-gray-200 text-hb-muted'" class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">2</span>
                            <span class="text-xs font-medium" :class="step >= 2 ? 'text-hb-navy font-bold' : 'text-hb-muted'">الأسئلة والراتب</span>
                        </div>
                        <div class="flex-1 h-0.5 mx-2 bg-gray-200"></div>
                        <div class="flex items-center space-x-2 space-x-reverse">
                            <span :class="step >= 3 ? 'bg-hb-navy text-white' : 'bg-gray-200 text-hb-muted'" class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">3</span>
                            <span class="text-xs font-medium" :class="step >= 3 ? 'text-hb-navy font-bold' : 'text-hb-muted'">المراجعة والموافقة</span>
                        </div>
                    </div>
                </div>
            </x-slot:header>

            @if (! $hasResumes)
                <div class="rounded-xl border border-dashed border-hb-border bg-hb-page/40 px-5 py-8 text-center" role="status">
                    <p class="text-sm font-semibold text-hb-navy">{{ __('candidate.apply_form.no_resume_title') }}</p>
                    <p class="mt-2 text-sm text-hb-muted">{{ __('candidate.apply_form.no_resume_body') }}</p>
                    <div class="mt-5 flex flex-wrap items-center justify-center gap-3">
                        <x-button :href="$uploadHref">{{ __('candidate.apply_form.upload_resume') }}</x-button>
                        <x-button variant="light" :href="$cancelHref">{{ __('candidate.apply_form.cancel') }}</x-button>
                    </div>
                </div>
            @else
                <form
                    method="POST"
                    action="{{ route('frontend.candidate.vacancies.apply.submit', ['vacancyId' => $vacancyId]) }}"
                    class="space-y-5"
                    data-apply-form
                >
                    @csrf

                    <!-- Step 1: Profile & Resume Selection -->
                    <div x-show="step === 1" class="space-y-4">
                        <x-form.select name="cv_id" :label="__('candidate.apply_form.resume')" :required="true" :error="$errors->first('cv_id')">
                            <option value="">{{ __('candidate.apply_form.select_resume') }}</option>
                            @foreach ($cvs as $opt)
                                <option
                                    value="{{ data_get($opt, 'id') }}"
                                    @selected((string) old('cv_id') === (string) data_get($opt, 'id'))
                                >
                                    {{ data_get($opt, 'original_filename', __('candidate.apply_form.resume')) }}
                                </option>
                            @endforeach
                        </x-form.select>

                        <p class="text-xs text-hb-muted">
                            {{ __('candidate.apply_form.need_another_resume') }}
                            <a class="font-semibold text-hb-blue hover:underline" href="{{ $uploadHref }}">{{ __('candidate.apply_form.upload_resume') }}</a>
                        </p>

                        <x-form.textarea
                            name="cover_note"
                            :label="__('candidate.apply_form.cover_note')"
                            :hint="__('candidate.apply_form.cover_note_hint')"
                            rows="4"
                            maxlength="3000"
                            :error="$errors->first('cover_note')"
                        >{{ old('cover_note') }}</x-form.textarea>
                    </div>

                    <!-- Step 2: Expected Salary & Job-Specific Questions -->
                    <div x-show="step === 2" class="space-y-4" style="display: none;">
                        <!-- Expected Salary Field -->
                        <x-form.input
                            type="number"
                            name="expected_salary"
                            :label="__('candidate.apply_form.expected_salary') ?? 'الراتب المتوقع'"
                            :placeholder="__('candidate.apply_form.expected_salary_placeholder') ?? 'أدخل الراتب المتوقع شهرياً'"
                            :value="old('expected_salary')"
                            :error="$errors->first('expected_salary')"
                        />

                        <!-- Dynamic Job-specific questions -->
                        @if(!empty($questions))
                            <div class="space-y-4 pt-2">
                                <h4 class="text-sm font-bold text-hb-navy border-b pb-2">أسئلة خاصة بالوظيفة</h4>
                                @foreach($questions as $index => $q)
                                    <x-form.textarea
                                        name="answers[{{ data_get($q, 'id', $index) }}]"
                                        :label="data_get($q, 'question_text', $q)"
                                        rows="2"
                                        :required="data_get($q, 'is_required', false)"
                                    />
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Step 3: Review & Consents -->
                    <div x-show="step === 3" class="space-y-4" style="display: none;">
                        <div class="rounded-lg bg-hb-page/50 p-4 border border-hb-border text-sm space-y-2 text-hb-navy">
                            <p class="font-semibold">تأكيد ومراجعة الطلب:</p>
                            <p class="text-xs text-hb-muted">يرجى قراءة الموافقة وتأكيدها لإتمام عملية التقديم على الوظيفة.</p>
                        </div>

                        <!-- Processing Consent (موافقة معالجة البيانات) -->
                        <div>
                            <x-form.checkbox
                                name="consent"
                                :label="__('candidate.apply_form.consent') ?? 'أوافق على معالجة بياناتي لغرض هذا التقديم'"
                                value="1"
                                x-model="consent"
                            />
                            @error('consent')
                                <p class="hb-field-error" role="alert">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Talent Pool Consent (موافقة بنك المواهب) -->
                        <div>
                            <x-form.checkbox
                                name="talent_pool_consent"
                                :label="__('candidate.apply_form.talent_pool_consent') ?? 'أوافق على حفظ ملفي في بنك المواهب لترشيحي لفرص مستقبلية'"
                                value="1"
                                x-model="talentPoolConsent"
                            />
                        </div>
                    </div>

                    <!-- Navigation & Actions Bar -->
                    <div class="flex flex-wrap items-center justify-between gap-3 pt-4 border-t border-hb-border">
                        <div class="flex items-center gap-2">
                            <x-button type="button" variant="light" x-show="step > 1" @click="step--">
                                السابق
                            </x-button>
                            <!-- Save Draft Button -->
                            <x-button type="button" variant="light" name="action" value="draft">
                                {{ __('candidate.apply_form.save_draft') ?? 'حفظ كمسودة' }}
                            </x-button>
                        </div>

                        <div class="flex items-center gap-2">
                            <x-button type="button" x-show="step < 3" @click="step++">
                                التالي
                            </x-button>

                            <!-- Submit Button (Disabled until consent is checked) -->
                            <x-button
                                type="submit"
                                x-show="step === 3"
                                ::disabled="!consent"
                                data-apply-submit
                            >
                                {{ __('candidate.apply_form.submit') }}
                            </x-button>

                            <x-button variant="light" :href="$cancelHref">{{ __('candidate.apply_form.cancel') }}</x-button>
                        </div>
                    </div>

                </form>
            @endif
        </x-card>
    </div>
</div>
@endsection
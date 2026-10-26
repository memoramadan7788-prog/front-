@extends('frontend.layouts.app')

@section('title', __('Exam'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ data_get($exam ?? [], 'attempt.exam_title', data_get($exam ?? [], 'exam.title', data_get($exam ?? [], 'title', __('Exam')))) }}</h1>
@endsection

@section('content')
    @php $questions = \App\Support\Rows::from(data_get($exam ?? [], 'questions', data_get($exam ?? [], 'exam.questions', []))); @endphp
    <form method="POST" action="{{ route('frontend.candidate.exams.submit.submit', ['attemptId' => $attemptId ?? null]) }}" class="max-w-3xl space-y-5" onsubmit="return confirm('{{ __('Submit your answers?') }}')">
        @csrf
        @forelse ($questions as $q)
            @php $qid = data_get($q, 'id', $loop->index); $type = data_get($q, 'question_type', 'mcq'); $i = $loop->index; @endphp
            <input type="hidden" name="answers[{{ $i }}][question_id]" value="{{ $qid }}">
            <x-card>
                <x-slot:header>
                    <p class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ __('Question') }} {{ $loop->iteration }} · {{ data_get($q, 'points', 0) }} {{ __('pts') }}</p>
                </x-slot:header>
                <p class="mb-3 text-sm font-medium text-hb-navy">{{ data_get($q, 'content') }}</p>
                @if ($type === 'mcq')
                    @foreach (\App\Support\Rows::from(data_get($q, 'options', [])) as $opt)
                        <label class="mb-2 flex items-start gap-2 text-sm">
                            <input type="radio" class="hb-checkbox mt-0.5" name="answers[{{ $i }}][selected_option_id]" value="{{ data_get($opt, 'id') }}" @checked((string) old('answers.'.$i.'.selected_option_id') === (string) data_get($opt, 'id'))>
                            <span>{{ data_get($opt, 'option_text', data_get($opt, 'content')) }}</span>
                        </label>
                    @endforeach
                @else
                    <textarea name="answers[{{ $i }}][{{ $type === 'coding' ? 'code' : 'answer_text' }}]" rows="{{ $type === 'coding' ? 10 : 5 }}" class="hb-input @if ($type === 'coding') font-mono @endif">{{ old('answers.'.$i.'.'.($type === 'coding' ? 'code' : 'answer_text')) }}</textarea>
                @endif
            </x-card>
        @empty
            <x-alert variant="warning">{{ __('No questions were loaded for this exam.') }}</x-alert>
        @endforelse
        @error('answers')<p class="hb-field-error" role="alert">{{ $message }}</p>@enderror
        @if (count($questions))
            <x-button type="submit">{{ __('Submit answers') }}</x-button>
        @endif
    </form>
@endsection

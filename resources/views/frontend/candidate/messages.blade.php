@extends('frontend.layouts.app')

@section('title', __('candidate.messages.page_title') ?? 'الرسائل والمحادثات')

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('candidate.messages.title') ?? 'الرسائل' }}</h1>
    <p class="mt-1 text-sm text-hb-muted">{{ __('candidate.messages.subtitle') ?? 'تواصل مع مسؤولي التوظيف والشركات بشأن طلباتك' }}</p>
@endsection

@section('content')
@php
    $conversations = \App\Support\Rows::from($conversations ?? []);
    $activeConversation = $activeConversation ?? data_get($conversations, '0');
    $messages = \App\Support\Rows::from(data_get($activeConversation, 'messages', []));
    $applicationContext = data_get($activeConversation, 'application', data_get($activeConversation, 'job'));
    $sendMessageRoute = filled(data_get($activeConversation, 'id')) 
        ? route('frontend.candidate.messages.send', ['conversationId' => data_get($activeConversation, 'id')]) 
        : '#';
@endphp

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 max-w-6xl mx-auto h-[calc(100vh-12rem)] min-h-[550px]">
    
    <!-- 1. Conversations Inbox List (قائمة المحادثات - صندوق الوارد) -->
    <div class="lg:col-span-4 flex flex-col bg-white rounded-xl border border-hb-border overflow-hidden shadow-sm">
        <div class="p-4 border-b border-hb-border bg-hb-page/30">
            <h2 class="text-base font-bold text-hb-navy">{{ __('candidate.messages.inbox') ?? 'الصندوق الوارد' }}</h2>
        </div>
        
        <div class="flex-1 overflow-y-auto divide-y divide-hb-border">
            @forelse ($conversations as $conv)
                @php
                    $isActive = (string) data_get($conv, 'id') === (string) data_get($activeConversation, 'id');
                    $convShowRoute = route('frontend.candidate.messages.show', ['conversationId' => data_get($conv, 'id')]);
                @endphp
                <a href="{{ $convShowRoute }}" class="block p-4 transition hover:bg-hb-page/50 {{ $isActive ? 'bg-hb-page/80 border-r-4 border-hb-navy' : '' }}">
                    <div class="flex items-center justify-between mb-1">
                        <span class="font-semibold text-sm text-hb-navy">{{ data_get($conv, 'company_name', data_get($conv, 'sender_name', 'شركة')) }}</span>
                        <span class="text-xs text-hb-muted">{{ data_get($conv, 'last_message_at', '') }}</span>
                    </div>
                    <p class="text-xs font-medium text-hb-blue truncate mb-1">{{ data_get($conv, 'job_title', '') }}</p>
                    <p class="text-xs text-hb-muted truncate">{{ data_get($conv, 'last_message_preview', __('candidate.messages.no_messages') ?? 'لا توجد رسائل بعد') }}</p>
                </a>
            @empty
                <div class="p-8 text-center text-sm text-hb-muted">
                    {{ __('candidate.messages.no_conversations') ?? 'لا توجد محادثات نشطة حالياً.' }}
                </div>
            @endforelse
        </div>
    </div>

    <!-- 2. Active Conversation & Messages Area (منطقة المحادثة النشطة والرسائل) -->
    <div class="lg:col-span-8 flex flex-col bg-white rounded-xl border border-hb-border overflow-hidden shadow-sm">
        @if(filled($activeConversation))
            <!-- Conversation Header -->
            <div class="p-4 border-b border-hb-border bg-hb-page/30 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-sm text-hb-navy">{{ data_get($activeConversation, 'company_name', __('candidate.messages.conversation')) }}</h3>
                    @if(filled(data_get($activeConversation, 'job_title')))
                        <p class="text-xs text-hb-muted">{{ data_get($activeConversation, 'job_title') }}</p>
                    @endif
                </div>
                @if(filled($applicationContext))
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-hb-navy/10 text-hb-navy">
                        {{ __('candidate.messages.application_linked') ?? 'مرتبط بطلب وظيفة' }}
                    </span>
                @endif
            </div>

            <!-- Messages Scrollable Body -->
            <div class="flex-1 p-4 overflow-y-auto space-y-4 bg-hb-page/10">
                @forelse($messages as $msg)
                    @php
                        $isMe = data_get($msg, 'is_sender_candidate', false);
                    @endphp
                    <div class="flex flex-col {{ $isMe ? 'items-end' : 'items-start' }}">
                        <div class="max-w-[75%] rounded-2xl px-4 py-3 text-sm {{ $isMe ? 'bg-hb-navy text-white rounded-br-none' : 'bg-white border border-hb-border text-hb-navy rounded-bl-none shadow-sm' }}">
                            <p class="leading-relaxed">{{ data_get($msg, 'body') }}</p>
                            
                            <!-- Attachments support -->
                            @if(has_rows(data_get($msg, 'attachments')))
                                <div class="mt-3 pt-2 border-t {{ $isMe ? 'border-white/20' : 'border-hb-border' }} space-y-1">
                                    @foreach(data_get($msg, 'attachments') as $file)
                                        <a href="{{ data_get($file, 'url') }}" target="_blank" class="flex items-center space-x-2 space-x-reverse text-xs underline truncate">
                                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                            <span class="truncate">{{ data_get($file, 'name', __('candidate.messages.attachment')) }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <span class="text-[10px] text-hb-muted mt-1 px-1">{{ data_get($msg, 'created_at') }}</span>
                    </div>
                @empty
                    <div class="h-full flex items-center justify-center text-sm text-hb-muted">
                        {{ __('candidate.messages.select_conversation_or_empty') ?? 'ابدأ المحادثة أو اختر رسالة لعرض التفاصيل' }}
                    </div>
                @endforelse
            </div>

            <!-- Message Reply & Attachment Input Form -->
            <div class="p-4 border-t border-hb-border bg-white">
                <form action="{{ $sendMessageRoute }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <div class="flex items-center gap-2">
                        <textarea
                            name="message"
                            rows="1"
                            class="flex-1 rounded-xl border border-hb-border px-4 py-2.5 text-sm focus:border-hb-navy focus:ring-1 focus:ring-hb-navy resize-none"
                            placeholder="{{ __('candidate.messages.type_placeholder') ?? 'اكتب رسالتك هنا...' }}"
                            required
                        ></textarea>
                        
                        <!-- Attachment Upload Button -->
                        <label class="cursor-pointer inline-flex items-center justify-center p-2.5 rounded-xl border border-hb-border bg-hb-page/50 hover:bg-hb-page text-hb-muted hover:text-hb-navy transition" title="{{ __('candidate.messages.attach_file') ?? 'إرفاق ملف' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                            <input type="file" name="attachments[]" class="hidden" multiple>
                        </label>

                        <x-button type="submit">
                            {{ __('candidate.messages.send') ?? 'إرسال' }}
                        </x-button>
                    </div>
                </form>
            </div>
        @else
            <!-- Empty State when no conversation selected -->
            <div class="flex-1 flex flex-col items-center justify-center p-8 text-center">
                <p class="text-sm font-semibold text-hb-navy">{{ __('candidate.messages.no_active_conversation_title') ?? 'لا توجد محادثة محددة' }}</p>
                <p class="text-xs text-hb-muted mt-1">{{ __('candidate.messages.no_active_conversation_body') ?? 'اختر محادثة من القائمة الجانبية لبدء المراسلة وعرض سياق الطلب.' }}</p>
            </div>
        @endif
    </div>
</div>
@endsection
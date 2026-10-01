@php
    $noticeSchool = $school ?? auth()->user()?->school;
    $notice = $noticeSchool?->billingNotice();
@endphp

@if ($notice)
<div class="mb-6 rounded-xl px-5 py-4 border
    {{ $notice['level'] === 'danger'
        ? 'bg-rose-50 dark:bg-red-500/10 border-rose-200 dark:border-red-500/20'
        : 'bg-amber-50 dark:bg-amber-500/10 border-amber-200 dark:border-amber-500/20' }}">
    <div class="flex items-start gap-3">
        <svg class="w-5 h-5 mt-0.5 shrink-0 {{ $notice['level'] === 'danger' ? 'text-rose-600 dark:text-rose-400' : 'text-amber-600 dark:text-amber-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
        <div>
            <p class="text-sm font-semibold {{ $notice['level'] === 'danger' ? 'text-rose-800 dark:text-rose-300' : 'text-amber-800 dark:text-amber-300' }}">
                {{ $notice['title'] }}
            </p>
            <p class="text-xs {{ $notice['level'] === 'danger' ? 'text-rose-600 dark:text-rose-300/80' : 'text-amber-600 dark:text-amber-400/80' }} mt-0.5">
                {{ $notice['message'] }}
            </p>
        </div>
    </div>
</div>
@endif
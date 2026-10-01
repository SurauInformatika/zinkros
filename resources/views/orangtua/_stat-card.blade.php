@php
    $color = $color ?? 'bg-primary/10 dark:bg-primary/100/10 text-primary dark:text-primary';
    $icon = $icon ?? null;
    $value = $value ?? '—';
    $suffix = $suffix ?? '';
    $label = $label ?? '';
@endphp
<div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5">
    <div class="flex items-center gap-3">
        @if ($icon)
            <div class="flex items-center justify-center w-11 h-11 rounded-xl {{ $color }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
            </div>
        @endif
        <div class="min-w-0">
            <p class="text-xs font-medium text-slate-500 dark:text-white/50">{{ $label }}</p>
            <p class="text-xl font-bold truncate">{{ $value }} <span class="text-sm font-medium text-slate-400">{{ $suffix }}</span></p>
        </div>
    </div>
</div>

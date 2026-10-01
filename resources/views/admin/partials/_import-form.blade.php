@php
    $importTitle = $importTitle ?? 'Import Data';
    $templateRoute = $templateRoute ?? null;
    $importRoute = $importRoute ?? null;
    $submitLabel = $submitLabel ?? 'Import';
    $helptext = $helptext ?? null;
    $accent = $accent ?? 'blue';

    $accents = [
        'blue' => [
            'summary' => 'bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-500/20',
            'button' => 'from-blue-600 to-blue-500',
            'file' => 'file:bg-blue-50 dark:file:bg-blue-500/10 file:text-blue-700 dark:file:text-blue-400 hover:file:bg-blue-100 dark:hover:file:bg-blue-500/20',
        ],
        'violet' => [
            'summary' => 'bg-violet-50 dark:bg-violet-500/10 text-violet-700 dark:text-violet-400 hover:bg-violet-100 dark:hover:bg-violet-500/20',
            'button' => 'from-violet-600 to-violet-500',
            'file' => 'file:bg-violet-50 dark:file:bg-violet-500/10 file:text-violet-700 dark:file:text-violet-400 hover:file:bg-violet-100 dark:hover:file:bg-violet-500/20',
        ],
        'emerald' => [
            'summary' => 'bg-primary/10 dark:bg-primary/100/10 text-primary dark:text-primary hover:bg-primary/15 dark:hover:bg-primary/100/20',
            'button' => 'from-primary to-secondary',
            'file' => 'file:bg-primary/10 dark:file:bg-primary/100/10 file:text-primary dark:file:text-primary hover:file:bg-primary/15 dark:hover:file:bg-primary/100/20',
        ],
        'amber' => [
            'summary' => 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 hover:bg-amber-100 dark:hover:bg-amber-500/20',
            'button' => 'from-amber-500 to-orange-500',
            'file' => 'file:bg-amber-50 dark:file:bg-amber-500/10 file:text-amber-700 dark:file:text-amber-400 hover:file:bg-amber-100 dark:hover:file:bg-amber-500/20',
        ],
        'rose' => [
            'summary' => 'bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400 hover:bg-rose-100 dark:hover:bg-rose-500/20',
            'button' => 'from-rose-600 to-pink-500',
            'file' => 'file:bg-rose-50 dark:file:bg-rose-500/10 file:text-rose-700 dark:file:text-rose-400 hover:file:bg-rose-100 dark:hover:file:bg-rose-500/20',
        ],
    ];
    $a = $accents[$accent] ?? $accents['blue'];
@endphp

<details class="mb-6 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-[#141414] overflow-hidden">
    <summary class="flex items-center gap-3 cursor-pointer select-none px-5 py-4 transition {{ $a['summary'] }}">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
        <span class="text-sm font-semibold">{{ $importTitle }}</span>
        @if ($helptext)
            <span class="text-xs font-normal opacity-70">{{ $helptext }}</span>
        @endif
        <svg class="w-4 h-4 ml-auto shrink-0 transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>
    </summary>

    <div class="px-5 py-5 border-t border-slate-100 dark:border-white/5">
        <div class="flex flex-col lg:flex-row lg:items-end gap-5">
            <div class="flex-1">
                @if ($templateRoute)
                <a href="{{ route($templateRoute) }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 px-4 py-2 text-sm font-medium text-slate-700 dark:text-white/70 transition hover:bg-slate-100 dark:hover:bg-white/10">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Download Template
                </a>
                @endif
                <p class="mt-2 text-xs text-slate-400 dark:text-white/30">Format .xlsx / .xls, maksimal 2MB.</p>
            </div>

            @if ($importRoute)
            <form method="POST" action="{{ route($importRoute) }}" enctype="multipart/form-data" class="flex flex-col sm:flex-row sm:items-center gap-3">
                @csrf
                <input type="file" name="file" accept=".xlsx,.xls" required
                    class="w-full sm:w-auto text-sm text-slate-500 dark:text-white/40 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:cursor-pointer {{ $a['file'] }}">
                <button type="submit"
                    class="shrink-0 rounded-lg bg-gradient-to-r {{ $a['button'] }} px-4 py-2.5 text-sm font-semibold text-white shadow transition hover:shadow-lg hover:-translate-y-0.5">
                    {{ $submitLabel }}
                </button>
            </form>
            @endif
        </div>

        <div class="mt-5 rounded-lg border border-slate-100 dark:border-white/5 bg-slate-50 dark:bg-white/5 p-4">
            <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-white/40 mb-2">Petunjuk</h4>
            <ul class="space-y-1.5 text-xs text-slate-600 dark:text-white/50">
                <li>Download template, isi data sesuai format. Baris header (Nama, Email, dll) tidak boleh diubah.</li>
                <li>Kolom <strong>Password</strong> opsional. Jika dikosongkan, password default <code class="rounded bg-slate-100 dark:bg-white/10 px-1 py-0.5">password123</code> digunakan.</li>
                <li>Data dengan email/nama yang sudah ada akan dilewati (tidak diduplikasi).</li>
            </ul>
        </div>
    </div>
</details>
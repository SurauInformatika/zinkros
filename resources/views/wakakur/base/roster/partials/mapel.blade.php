<div id="roster-list" class="space-y-4">
    @forelse ($classes as $class)
        @php
            $rows = $rosterRows[$class->id] ?? collect();
            $complete = $rows->filter(fn ($row) => $row->teacher_id)->count();
            $missing = $rows->count() - $complete;
        @endphp
        <div class="roster-card rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5" data-class-card="{{ $class->id }}">
            <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
                <div class="min-w-0">
                    <h3 class="font-semibold text-lg">{{ $class->class_name }}</h3>
                    <p class="text-xs text-slate-400 dark:text-white/30 mt-0.5">
                        Wali: <span class="font-medium text-slate-500 dark:text-white/50">{{ $class->waliNames() }}</span>
                        <span class="mx-1.5">·</span>
                        <span class="font-medium">{{ $rows->count() }} mapel</span>
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    @if ($missing > 0)
                        <span class="inline-flex items-center rounded-full bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 px-2.5 py-1 text-xs font-medium text-amber-700 dark:text-amber-400">
                            {{ $missing }} belum ada guru
                        </span>
                    @elseif ($rows->count() > 0)
                        <span class="inline-flex items-center rounded-full bg-primary/10 dark:bg-primary/100/10 border border-primary/20 dark:border-primary/20 px-2.5 py-1 text-xs font-medium text-primary dark:text-primary">
                            Lengkap
                        </span>
                    @endif
                    <button type="button" onclick="printRosterCard('{{ $class->id }}')"
                        class="no-print inline-flex items-center gap-1.5 rounded-lg border border-slate-200 dark:border-white/10 px-2.5 py-1.5 text-xs font-medium text-slate-500 dark:text-white/40 hover:bg-slate-50 dark:hover:bg-white/5 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Cetak
                    </button>
                </div>
            </div>

            @if ($rows->count() > 0)
                <div class="space-y-1.5">
                    @foreach ($rows as $row)
                        <div class="flex items-center justify-between gap-3 rounded-lg px-3 py-2 {{ $row->teacher_id ? 'bg-slate-50 dark:bg-white/5' : 'bg-red-50/60 dark:bg-red-500/10' }}">
                            <div class="flex items-center gap-2 min-w-0">
                                @if ($row->subject?->type === 'QURAN')
                                    <svg class="w-3.5 h-3.5 shrink-0 text-primary dark:text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                @endif
                                <span class="text-sm font-medium text-slate-700 dark:text-white/70">{{ $row->subject?->name ?? 'Tanpa nama' }}</span>
                            </div>
                            @if ($row->teacher_id && $row->teacher)
                                <span class="inline-flex items-center gap-1.5 rounded-md bg-primary/10 dark:bg-primary/100/10 border border-primary/20 dark:border-primary/20 px-2.5 py-1 text-xs font-medium text-primary dark:text-primary">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    {{ $row->teacher->name }}
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-md bg-red-100 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 px-2.5 py-1 text-xs font-medium text-red-700 dark:text-red-400">
                                    Belum ada guru
                                </span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-slate-400 dark:text-white/30">Belum ada mapel diampu.</p>
            @endif
        </div>
    @empty
    <div class="no-print rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-8 text-center text-slate-400 dark:text-white/30 text-sm">
        @if ($q !== '')
            Tidak ada kelas yang cocok dengan "<strong>{{ $q }}</strong>".
        @else
            Belum ada data roster.
        @endif
    </div>
    @endforelse

    @if ($classes->isNotEmpty())
        @php
            $totalRows = $rosterRows->flatten(1);
            $totalComplete = $totalRows->filter(fn ($row) => $row->teacher_id)->count();
            $totalMissing = $totalRows->count() - $totalComplete;
        @endphp
        <div class="no-print rounded-xl bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/10 px-5 py-4 text-sm">
            <span class="font-medium text-slate-600 dark:text-white/60">
                {{ $classes->count() }} kelas · {{ $totalRows->count() }} mapel terpetakan
            </span>
            @if ($totalMissing > 0)
                <span class="ml-2 inline-flex items-center rounded-full bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 px-2.5 py-1 text-xs font-medium text-amber-700 dark:text-amber-400">
                    {{ $totalMissing }} belum ada guru
                </span>
            @elseif ($totalRows->count() > 0)
                <span class="ml-2 inline-flex items-center rounded-full bg-primary/10 dark:bg-primary/100/10 border border-primary/20 dark:border-primary/20 px-2.5 py-1 text-xs font-medium text-primary dark:text-primary">
                    Semua lengkap
                </span>
            @endif
        </div>
    @endif
</div>
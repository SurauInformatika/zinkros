@php
    $paletteJadwal = [
        'bg-rose-100/80 dark:bg-rose-500/15 text-rose-900 dark:text-rose-300',
        'bg-amber-100/80 dark:bg-amber-500/15 text-amber-900 dark:text-amber-300',
        'bg-primary/15/80 dark:bg-primary/100/15 text-primary-dark dark:text-primary',
        'bg-sky-100/80 dark:bg-sky-500/15 text-sky-900 dark:text-sky-300',
        'bg-violet-100/80 dark:bg-violet-500/15 text-violet-900 dark:text-violet-300',
        'bg-orange-100/80 dark:bg-orange-500/15 text-orange-900 dark:text-orange-300',
        'bg-lime-100/80 dark:bg-lime-500/15 text-lime-900 dark:text-lime-300',
        'bg-secondary/15/80 dark:bg-secondary/15 text-secondary-dark dark:text-secondary',
        'bg-indigo-100/80 dark:bg-indigo-500/15 text-indigo-900 dark:text-indigo-300',
        'bg-fuchsia-100/80 dark:bg-fuchsia-500/15 text-fuchsia-900 dark:text-fuchsia-300',
        'bg-cyan-100/80 dark:bg-cyan-500/15 text-cyan-900 dark:text-cyan-300',
        'bg-pink-100/80 dark:bg-pink-500/15 text-pink-900 dark:text-pink-300',
    ];
@endphp

<div id="roster-list" class="space-y-5">
    @forelse ($classes as $class)
@php
    $classSched = $schedules[$class->id] ?? collect();
    $classJp = $jpByClass[$class->id] ?? array_fill_keys($days, 8);
    $maxJp = max(array_values($classJp) ?: [8]);
    $cells = [];

    foreach ($classSched as $row) {
        for ($jp = $row->start_jp; $jp <= $row->end_jp; $jp++) {
            $cells[$row->day_name][$jp] = $row;
        }
    }
@endphp
        <div class="roster-card rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5" data-class-card="{{ $class->id }}">
            <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
                <div class="min-w-0">
                    <h3 class="font-semibold text-lg">{{ $class->class_name }}</h3>
                    <p class="text-xs text-slate-400 dark:text-white/30 mt-0.5">
                        Wali: <span class="font-medium text-slate-500 dark:text-white/50">{{ $class->waliNames() }}</span>
                        <span class="mx-1.5">·</span>
                        <span class="font-medium">
                            {{ $classSched->count() }} blok jadwal
                            · {{ $classSched->sum(fn ($r) => $r->end_jp - $r->start_jp + 1) }} JP/minggu
                        </span>
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    @if ($classSched->count() > 0)
                        <span class="inline-flex items-center rounded-full bg-primary/10 dark:bg-primary/100/10 border border-primary/20 dark:border-primary/20 px-2.5 py-1 text-xs font-medium text-primary dark:text-primary">
                            Sudah diatur
                        </span>
                    @else
                        <span class="inline-flex items-center rounded-full bg-slate-100 dark:bg-white/10 border border-slate-200 dark:border-white/10 px-2.5 py-1 text-xs font-medium text-slate-500 dark:text-white/40">
                            Belum diatur
                        </span>
                    @endif
                    <a href="{{ route('wakasek.base.roster.jadwal-edit', $class) }}"
                        class="no-print inline-flex items-center gap-1.5 rounded-lg bg-primary text-white px-3 py-1.5 text-xs font-medium hover:opacity-90 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Atur Jadwal
                    </a>
                    <button type="button" onclick="printRosterCard('{{ $class->id }}')"
                        class="no-print inline-flex items-center gap-1.5 rounded-lg border border-slate-200 dark:border-white/10 px-2.5 py-1.5 text-xs font-medium text-slate-500 dark:text-white/40 hover:bg-slate-50 dark:hover:bg-white/5 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Cetak
                    </button>
                </div>
            </div>

            @if ($classSched->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm border-separate border-spacing-0.5">
                        <thead>
                            <tr>
                                <th class="w-16 px-2 py-1 text-left text-xs font-medium text-slate-400 dark:text-white/30">Jam</th>
                                @foreach ($days as $day)
                                    @if (($classJp[$day] ?? 0) > 0)
                                        <th class="px-2 py-1 text-xs font-semibold text-slate-600 dark:text-white/60 text-center">
                                            {{ ucfirst($day) }}
                                            <span class="block font-normal text-[10px] text-slate-400 dark:text-white/30">{{ $classJp[$day] }} JP</span>
                                        </th>
                                    @endif
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @for ($jp = 1; $jp <= $maxJp; $jp++)
                                <tr>
                                    <td class="px-2 py-1 text-xs font-medium text-slate-500 dark:text-white/40">Jam {{ $jp }}</td>
                                    @foreach ($days as $day)
                                        @if (($classJp[$day] ?? 0) > 0)
                                            <td class="p-0.5 h-10 align-top">
                                                @php $row = $cells[$day][$jp] ?? null; @endphp
                                                @if ($row)
                                                    @php $tone = $paletteJadwal[abs(crc32($row->subject->name)) % count($paletteJadwal)]; @endphp
                                                    <div class="h-full rounded-md px-1.5 py-1 text-[11px] leading-tight {{ $tone }}">
                                                        <div class="font-semibold">{{ $row->subject->name }}</div>
                                                        @if ($row->teacher)
                                                            <div class="opacity-70 font-normal">{{ $row->teacher->name }}</div>
                                                        @endif
                                                    </div>
                                                @endif
                                            </td>
                                        @endif
                                    @endforeach
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm text-slate-400 dark:text-white/30">Belum ada jadwal mingguan untuk kelas ini.</p>
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
</div>
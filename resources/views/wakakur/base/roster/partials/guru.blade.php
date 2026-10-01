@php
    $paletteGuru = [
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
    ];
    $maxJp = max(array_values($jpByDay) ?: [8]);
    $cells = [];
    $totalBlocks = 0;
    foreach ($guruSchedules as $day => $rows) {
        foreach ($rows as $row) {
            $totalBlocks++;
            for ($jp = $row->start_jp; $jp <= $row->end_jp; $jp++) {
                $cells[$day][$jp] = $row;
            }
        }
    }
@endphp

@if ($selectedGuru === null)
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-8 text-center text-slate-400 dark:text-white/30 text-sm">
        Pilih guru untuk melihat jadwal mengajarnya.
    </div>
@else
    <div class="roster-card rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5">
        <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
            <div class="min-w-0">
                <h3 class="font-semibold text-lg">{{ $teachers->firstWhere('id', $selectedGuru)?->name ?? 'Guru' }}</h3>
                <p class="text-xs text-slate-400 dark:text-white/30 mt-0.5">
                    <span class="font-medium">{{ $totalBlocks }} blok jadwal</span>
                    <span class="mx-1.5">·</span>
                    <span class="font-medium">
                        {{ collect($guruSchedules)->flatten(1)->sum(fn ($r) => $r->end_jp - $r->start_jp + 1) }} JP/minggu
                    </span>
                </p>
            </div>
            <button type="button" onclick="printRosterAll()"
                class="no-print inline-flex items-center gap-1.5 rounded-lg border border-slate-200 dark:border-white/10 px-2.5 py-1.5 text-xs font-medium text-slate-500 dark:text-white/40 hover:bg-slate-50 dark:hover:bg-white/5 transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak
            </button>
        </div>

        @if ($totalBlocks > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm border-separate border-spacing-0.5">
                    <thead>
                        <tr>
                            <th class="w-16 px-2 py-1 text-left text-xs font-medium text-slate-400 dark:text-white/30">Jam</th>
                            @foreach ($days as $day)
                                @if (($jpByDay[$day] ?? 0) > 0)
                                    <th class="px-2 py-1 text-xs font-semibold text-slate-600 dark:text-white/60 text-center">
                                        {{ ucfirst($day) }}
                                        <span class="block font-normal text-[10px] text-slate-400 dark:text-white/30">{{ $jpByDay[$day] }} JP</span>
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
                                    @if (($jpByDay[$day] ?? 0) > 0)
                                        <td class="p-0.5 h-10 align-top">
                                            @php $row = $cells[$day][$jp] ?? null; @endphp
                                            @if ($row)
                                                @php $tone = $paletteGuru[abs(crc32($row->classRoom->class_name)) % count($paletteGuru)]; @endphp
                                                <div class="h-full rounded-md px-1.5 py-1 text-[11px] leading-tight {{ $tone }}">
                                                    <div class="font-semibold">{{ $row->classRoom->class_name }}</div>
                                                    <div class="opacity-70 font-normal">{{ $row->subject->name }}</div>
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
            <p class="text-sm text-slate-400 dark:text-white/30">Guru ini belum punya jadwal mingguan.</p>
        @endif
    </div>
@endif
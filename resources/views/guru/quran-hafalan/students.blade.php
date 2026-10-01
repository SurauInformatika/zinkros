@extends('layouts.app')

@section('title', 'Input Hafalan Al-Quran')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold tracking-tight">Input Hafalan Al-Quran</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Pilih siswa untuk input hafalan</p>
    </div>

    @if (session('success'))
    <div class="rounded-lg bg-primary/10 dark:bg-primary/100/10 border border-primary/20 dark:border-primary/20 p-4 text-sm text-primary dark:text-primary">{{ session('success') }}</div>
    @endif

    @if ($assignments->count() > 0)
        @foreach ($assignments as $className => $group)
            <div class="rounded-xl border border-slate-200 bg-white dark:border-white/10 dark:bg-[#141414] overflow-hidden">
                <div class="border-b border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/[0.02] px-4 py-3">
                    <h2 class="text-sm font-semibold text-slate-700 dark:text-white/80">Kelas {{ $className }}</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm whitespace-nowrap">
                        <thead>
                                <tr class="border-b border-slate-100 dark:border-white/5">
                                <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 dark:text-white/40">Nama Siswa</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 dark:text-white/40">Hafalan Terakhir</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 dark:text-white/40">Target</th>
                                <th class="px-4 py-2 text-center text-xs font-medium text-slate-500 dark:text-white/40 w-48">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($group as $i => $a)
                                @php
                                    $sid = $a->student_id;
                                    $lastRecord = \App\Models\TahfidzRecord::where('student_id', $sid)
                                        ->where('teacher_id', auth()->id())
                                        ->with('quranMaster')
                                        ->orderByDesc('recorded_date')
                                        ->first();
                                    $targets = $a->student?->quranTargets ?? collect();
                                @endphp
                                <tr class="border-b border-slate-50 last:border-0 dark:border-white/5 hover:bg-slate-50 dark:hover:bg-white/[0.02]">
                                    <td class="px-4 py-3 font-medium text-slate-700 dark:text-white/80">{{ $a->student?->name ?? '-' }}</td>
                                    <td class="px-4 py-3">
                                        @if ($lastRecord)
                                            <div class="text-sm text-slate-700 dark:text-white/70">{{ $lastRecord->quranMaster->surah_name }} <span class="font-medium">{{ $lastRecord->ayat_start }}-{{ $lastRecord->ayat_end }}</span></div>
                                            <div class="text-xs text-slate-400 dark:text-white/30 mt-0.5">
                                                {{ \Carbon\Carbon::parse($lastRecord->recorded_date)->format('d M Y') }}
                                                <span class="inline-flex items-center rounded-md {{ $lastRecord->activity_type === 'ZIADAH' ? 'bg-primary/10 dark:bg-primary/100/10 text-primary dark:text-primary' : 'bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400' }} px-1.5 py-0.5 text-[10px] font-medium ml-1">{{ $lastRecord->activity_type === 'ZIADAH' ? 'Ziadah' : 'Murajaah' }}</span>
                                                @php $p = \App\Models\TahfidzRecord::scoreToPredikat($lastRecord->score); @endphp
                                                <span class="inline-flex items-center rounded-md {{ \App\Models\TahfidzRecord::predikatColor($p) }} px-1.5 py-0.5 text-[10px] font-bold ml-1">{{ $lastRecord->score }} ({{ $p }})</span>
                                            </div>
                                        @else
                                            <span class="text-xs text-slate-400 dark:text-white/30">Belum ada hafalan</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($targets->isNotEmpty())
                                            @php
                                                $activeTargets = $targets->filter(fn ($t) => $t->is_active);
                                                $firstTarget = $targets->first();
                                                $progress = $a->target_progress;
                                            @endphp
                                            @if ($firstTarget)
                                                <div class="w-40">
                                                    <div class="flex items-center justify-between mb-1">
                                                        <span class="text-xs text-slate-500 dark:text-white/40 truncate">{{ $firstTarget->title }}</span>
                                                        <span class="text-xs font-semibold {{ $progress && $progress['status'] === 'completed' ? 'text-primary dark:text-primary' : 'text-slate-600 dark:text-white/60' }}">
                                                            {{ $progress ? $progress['percent'] . '%' : '-' }}
                                                        </span>
                                                    </div>
                                                    @if ($progress)
                                                        <a href="{{ route('guru.quran-hafalan.input', $sid) }}" class="group block">
                                                            <div class="h-1.5 w-full rounded-full bg-slate-100 dark:bg-white/10 overflow-hidden">
                                                                <div class="h-full rounded-full {{ $progress['status'] === 'completed' ? 'bg-primary/100' : ($progress['status'] === 'overdue' ? 'bg-red-500' : 'bg-primary/100/80 group-hover:bg-primary/100') }} transition-colors" style="width: {{ min(100, $progress['percent']) }}%"></div>
                                                            </div>
                                                        </a>
                                                        <div class="flex items-center justify-between mt-1">
                                                            <span class="text-[10px] text-slate-400 dark:text-white/30">{{ $progress['completed_ayat'] }}/{{ $progress['total_ayat'] }} ayat</span>
                                                            <a href="{{ route('guru.quran-hafalan.target-edit', [$sid, $firstTarget->id]) }}" class="text-[10px] text-slate-400 hover:text-primary dark:text-white/30 dark:hover:text-primary">Edit</a>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        @else
                                            <div class="flex items-center justify-between gap-2">
                                                <span class="text-xs text-slate-400 dark:text-white/30">Belum ada target</span>
                                                <a href="{{ route('guru.quran-hafalan.target-create', $sid) }}" class="inline-flex items-center gap-1 rounded-lg border border-primary/20 dark:border-primary/20 px-2 py-1 text-[10px] font-medium text-primary dark:text-primary hover:bg-primary/10 dark:hover:bg-primary/100/10 transition-colors whitespace-nowrap">
                                                    Buat Target
                                                </a>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <a href="{{ route('guru.quran-hafalan.input', $a->student_id) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-primary/100 px-3 py-1.5 text-xs font-medium text-white hover:bg-primary transition-colors whitespace-nowrap">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                Input
                                            </a>
                                            <a href="{{ route('guru.quran-hafalan.history', $a->student_id) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 dark:border-white/10 px-3 py-1.5 text-xs font-medium text-slate-600 dark:text-white/60 hover:bg-slate-50 dark:hover:bg-white/[0.05] transition-colors whitespace-nowrap">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                Riwayat
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    @else
        <div class="rounded-xl border border-slate-200 bg-white p-12 text-center dark:border-white/10 dark:bg-[#141414]">
            <p class="text-sm text-slate-400 dark:text-white/30">Anda belum memiliki siswa Al-Quran yang di-assign.</p>
        </div>
    @endif
</div>
@endsection

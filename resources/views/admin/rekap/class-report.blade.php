@extends('layouts.app')

@section('title', 'Rekap — ' . $class->class_name)

@section('content')
<div class="mb-8">
    <div class="flex items-center gap-3 mb-2">
        <a href="{{ route('admin.rekap.index') }}" class="text-slate-400 hover:text-slate-600 dark:text-white/30 dark:hover:text-white/60 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="text-2xl font-bold tracking-tight">Rekap {{ $class->class_name }}</h1>
    </div>
    <p class="mt-1 text-sm text-slate-500 dark:text-white/40">
        Tingkat {{ $class->grade_level }} — {{ $classSummary['total_students'] }} siswa — Tahun Ajaran {{ now()->format('Y') }}
    </p>
</div>

<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4">
        <p class="text-xs text-slate-500 dark:text-white/40 mb-1">Total Siswa</p>
        <p class="text-2xl font-bold">{{ $classSummary['total_students'] }}</p>
    </div>
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4">
        <p class="text-xs text-slate-500 dark:text-white/40 mb-1">Rata-rata Kehadiran</p>
        <p class="text-2xl font-bold text-primary dark:text-primary">{{ $classSummary['avg_attendance'] }}%</p>
    </div>
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4">
        <p class="text-xs text-slate-500 dark:text-white/40 mb-1">Total Absensi</p>
        <p class="text-2xl font-bold">{{ $classSummary['total_records'] }}</p>
    </div>
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4">
        <p class="text-xs text-slate-500 dark:text-white/40 mb-1">Mata Pelajaran</p>
        <p class="text-2xl font-bold">{{ $subjects->count() }}</p>
    </div>
</div>

<div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 overflow-hidden mb-6">
    <div class="p-4 border-b border-slate-100 dark:border-white/5">
        <h2 class="font-semibold">Daftar Siswa</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm whitespace-nowrap">
            <thead>
                <tr class="border-b border-slate-100 dark:border-white/5 bg-slate-50 dark:bg-white/[0.02]">
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40 sticky left-0 bg-slate-50 dark:bg-[#141414] z-10">Siswa</th>
                    <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40" colspan="4">Kehadiran</th>
                    @foreach ($subjects as $sub)
                    <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40" colspan="2">{{ Str::limit($sub->name, 12) }}</th>
                    @endforeach
                    <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40" colspan="3">Tahfidz</th>
                </tr>
                <tr class="border-b border-slate-100 dark:border-white/5 bg-slate-50 dark:bg-white/[0.02]">
                    <th class="px-4 py-1 text-left text-xs font-medium text-slate-400 dark:text-white/30 sticky left-0 bg-slate-50 dark:bg-[#141414] z-10"></th>
                    <th class="px-2 py-1 text-center text-xs text-slate-400 dark:text-white/30">H</th>
                    <th class="px-2 py-1 text-center text-xs text-slate-400 dark:text-white/30">S</th>
                    <th class="px-2 py-1 text-center text-xs text-slate-400 dark:text-white/30">I</th>
                    <th class="px-2 py-1 text-center text-xs text-slate-400 dark:text-white/30">A</th>
                    @foreach ($subjects as $sub)
                    <th class="px-2 py-1 text-center text-xs text-slate-400 dark:text-white/30">Nilai</th>
                    <th class="px-2 py-1 text-center text-xs text-slate-400 dark:text-white/30">Grade</th>
                    @endforeach
                    <th class="px-2 py-1 text-center text-xs text-slate-400 dark:text-white/30">Ayat</th>
                    <th class="px-2 py-1 text-center text-xs text-slate-400 dark:text-white/30">Surah</th>
                    <th class="px-2 py-1 text-center text-xs text-slate-400 dark:text-white/30">Grade</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($students as $std)
                @php
                    $att = $attendanceStats->get($std->id, ['hadir'=>0,'sakit'=>0,'izin'=>0,'alpa'=>0,'pct_hadir'=>0]);
                    $grades = $gradeStats->get($std->id, collect());
                    $tah = $tahfidzStats->get($std->id, null);
                @endphp
                <tr class="border-b border-slate-50 dark:border-white/5 last:border-0 hover:bg-slate-50 dark:hover:bg-white/[0.02]">
                    <td class="px-4 py-2.5 font-medium sticky left-0 bg-white dark:bg-[#141414] z-10">
                        <a href="{{ route('admin.rekap.student', $std->id) }}" class="text-primary dark:text-primary hover:underline">{{ $std->name }}</a>
                    </td>
                    <td class="px-2 py-2.5 text-center text-primary dark:text-primary font-medium">{{ $att['hadir'] }}</td>
                    <td class="px-2 py-2.5 text-center text-amber-600 dark:text-amber-400">{{ $att['sakit'] }}</td>
                    <td class="px-2 py-2.5 text-center text-blue-600 dark:text-blue-400">{{ $att['izin'] }}</td>
                    <td class="px-2 py-2.5 text-center text-red-600 dark:text-red-400">{{ $att['alpa'] }}</td>
                    @foreach ($subjects as $sub)
                        @if ($grades->has($sub->id))
                            @php $sg = $grades[$sub->id]; @endphp
                            <td class="px-2 py-2.5 text-center text-sm">{{ $sg['avg'] }}</td>
                            <td class="px-2 py-2.5 text-center">
                                @if ($sg['final'] >= 80)
                                <span class="inline-flex items-center rounded-md bg-primary/10 dark:bg-primary/100/10 px-1.5 py-0.5 text-xs font-bold text-primary dark:text-primary">{{ $sg['final'] }}</span>
                                @elseif ($sg['final'] >= 60)
                                <span class="inline-flex items-center rounded-md bg-amber-50 dark:bg-amber-500/10 px-1.5 py-0.5 text-xs font-bold text-amber-700 dark:text-amber-400">{{ $sg['final'] }}</span>
                                @else
                                <span class="inline-flex items-center rounded-md bg-red-50 dark:bg-red-500/10 px-1.5 py-0.5 text-xs font-bold text-red-700 dark:text-red-400">{{ $sg['final'] }}</span>
                                @endif
                            </td>
                        @else
                            <td class="px-2 py-2.5 text-center text-xs text-slate-300 dark:text-white/20">—</td>
                            <td class="px-2 py-2.5 text-center text-xs text-slate-300 dark:text-white/20">—</td>
                        @endif
                    @endforeach
                    @if ($tah)
                        <td class="px-2 py-2.5 text-center">{{ $tah['total_ayat'] }}</td>
                        <td class="px-2 py-2.5 text-center">{{ $tah['surahs'] }}</td>
                        <td class="px-2 py-2.5 text-center">
                            @php $p = \App\Models\TahfidzRecord::scoreToPredikat((int)($tah['avg_score'] ?? 0)); @endphp
                            <span class="font-medium">{{ $tah['avg_score'] ?? '-' }}</span>
                            <span class="inline-flex items-center rounded-md {{ \App\Models\TahfidzRecord::predikatColor($p) }} px-1.5 py-0.5 text-xs font-bold ml-1">{{ $p }}</span>
                        </td>
                    @else
                        <td class="px-2 py-2.5 text-center text-xs text-slate-300 dark:text-white/20">—</td>
                        <td class="px-2 py-2.5 text-center text-xs text-slate-300 dark:text-white/20">—</td>
                        <td class="px-2 py-2.5 text-center text-xs text-slate-300 dark:text-white/20">—</td>
                    @endif
                </tr>
                @empty
                <tr>
                    <td colspan="{{ 5 + ($subjects->count() * 2) + 3 }}" class="px-4 py-8 text-center text-slate-400 dark:text-white/30 text-sm">Belum ada data siswa.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

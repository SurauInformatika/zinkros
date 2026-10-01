@extends('layouts.app')

@section('title', 'Rapor — ' . $student->name)

@section('content')
<div class="mb-8">
    <div class="flex items-center gap-3 mb-2">
        <a href="{{ route('admin.rekap.class', $student->class_id) }}" class="text-slate-400 hover:text-slate-600 dark:text-white/30 dark:hover:text-white/60 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="text-2xl font-bold tracking-tight">Rapor {{ $student->name }}</h1>
    </div>
    <p class="mt-1 text-sm text-slate-500 dark:text-white/40">
        {{ $student->classRoom?->class_name ?? '-' }} — NISN: {{ $student->nisn }} — Tahun Ajaran {{ $ay?->name ?? '-' }}
    </p>
</div>

<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4">
        <p class="text-xs text-slate-500 dark:text-white/40 mb-1">Kehadiran</p>
        <p class="text-2xl font-bold text-primary dark:text-primary">{{ $attStats['pct_hadir'] }}%</p>
        <p class="text-xs text-slate-400 dark:text-white/30 mt-1">{{ $attStats['hadir'] }}H / {{ $attStats['total'] }} hari</p>
    </div>
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4">
        <p class="text-xs text-slate-500 dark:text-white/40 mb-1">Sakit / Izin / Alpa</p>
        <p class="text-lg font-bold">
            <span class="text-amber-600 dark:text-amber-400">{{ $attStats['sakit'] }}S</span>
            <span class="text-slate-300 dark:text-white/20">/</span>
            <span class="text-blue-600 dark:text-blue-400">{{ $attStats['izin'] }}I</span>
            <span class="text-slate-300 dark:text-white/20">/</span>
            <span class="text-red-600 dark:text-red-400">{{ $attStats['alpa'] }}A</span>
        </p>
    </div>
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4">
        <p class="text-xs text-slate-500 dark:text-white/40 mb-1">Mata Pelajaran</p>
        <p class="text-2xl font-bold">{{ $subjectGrades->count() }}</p>
    </div>
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4">
        <p class="text-xs text-slate-500 dark:text-white/40 mb-1">Tahfidz</p>
        @if ($tahfidzStats)
        <p class="text-2xl font-bold">{{ $tahfidzStats['total_ayat'] }} ayat</p>
        <p class="text-xs text-slate-400 dark:text-white/30 mt-1">{{ $tahfidzStats['surahs'] }} surah</p>
        @else
        <p class="text-2xl font-bold text-slate-300 dark:text-white/20">—</p>
        @endif
    </div>
</div>

<div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 overflow-hidden mb-6">
    <div class="p-4 border-b border-slate-100 dark:border-white/5">
        <h2 class="font-semibold">Nilai per Mata Pelajaran</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm whitespace-nowrap">
            <thead>
                <tr class="border-b border-slate-100 dark:border-white/5 bg-slate-50 dark:bg-white/[0.02]">
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Mata Pelajaran</th>
                    @foreach ($gradeTypes as $gt)
                    <th class="px-3 py-3 text-center font-medium text-slate-500 dark:text-white/40">{{ $gt->name }}</th>
                    @endforeach
                    <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Rata-rata</th>
                    <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Nilai Akhir</th>
                    <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Grade</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($subjects as $sub)
                    @php $sg = $subjectGrades->get($sub->id, null); @endphp
                    <tr class="border-b border-slate-50 dark:border-white/5 last:border-0">
                        <td class="px-4 py-2.5 font-medium">
                            {{ $sub->name }}
                            @if ($sub->type === 'QURAN')
                            <span class="inline-flex items-center rounded-md bg-purple-50 dark:bg-purple-500/10 px-1.5 py-0.5 text-xs font-medium text-purple-700 dark:text-purple-400 ml-1">Quran</span>
                            @endif
                        </td>
                        @foreach ($gradeTypes as $gt)
                            @if ($sg && isset($sg['by_type'][$gt->id]))
                            <td class="px-3 py-2.5 text-center">{{ $sg['by_type'][$gt->id] }}</td>
                            @else
                            <td class="px-3 py-2.5 text-center text-slate-300 dark:text-white/20">—</td>
                            @endif
                        @endforeach
                        <td class="px-4 py-2.5 text-center">{{ $sg ? $sg['avg'] : '—' }}</td>
                        <td class="px-4 py-2.5 text-center font-bold">{{ $sg ? $sg['final'] : '—' }}</td>
                        <td class="px-4 py-2.5 text-center">
                            @if ($sg)
                                @if ($sg['final'] >= 80)
                                <span class="inline-flex items-center rounded-md bg-primary/10 dark:bg-primary/100/10 px-2 py-0.5 text-xs font-bold text-primary dark:text-primary">A</span>
                                @elseif ($sg['final'] >= 60)
                                <span class="inline-flex items-center rounded-md bg-amber-50 dark:bg-amber-500/10 px-2 py-0.5 text-xs font-bold text-amber-700 dark:text-amber-400">B</span>
                                @else
                                <span class="inline-flex items-center rounded-md bg-red-50 dark:bg-red-500/10 px-2 py-0.5 text-xs font-bold text-red-700 dark:text-red-400">C</span>
                                @endif
                            @else
                            <span class="text-slate-300 dark:text-white/20">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                <tr>
                    <td colspan="{{ 3 + $gradeTypes->count() }}" class="px-4 py-8 text-center text-slate-400 dark:text-white/30 text-sm">Belum ada data nilai.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if ($tahfidzStats)
<div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5 mb-6">
    <h2 class="font-semibold mb-4">Hafalan Tahfidz</h2>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-4">
        <div>
            <p class="text-xs text-slate-500 dark:text-white/40">Total Hafalan</p>
            <p class="text-xl font-bold">{{ $tahfidzStats['total'] }}</p>
        </div>
        <div>
            <p class="text-xs text-slate-500 dark:text-white/40">Total Ayat</p>
            <p class="text-xl font-bold">{{ $tahfidzStats['total_ayat'] }}</p>
        </div>
        <div>
            <p class="text-xs text-slate-500 dark:text-white/40">Ziadah / Murajaah</p>
            <p class="text-xl font-bold">
                <span class="text-primary dark:text-primary">{{ $tahfidzStats['ziadah'] }}</span>
                <span class="text-slate-300 dark:text-white/20">/</span>
                <span class="text-blue-600 dark:text-blue-400">{{ $tahfidzStats['murajaah'] }}</span>
            </p>
        </div>
        <div>
            <p class="text-xs text-slate-500 dark:text-white/40">Rata-rata Skor</p>
            @php $p = \App\Models\TahfidzRecord::scoreToPredikat((int)($tahfidzStats['avg_score'] ?? 0)); @endphp
            <p class="text-xl font-bold">
                {{ $tahfidzStats['avg_score'] ?? '-' }}
                <span class="inline-flex items-center rounded-md {{ \App\Models\TahfidzRecord::predikatColor($p) }} px-2 py-0.5 text-sm font-bold ml-1">{{ $p }}</span>
            </p>
        </div>
    </div>
    @if ($tahfidzStats['surah_names']->isNotEmpty())
    <p class="text-xs text-slate-500 dark:text-white/40 mb-2">Surah yang Dihafal:</p>
    <div class="flex flex-wrap gap-2">
        @foreach ($tahfidzStats['surah_names'] as $surah)
        <span class="inline-flex items-center rounded-md bg-purple-50 dark:bg-purple-500/10 px-2.5 py-1 text-xs font-medium text-purple-700 dark:text-purple-400">{{ $surah }}</span>
        @endforeach
    </div>
    @endif
</div>
@endif
@endsection

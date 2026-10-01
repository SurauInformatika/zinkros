@extends('layouts.app')

@section('title', 'Kehadiran ' . \Carbon\Carbon::parse($date)->translatedFormat('d M Y'))

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('guru.quran-absensi.rekap') }}" class="text-slate-400 hover:text-slate-600 dark:text-white/30 dark:hover:text-white/60 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Kehadiran {{ \Carbon\Carbon::parse($date)->translatedFormat('d M Y') }}</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-white/40">{{ $totalStudents }} siswa terdaftar</p>
        </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4">
            <p class="text-xs text-slate-500 dark:text-white/40 mb-1">Hadir</p>
            <p class="text-2xl font-bold text-primary dark:text-primary">{{ $hadir }}</p>
        </div>
        <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4">
            <p class="text-xs text-slate-500 dark:text-white/40 mb-1">Sakit</p>
            <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $sakit }}</p>
        </div>
        <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4">
            <p class="text-xs text-slate-500 dark:text-white/40 mb-1">Izin</p>
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $izin }}</p>
        </div>
        <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4">
            <p class="text-xs text-slate-500 dark:text-white/40 mb-1">Alpa</p>
            <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $alpa }}</p>
        </div>
    </div>

    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 overflow-hidden">
        <div class="p-4 border-b border-slate-100 dark:border-white/5">
            <h2 class="font-semibold">Daftar Siswa</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm whitespace-nowrap">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-white/5 bg-slate-50 dark:bg-white/[0.02]">
                        <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Nama Siswa</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Kelas</th>
                        <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Status</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Hafalan</th>
                        <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Skor</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($students as $s)
                    <tr class="border-b border-slate-50 dark:border-white/5 last:border-0 hover:bg-slate-50 dark:hover:bg-white/[0.02] transition-colors">
                        <td class="px-4 py-3">
                            <a href="{{ route('guru.quran-absensi.student-detail', ['date' => $date, 'studentId' => $s['id']]) }}" class="font-medium text-slate-700 dark:text-white/80 hover:text-primary dark:hover:text-primary transition-colors">
                                {{ $s['name'] }}
                            </a>
                        </td>
                        <td class="px-4 py-3 text-slate-500 dark:text-white/40">{{ $s['class'] }}</td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $statusCls = match($s['status']) {
                                    'HADIR' => 'bg-primary/10 dark:bg-primary/100/10 text-primary dark:text-primary',
                                    'SAKIT' => 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400',
                                    'IZIN' => 'bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400',
                                    default => 'bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400',
                                };
                            @endphp
                            <span class="inline-flex items-center rounded-md {{ $statusCls }} px-2 py-0.5 text-xs font-medium">{{ $s['status'] }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-600 dark:text-white/60">{{ $s['hafalan'] ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            @if ($s['predikat'])
                                @php $p = $s['predikat']; @endphp
                                <span class="inline-flex items-center rounded-md {{ \App\Models\TahfidzRecord::predikatColor($p) }} px-2 py-0.5 text-xs font-bold">{{ $s['score'] }} ({{ $p }})</span>
                            @else
                                <span class="text-slate-300 dark:text-white/20">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-slate-400 dark:text-white/30 text-sm">Tidak ada data siswa.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

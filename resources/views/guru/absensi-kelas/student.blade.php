@extends('layouts.app')

@section('title', 'Absensi — ' . $student->name)

@section('content')
<div class="mb-6">
    <a href="{{ route('guru.absensi-kelas.rekap', ['class_id' => $classRoom->id, 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}" class="text-sm text-slate-500 dark:text-white/40 hover:text-primary dark:hover:text-primary">&larr; Kembali ke Rekap</a>
</div>

<div class="mb-8">
    <div class="flex items-center gap-4">
        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-gradient-to-br from-primary to-secondary shadow-lg shadow-primary/25">
            <span class="text-lg font-bold text-white">{{ strtoupper(substr($student->name, 0, 2)) }}</span>
        </div>
        <div>
            <h1 class="text-2xl font-bold tracking-tight">{{ $student->name }}</h1>
            <p class="text-sm text-slate-500 dark:text-white/40">{{ $classRoom->class_name }} · {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} — {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}</p>
        </div>
    </div>
</div>

<div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mb-8">
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm text-slate-500 dark:text-white/40">Hadir</span>
            <span class="text-xs font-medium text-primary dark:text-primary">{{ $stats['persentase_hadir'] }}%</span>
        </div>
        <p class="text-2xl font-bold text-primary dark:text-primary">{{ $stats['hadir'] }}</p>
        <div class="mt-2 h-1.5 rounded-full bg-slate-100 dark:bg-white/5 overflow-hidden">
            <div class="h-full rounded-full bg-primary/100" style="width: {{ $stats['persentase_hadir'] }}%"></div>
        </div>
    </div>
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm text-slate-500 dark:text-white/40">Sakit</span>
            <span class="text-xs font-medium text-amber-600 dark:text-amber-400">{{ $stats['persentase_sakit'] }}%</span>
        </div>
        <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $stats['sakit'] }}</p>
        <div class="mt-2 h-1.5 rounded-full bg-slate-100 dark:bg-white/5 overflow-hidden">
            <div class="h-full rounded-full bg-amber-500" style="width: {{ $stats['persentase_sakit'] }}%"></div>
        </div>
    </div>
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm text-slate-500 dark:text-white/40">Izin</span>
            <span class="text-xs font-medium text-blue-600 dark:text-blue-400">{{ $stats['persentase_izin'] }}%</span>
        </div>
        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['izin'] }}</p>
        <div class="mt-2 h-1.5 rounded-full bg-slate-100 dark:bg-white/5 overflow-hidden">
            <div class="h-full rounded-full bg-blue-500" style="width: {{ $stats['persentase_izin'] }}%"></div>
        </div>
    </div>
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm text-slate-500 dark:text-white/40">Alpa</span>
            <span class="text-xs font-medium text-red-600 dark:text-red-400">{{ $stats['persentase_alpa'] }}%</span>
        </div>
        <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $stats['alpa'] }}</p>
        <div class="mt-2 h-1.5 rounded-full bg-slate-100 dark:bg-white/5 overflow-hidden">
            <div class="h-full rounded-full bg-red-500" style="width: {{ $stats['persentase_alpa'] }}%"></div>
        </div>
    </div>
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm text-slate-500 dark:text-white/40">Total</span>
            <span class="text-xs font-medium text-slate-400 dark:text-white/30">{{ $stats['total'] }} hari</span>
        </div>
        <p class="text-2xl font-bold">{{ $stats['persentase_hadir'] }}%</p>
        <p class="text-xs text-slate-500 dark:text-white/40 mt-1">Tingkat Kehadiran</p>
    </div>
</div>

@php
    $studentRoute = fn ($from, $to) => route('guru.absensi-kelas.student', array_merge(['studentId' => $student->id, 'class_id' => $classRoom->id], ['date_from' => $from, 'date_to' => $to]));
    $now = \Carbon\Carbon::now();
@endphp

<div class="flex flex-wrap gap-2 mb-8">
    <a href="{{ $studentRoute($now->copy()->startOfWeek()->toDateString(), $now->copy()->endOfWeek()->toDateString()) }}"
        class="px-3 py-1.5 rounded-lg text-xs font-medium border transition-colors {{ $dateFrom === $now->copy()->startOfWeek()->toDateString() && $dateTo === $now->copy()->endOfWeek()->toDateString() ? 'bg-primary/10 border-primary/30 text-primary dark:bg-primary/100/10 dark:border-primary/30 dark:text-primary' : 'bg-white dark:bg-[#141414] border-slate-200 dark:border-white/10 text-slate-600 dark:text-white/50 hover:border-primary/30 hover:text-primary dark:hover:border-primary/30 dark:hover:text-primary' }}">Minggu Ini</a>
    <a href="{{ $studentRoute($now->copy()->startOfWeek()->subWeek()->toDateString(), $now->copy()->endOfWeek()->subWeek()->toDateString()) }}"
        class="px-3 py-1.5 rounded-lg text-xs font-medium border transition-colors {{ $dateFrom === $now->copy()->startOfWeek()->subWeek()->toDateString() && $dateTo === $now->copy()->endOfWeek()->subWeek()->toDateString() ? 'bg-primary/10 border-primary/30 text-primary dark:bg-primary/100/10 dark:border-primary/30 dark:text-primary' : 'bg-white dark:bg-[#141414] border-slate-200 dark:border-white/10 text-slate-600 dark:text-white/50 hover:border-primary/30 hover:text-primary dark:hover:border-primary/30 dark:hover:text-primary' }}">Minggu Lalu</a>
    <a href="{{ $studentRoute($now->copy()->startOfMonth()->toDateString(), $now->copy()->endOfMonth()->toDateString()) }}"
        class="px-3 py-1.5 rounded-lg text-xs font-medium border transition-colors {{ $dateFrom === $now->copy()->startOfMonth()->toDateString() && $dateTo === $now->copy()->endOfMonth()->toDateString() ? 'bg-primary/10 border-primary/30 text-primary dark:bg-primary/100/10 dark:border-primary/30 dark:text-primary' : 'bg-white dark:bg-[#141414] border-slate-200 dark:border-white/10 text-slate-600 dark:text-white/50 hover:border-primary/30 hover:text-primary dark:hover:border-primary/30 dark:hover:text-primary' }}">Bulan Ini</a>
    <a href="{{ $studentRoute($now->copy()->startOfMonth()->subMonth()->toDateString(), $now->copy()->endOfMonth()->subMonth()->toDateString()) }}"
        class="px-3 py-1.5 rounded-lg text-xs font-medium border transition-colors {{ $dateFrom === $now->copy()->startOfMonth()->subMonth()->toDateString() && $dateTo === $now->copy()->endOfMonth()->subMonth()->toDateString() ? 'bg-primary/10 border-primary/30 text-primary dark:bg-primary/100/10 dark:border-primary/30 dark:text-primary' : 'bg-white dark:bg-[#141414] border-slate-200 dark:border-white/10 text-slate-600 dark:text-white/50 hover:border-primary/30 hover:text-primary dark:hover:border-primary/30 dark:hover:text-primary' }}">Bulan Lalu</a>
    <a href="{{ $studentRoute($now->copy()->startOfQuarter()->toDateString(), $now->copy()->endOfQuarter()->toDateString()) }}"
        class="px-3 py-1.5 rounded-lg text-xs font-medium border transition-colors {{ $dateFrom === $now->copy()->startOfQuarter()->toDateString() && $dateTo === $now->copy()->endOfQuarter()->toDateString() ? 'bg-primary/10 border-primary/30 text-primary dark:bg-primary/100/10 dark:border-primary/30 dark:text-primary' : 'bg-white dark:bg-[#141414] border-slate-200 dark:border-white/10 text-slate-600 dark:text-white/50 hover:border-primary/30 hover:text-primary dark:hover:border-primary/30 dark:hover:text-primary' }}">Semester Ini</a>
    <a href="{{ $studentRoute($now->copy()->subQuarter()->startOfQuarter()->toDateString(), $now->copy()->subQuarter()->endOfQuarter()->toDateString()) }}"
        class="px-3 py-1.5 rounded-lg text-xs font-medium border transition-colors {{ $dateFrom === $now->copy()->subQuarter()->startOfQuarter()->toDateString() && $dateTo === $now->copy()->subQuarter()->endOfQuarter()->toDateString() ? 'bg-primary/10 border-primary/30 text-primary dark:bg-primary/100/10 dark:border-primary/30 dark:text-primary' : 'bg-white dark:bg-[#141414] border-slate-200 dark:border-white/10 text-slate-600 dark:text-white/50 hover:border-primary/30 hover:text-primary dark:hover:border-primary/30 dark:hover:text-primary' }}">Semester Lalu</a>
    <a href="{{ $studentRoute($now->copy()->startOfYear()->toDateString(), $now->copy()->endOfYear()->toDateString()) }}"
        class="px-3 py-1.5 rounded-lg text-xs font-medium border transition-colors {{ $dateFrom === $now->copy()->startOfYear()->toDateString() && $dateTo === $now->copy()->endOfYear()->toDateString() ? 'bg-primary/10 border-primary/30 text-primary dark:bg-primary/100/10 dark:border-primary/30 dark:text-primary' : 'bg-white dark:bg-[#141414] border-slate-200 dark:border-white/10 text-slate-600 dark:text-white/50 hover:border-primary/30 hover:text-primary dark:hover:border-primary/30 dark:hover:text-primary' }}">Tahun Ini</a>
</div>

<div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 overflow-hidden">
    <div class="p-4 border-b border-slate-100 dark:border-white/5">
        <h2 class="font-semibold">Riwayat Absensi</h2>
    </div>

    @if ($records->isEmpty())
    <div class="p-8 text-center text-slate-400 dark:text-white/30 text-sm">Belum ada data absensi untuk rentang tanggal ini.</div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm whitespace-nowrap">
            <thead>
                <tr class="border-b border-slate-100 dark:border-white/5 bg-slate-50 dark:bg-white/[0.02]">
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Tanggal</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Hari</th>
                    <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Status</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Catatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($records as $rec)
                <tr class="border-b border-slate-50 dark:border-white/5 last:border-0 hover:bg-slate-50 dark:hover:bg-white/[0.02] transition-colors">
                    <td class="px-4 py-2.5">{{ \Carbon\Carbon::parse($rec->date)->format('d M Y') }}</td>
                    <td class="px-4 py-2.5 text-slate-500 dark:text-white/40">{{ \Carbon\Carbon::parse($rec->date)->isoFormat('dddd') }}</td>
                    <td class="px-4 py-2.5 text-center">
                        @if ($rec->status === 'HADIR')
                        <span class="inline-flex items-center rounded-md bg-primary/10 dark:bg-primary/100/10 px-2.5 py-1 text-xs font-bold text-primary dark:text-primary">Hadir</span>
                        @elseif ($rec->status === 'SAKIT')
                        <span class="inline-flex items-center rounded-md bg-amber-50 dark:bg-amber-500/10 px-2.5 py-1 text-xs font-bold text-amber-700 dark:text-amber-400">Sakit</span>
                        @elseif ($rec->status === 'IZIN')
                        <span class="inline-flex items-center rounded-md bg-blue-50 dark:bg-blue-500/10 px-2.5 py-1 text-xs font-bold text-blue-700 dark:text-blue-400">Izin</span>
                        @else
                        <span class="inline-flex items-center rounded-md bg-red-50 dark:bg-red-500/10 px-2.5 py-1 text-xs font-bold text-red-700 dark:text-red-400">Alpa</span>
                        @endif
                    </td>
                    <td class="px-4 py-2.5 text-sm text-slate-500 dark:text-white/40">{{ $rec->notes ?: '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection

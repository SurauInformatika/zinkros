@extends('layouts.app')

@section('title', $kaldik->name)

@php
    $statusColors = [
        'draft'     => 'bg-slate-100 dark:bg-white/10 text-slate-600 dark:text-white/50',
        'pending'   => 'bg-amber-100 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400',
        'final'     => 'bg-primary/15 dark:bg-primary/100/10 text-primary dark:text-primary',
        'archived'  => 'bg-slate-100 dark:bg-white/10 text-slate-400 dark:text-white/30',
    ];
    $statusLabels = [
        'draft' => 'Draf', 'pending' => 'Menunggu Approval', 'final' => 'Resmi', 'archived' => 'Arsip',
    ];
@endphp

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <a href="{{ route('guru.kaldik.index') }}" class="text-sm text-slate-500 dark:text-white/40 hover:text-primary dark:hover:text-primary mb-1 inline-block">&larr; Kembali</a>
        <h1 class="text-2xl font-bold tracking-tight">{{ $kaldik->name }}</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Detail kalender pendidikan.</p>
    </div>
    <a href="{{ route('guru.kaldik.export', $kaldik) }}" target="_blank" class="rounded-lg bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 text-sm font-medium transition-colors">Export PDF</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5">
        <div class="text-sm text-slate-500 dark:text-white/40 mb-1">Status</div>
        <span class="inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-medium {{ $statusColors[$kaldik->status] ?? '' }}">{{ $statusLabels[$kaldik->status] ?? $kaldik->status }}</span>
    </div>
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5">
        <div class="text-sm text-slate-500 dark:text-white/40 mb-1">Tahun Ajaran / Cakupan</div>
        <div class="text-lg font-bold text-slate-900 dark:text-white">{{ $kaldik->academicYear?->name ?? '-' }} / {{ $kaldik->semesterLabel() }}</div>
    </div>
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5">
        <div class="text-sm text-slate-500 dark:text-white/40 mb-1">Periode</div>
        <div class="text-lg font-bold text-slate-900 dark:text-white">{{ $kaldik->start_date->format('d M Y') }} — {{ $kaldik->end_date->format('d M Y') }}</div>
    </div>
</div>

<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <div class="rounded-xl bg-primary/10 dark:bg-primary/100/5 border border-primary/20 dark:border-primary/10 p-4 text-center">
        <div class="text-2xl font-bold text-primary dark:text-primary">{{ $kaldik->totalWeeks() }}</div>
        <div class="text-xs text-slate-500 dark:text-white/40 mt-1">Total Minggu</div>
    </div>
    <div class="rounded-xl bg-blue-50 dark:bg-blue-500/5 border border-blue-200 dark:border-blue-500/10 p-4 text-center">
        <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $kaldik->holidays->count() }}</div>
        <div class="text-xs text-slate-500 dark:text-white/40 mt-1">Hari Libur</div>
    </div>
    <div class="rounded-xl bg-amber-50 dark:bg-amber-500/5 border border-amber-200 dark:border-amber-500/10 p-4 text-center">
        <div class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $kaldik->effectiveWeeks() }}</div>
        <div class="text-xs text-slate-500 dark:text-white/40 mt-1">Minggu Efektif</div>
    </div>
    <div class="rounded-xl bg-purple-50 dark:bg-purple-500/5 border border-purple-200 dark:border-purple-500/10 p-4 text-center">
        <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $kaldik->totalJp() }}</div>
        <div class="text-xs text-slate-500 dark:text-white/40 mt-1">Total JP</div>
    </div>
</div>

<!-- Struktur & Durasi -->
<div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-6">
    <h2 class="text-lg font-semibold mb-4">Struktur &amp; Durasi JP per Jenjang</h2>
    @if ($kaldik->levelStructures->count() > 0)
    <div class="overflow-x-auto">
        <table class="w-full text-sm whitespace-nowrap">
            <thead>
                <tr class="border-b border-slate-100 dark:border-white/5">
                    <th class="px-2 pb-2 text-left font-medium text-slate-500 dark:text-white/40 whitespace-nowrap">Jenjang</th>
                    @php $viewDays = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'ahad']; @endphp
                    @foreach ($viewDays as $day)
                    <th class="px-2 pb-2 text-center font-medium text-slate-500 dark:text-white/40 capitalize whitespace-nowrap">{{ $day }}</th>
                    @endforeach
                    <th class="px-2 pb-2 text-center font-medium text-slate-500 dark:text-white/40">mnt/JP</th>
                    <th class="px-2 pb-2 text-center font-medium text-slate-500 dark:text-white/40">JP/mgg</th>
                    <th class="px-2 pb-2 text-center font-medium text-slate-500 dark:text-white/40">mnt/mgg</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($kaldik->levelStructures->sortBy('grade_level_start') as $ls)
                <tr class="border-b border-slate-50 dark:border-white/5 last:border-0">
                    <td class="px-2 py-2 whitespace-nowrap">Kelas {{ $ls->grade_level_start }}{{ $ls->grade_level_start !== $ls->grade_level_end ? ' - ' . $ls->grade_level_end : '' }}</td>
                    @foreach ($viewDays as $day)
                    <td class="px-2 py-2 text-center">{{ $ls->jpForDay($day) }}</td>
                    @endforeach
                    <td class="px-2 py-2 text-center text-slate-500 dark:text-white/40">{{ $ls->jp_duration_minutes }}</td>
                    <td class="px-2 py-2 text-center font-semibold">{{ $ls->jpPerWeek() }}</td>
                    <td class="px-2 py-2 text-center font-medium">{{ number_format($ls->jpPerWeek() * $ls->jp_duration_minutes, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <p class="mt-3 text-xs text-slate-400 dark:text-white/30">Angka per hari = jumlah JP (0 = libur mingguan).</p>
    @else
    <p class="text-sm text-slate-400 dark:text-white/30 italic">Belum ada data.</p>
    @endif
</div>

<!-- Holidays -->
<div class="mt-6 rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-6">
    <h2 class="text-lg font-semibold mb-4">Hari Libur / Hari Besar</h2>
    @if ($kaldik->holidays->count() > 0)
    <div class="overflow-x-auto">
        <table class="w-full text-sm whitespace-nowrap">
            <thead>
                <tr class="border-b border-slate-100 dark:border-white/5">
                    <th class="pb-2 text-left font-medium text-slate-500 dark:text-white/40">Tanggal</th>
                    <th class="pb-2 text-left font-medium text-slate-500 dark:text-white/40">Keterangan</th>
                    <th class="pb-2 text-left font-medium text-slate-500 dark:text-white/40">Jenis</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($kaldik->holidays as $h)
                <tr class="border-b border-slate-50 dark:border-white/5 last:border-0">
                    <td class="py-2">{{ $h->date->format('d M Y') }}</td>
                    <td class="py-2">{{ $h->name }}</td>
                    <td class="py-2 text-slate-500 dark:text-white/40">{{ ucfirst($h->type) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <p class="text-sm text-slate-400 dark:text-white/30 italic">Belum ada data hari libur.</p>
    @endif
</div>
@endsection

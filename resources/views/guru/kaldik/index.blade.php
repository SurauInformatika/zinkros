@extends('layouts.app')

@section('title', 'Kalender Pendidikan')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold tracking-tight">Kalender Pendidikan</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Lihat kalender pendidikan resmi sekolah.</p>
</div>

<div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm whitespace-nowrap">
            <thead>
                <tr class="border-b border-slate-100 dark:border-white/5 bg-slate-50 dark:bg-white/[0.02]">
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Nama</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Tahun Ajaran</th>
                    <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Sem</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Periode</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($calendars as $cal)
                <tr class="border-b border-slate-50 dark:border-white/5 last:border-0 hover:bg-slate-50 dark:hover:bg-white/[0.02]">
                    <td class="px-4 py-3">
                        <div class="font-medium">{{ $cal->name }}</div>
                        @if ($cal->template)
                            <div class="text-xs text-slate-400 dark:text-white/30 mt-0.5">via {{ $cal->template->name }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-slate-500 dark:text-white/40 text-sm">{{ $cal->academicYear->name ?? '-' }}</td>
                    <td class="px-4 py-3 text-center text-slate-500 dark:text-white/40">{{ $cal->semesterLabel() }}</td>
                    <td class="px-4 py-3 text-slate-500 dark:text-white/40 text-xs">
                        {{ $cal->start_date->format('d M Y') }} — {{ $cal->end_date->format('d M Y') }}
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('guru.kaldik.show', $cal) }}" class="text-primary dark:text-primary hover:underline text-xs">Lihat Detail</a>
                            <a href="{{ route('guru.kaldik.export', $cal) }}" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline text-xs">Export PDF</a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-slate-400 dark:text-white/30">Belum ada kalender pendidikan yang resmi.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

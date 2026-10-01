@extends('layouts.app')

@section('title', 'Riwayat Absensi')

@section('content')
<div class="mb-6">
    <a href="{{ route('guru.absensi-mapel.index') }}" class="text-sm text-slate-500 dark:text-white/40 hover:text-primary dark:hover:text-primary transition-colors">&larr; Kembali ke Daftar Kelas</a>
</div>

<div class="mb-8">
    <h1 class="text-2xl font-bold tracking-tight">Riwayat Absensi</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Semua catatan absensi yang pernah diisi.</p>
</div>

<form method="GET" class="mb-6 rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4">
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
        <div>
            <label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Mata Pelajaran</label>
            <select name="subject_id" class="w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-2 text-sm focus:border-primary focus:ring-primary/20 dark:text-white/80">
                <option value="">Semua</option>
                @foreach ($subjects as $subject)
                <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Dari Tanggal</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                class="w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-2 text-sm focus:border-primary focus:ring-primary/20 dark:text-white/80">
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Sampai Tanggal</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                class="w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-2 text-sm focus:border-primary focus:ring-primary/20 dark:text-white/80">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="rounded-lg bg-primary/100 hover:bg-primary text-white px-4 py-2 text-sm font-medium transition-colors">
                Filter
            </button>
            <a href="{{ route('guru.absensi-mapel.history') }}" class="rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-[#141414] px-4 py-2 text-sm font-medium hover:bg-slate-50 dark:hover:bg-white/5 transition-colors">
                Reset
            </a>
        </div>
    </div>
</form>

@if ($absensi->isEmpty())
<div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-8 text-center">
    <svg class="w-12 h-12 mx-auto mb-3 text-slate-300 dark:text-white/10" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
    <p class="text-slate-500 dark:text-white/40 font-medium">Belum ada riwayat absensi</p>
</div>
@else
<div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm whitespace-nowrap">
            <thead>
                <tr class="border-b border-slate-100 dark:border-white/5 bg-slate-50 dark:bg-white/[0.02]">
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Tanggal</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Siswa</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Kelas</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Mata Pelajaran</th>
                    <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Status</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Catatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($absensi as $row)
                <tr class="border-b border-slate-50 dark:border-white/5 last:border-0">
                    <td class="px-4 py-3">{{ $row->date }}</td>
                    <td class="px-4 py-3 font-medium">{{ $row->student->name ?? '-' }}</td>
                    <td class="px-4 py-3 text-slate-500 dark:text-white/40">{{ $row->student->classRoom->class_name ?? '-' }}</td>
                    <td class="px-4 py-3">{{ $row->subject->name ?? '-' }}</td>
                    <td class="px-4 py-3 text-center">
                        @if ($row->status === 'HADIR')
                        <span class="inline-flex items-center rounded-md bg-primary/10 dark:bg-primary/100/10 px-2 py-0.5 text-xs font-medium text-primary dark:text-primary">Hadir</span>
                        @elseif ($row->status === 'SAKIT')
                        <span class="inline-flex items-center rounded-md bg-amber-50 dark:bg-amber-500/10 px-2 py-0.5 text-xs font-medium text-amber-700 dark:text-amber-400">Sakit</span>
                        @elseif ($row->status === 'IZIN')
                        <span class="inline-flex items-center rounded-md bg-blue-50 dark:bg-blue-500/10 px-2 py-0.5 text-xs font-medium text-blue-700 dark:text-blue-400">Izin</span>
                        @else
                        <span class="inline-flex items-center rounded-md bg-red-50 dark:bg-red-500/10 px-2 py-0.5 text-xs font-medium text-red-700 dark:text-red-400">Alpa</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-xs text-slate-500 dark:text-white/40">{{ $row->notes ?: '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">
    {{ $absensi->links() }}
</div>
@endif
@endsection

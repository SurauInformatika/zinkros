@extends('layouts.app')

@section('title', 'Kalender Pendidikan')

@php
    $statusColors = [
        'draft'     => 'bg-slate-100 dark:bg-white/10 text-slate-600 dark:text-white/50',
        'pending'   => 'bg-amber-100 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400',
        'final'     => 'bg-primary/15 dark:bg-primary/100/10 text-primary dark:text-primary',
        'archived'  => 'bg-slate-100 dark:bg-white/10 text-slate-400 dark:text-white/30',
    ];
    $statusLabels = [
        'draft' => 'Draf', 'pending' => 'Menunggu Approval', 'final' => 'Final', 'archived' => 'Arsip',
    ];
@endphp

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight">Kalender Pendidikan</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Kelola KALDIK sekolah.</p>
    </div>
    <div class="flex items-center gap-2">
        <button onclick="document.getElementById('copyModal').classList.remove('hidden')" class="rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-[#141414] px-4 py-2 text-sm font-medium hover:bg-slate-50 dark:hover:bg-white/5 transition-colors">
            Salin dari Tahun Lalu
        </button>
        <a href="{{ route('wakasek.kaldik.create') }}" class="rounded-lg bg-primary/100 hover:bg-primary text-white px-4 py-2 text-sm font-medium transition-colors">
            + Buat Baru
        </a>
    </div>
</div>

@if (session('success'))
<div class="mb-4 rounded-lg bg-primary/10 dark:bg-primary/100/10 border border-primary/20 dark:border-primary/20 p-4 text-sm text-primary dark:text-primary">{{ session('success') }}</div>
@endif
@if (session('error'))
<div class="mb-4 rounded-lg bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 p-4 text-sm text-red-700 dark:text-red-400">{{ session('error') }}</div>
@endif

<div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm whitespace-nowrap">
            <thead>
                <tr class="border-b border-slate-100 dark:border-white/5 bg-slate-50 dark:bg-white/[0.02]">
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Nama</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Tahun Ajaran</th>
                    <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Semester</th>
                    <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Status</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Periode</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($calendars as $cal)
                <tr class="border-b border-slate-50 dark:border-white/5 last:border-0 hover:bg-slate-50 dark:hover:bg-white/[0.02]">
                    <td class="px-4 py-3">
                        <div class="font-medium">{{ $cal->name }}</div>
                        @if ($cal->version > 1 || $cal->parent_version_id)
                            <div class="text-xs text-slate-400 dark:text-white/30 mt-0.5">Revisi v{{ $cal->version }}</div>
                        @endif
                        @if ($cal->template)
                            <div class="text-xs text-slate-400 dark:text-white/30 mt-0.5">via {{ $cal->template->name }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-slate-500 dark:text-white/40 text-sm">{{ $cal->academicYear->name ?? '-' }}</td>
                    <td class="px-4 py-3 text-center text-slate-500 dark:text-white/40">{{ $cal->semesterLabel() }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium {{ $statusColors[$cal->status] ?? '' }}">{{ $statusLabels[$cal->status] ?? $cal->status }}</span>
                    </td>
                    <td class="px-4 py-3 text-slate-500 dark:text-white/40 text-xs">
                        {{ $cal->start_date->format('d M Y') }} — {{ $cal->end_date->format('d M Y') }}
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            @if ($cal->isDraft())
                                <a href="{{ route('wakasek.kaldik.edit', $cal) }}" class="text-primary dark:text-primary hover:underline text-xs">Edit</a>
                                <form method="POST" action="{{ route('wakasek.kaldik.submit', $cal) }}" onsubmit="return confirm('Submit untuk approval Kepsek?')">
                                    @csrf
                                    <button type="submit" class="text-blue-600 dark:text-blue-400 hover:underline text-xs">Submit</button>
                                </form>
                                <form method="POST" action="{{ route('wakasek.kaldik.destroy', $cal) }}" onsubmit="return confirm('Hapus kalender ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 dark:text-red-400 hover:underline text-xs">Hapus</button>
                                </form>
                            @elseif ($cal->isFinal())
                                <a href="{{ route('wakasek.kaldik.export', $cal) }}" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline text-xs">Export PDF</a>
                                <form method="POST" action="{{ route('wakasek.kaldik.revise', $cal) }}" onsubmit="return confirm('Buat revisi draft dari kalender FINAL ini?')">
                                    @csrf
                                    <button type="submit" class="text-primary dark:text-primary hover:underline text-xs">Buat Revisi</button>
                                </form>
                            @else
                                <a href="{{ route('wakasek.kaldik.export', $cal) }}" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline text-xs">Export PDF</a>
                                <span class="text-xs text-slate-400 dark:text-white/30">Read-only</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-slate-400 dark:text-white/30">Belum ada kalender pendidikan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Copy Modal -->
<div id="copyModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white dark:bg-[#141414] rounded-xl border border-slate-200 dark:border-white/10 p-6 w-full max-w-lg mx-4">
        <h3 class="text-lg font-semibold mb-4">Salin dari Tahun Lalu</h3>
        <form method="POST" action="{{ route('wakasek.kaldik.copy') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Kalender Sumber</label>
                <select name="source_calendar_id" required class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
                    @foreach ($calendars as $cal)
                        <option value="{{ $cal->id }}">{{ $cal->name }} ({{ $cal->semesterLabel() }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Nama Kalender Baru</label>
                <input type="text" name="name" required class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" placeholder="contoh: KALDIK 2026/2027">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Tahun Ajaran</label>
                <select name="academic_year_id" required class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
                    @foreach (\App\Models\AcademicYear::where('school_id', auth()->user()->school_id)->get() as $y)
                        <option value="{{ $y->id }}" {{ $y->is_active ? 'selected' : '' }}>{{ $y->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Mulai Semester 1</label>
                    <input type="date" name="start_date" required class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Mulai Semester 2 <span class="text-xs text-slate-400">(opsional)</span></label>
                    <input type="date" name="semester_2_start_date" class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Akhir Semester 2</label>
                    <input type="date" name="end_date" required class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('copyModal').classList.add('hidden')" class="text-sm text-slate-500 hover:text-slate-700 dark:text-white/40 dark:hover:text-white/60">Batal</button>
                <button type="submit" class="rounded-xl bg-gradient-to-r from-primary to-secondary px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition hover:shadow-xl hover:shadow-primary/30">Salin</button>
            </div>
        </form>
    </div>
</div>
@endsection

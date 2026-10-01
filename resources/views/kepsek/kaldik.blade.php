@extends('layouts.app')

@section('title', 'Approval KALDIK')

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
<div class="mb-6">
    <h1 class="text-2xl font-bold tracking-tight">Approval KALDIK</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Review dan approve kalender pendidikan dari WAKIL KEPALA SEKOLAH.</p>
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
                    <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Sem</th>
                    <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Status</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Diajukan Oleh</th>
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
                        @if ($cal->reject_reason)
                            <div class="text-xs text-red-500 dark:text-red-400 mt-0.5">Ditolak: {{ Str::limit($cal->reject_reason, 60) }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-slate-500 dark:text-white/40 text-sm">{{ $cal->academicYear->name ?? '-' }}</td>
                    <td class="px-4 py-3 text-center text-slate-500 dark:text-white/40">{{ $cal->semesterLabel() }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium {{ $statusColors[$cal->status] ?? '' }}">{{ $statusLabels[$cal->status] ?? $cal->status }}</span>
                    </td>
                    <td class="px-4 py-3 text-slate-500 dark:text-white/40 text-xs">{{ $cal->creator?->name ?? '-' }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            @if ($cal->isFinal())
                                <a href="{{ route('kepsek.kaldik.export', $cal) }}" target="_blank" class="rounded-lg bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 text-xs font-medium transition-colors">Export PDF</a>
                            @endif
                            @if ($cal->isPending())
                                <form method="POST" action="{{ route('kepsek.kaldik.approve', $cal) }}" onsubmit="return confirm('Approve kalender ini? Status akan menjadi FINAL.{{ $cal->parent_version_id ? ' Versi final sebelumnya akan diarsipkan.' : '' }}')">
                                    @csrf
                                    <button type="submit" class="rounded-lg bg-primary/100 hover:bg-primary text-white px-3 py-1 text-xs font-medium transition-colors">Approve</button>
                                </form>
                                <button onclick="showRejectModal('{{ $cal->id }}', '{{ $cal->name }}')" class="rounded-lg bg-red-500 hover:bg-red-600 text-white px-3 py-1 text-xs font-medium transition-colors">Tolak</button>
                            @elseif ($cal->isFinal())
                                <span class="text-xs text-primary dark:text-primary font-medium">Approved</span>
                            @elseif ($cal->isDraft() && $cal->reject_reason)
                                <span class="text-xs text-amber-500 dark:text-amber-400">Perlu Revisi</span>
                            @else
                                <span class="text-xs text-slate-400 dark:text-white/30">-</span>
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

<!-- Reject Modal -->
<div id="rejectModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white dark:bg-[#141414] rounded-xl border border-slate-200 dark:border-white/10 p-6 w-full max-w-md mx-4">
        <h3 class="text-lg font-semibold mb-1">Tolak Kalender</h3>
        <p class="text-sm text-slate-500 dark:text-white/40 mb-4">Menolak: <span id="rejectName" class="font-medium text-slate-700 dark:text-white/60"></span></p>
        <form id="rejectForm" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Alasan Penolakan <span class="text-red-500">*</span></label>
                <textarea name="reject_reason" rows="3" required
                    class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition"
                    placeholder="Jelaskan alasan penolakan..."></textarea>
            </div>
            <div class="flex items-center justify-end gap-3">
                <button type="button" onclick="document.getElementById('rejectModal').classList.add('hidden')" class="text-sm text-slate-500 hover:text-slate-700 dark:text-white/40 dark:hover:text-white/60">Batal</button>
                <button type="submit" class="rounded-xl bg-red-500 hover:bg-red-600 text-white px-5 py-2.5 text-sm font-semibold transition">Tolak</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function showRejectModal(id, name) {
    document.getElementById('rejectForm').action = '{{ url("kepsek/kaldik") }}/' + id + '/reject';
    document.getElementById('rejectName').textContent = name;
    document.getElementById('rejectModal').classList.remove('hidden');
}
</script>
@endpush

@extends('layouts.app')

@section('title', 'Dashboard ' . auth()->user()->school->roleLabel('kepsek'))

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold tracking-tight">Dashboard {{ auth()->user()->school->roleLabel('kepsek') }}</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Selamat datang, {{ auth()->user()->name }}.</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5">
        <div class="text-sm text-slate-500 dark:text-white/40">Total Siswa</div>
        <div class="mt-1 text-3xl font-bold text-slate-900 dark:text-white">{{ $stats['siswa'] }}</div>
    </div>
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5">
        <div class="text-sm text-slate-500 dark:text-white/40">Total Guru</div>
        <div class="mt-1 text-3xl font-bold text-slate-900 dark:text-white">{{ $stats['guru'] }}</div>
    </div>
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5">
        <div class="text-sm text-slate-500 dark:text-white/40">Total Kelas</div>
        <div class="mt-1 text-3xl font-bold text-slate-900 dark:text-white">{{ $stats['kelas'] }}</div>
    </div>
    <a href="{{ route('kepsek.kaldik') }}" class="rounded-xl bg-white dark:bg-[#141414] border {{ $stats['pending'] > 0 ? 'border-amber-300 dark:border-amber-500/30' : 'border-slate-200 dark:border-white/10' }} p-5 hover:border-primary/30 dark:hover:border-primary/30 transition group">
        <div class="text-sm text-slate-500 dark:text-white/40">Menunggu Approval</div>
        <div class="mt-1 text-3xl font-bold {{ $stats['pending'] > 0 ? 'text-amber-500' : 'text-slate-900 dark:text-white' }}">{{ $stats['pending'] }}</div>
    </a>
</div>

<div class="flex items-center gap-3 mb-8">
    <a href="{{ route('kepsek.kaldik') }}" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-primary to-secondary px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition hover:shadow-xl hover:shadow-primary/30">
        Approval KALDIK
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    </a>
    <a href="{{ route('kepsek.classes') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-[#141414] px-6 py-3 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-white/5 transition">
        Lihat Kelas
    </a>
    <a href="{{ route('kepsek.students') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-[#141414] px-6 py-3 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-white/5 transition">
        Lihat Siswa
    </a>
</div>

@if ($recentNotifications->count() > 0)
<div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold">Notifikasi Terbaru</h2>
        @if ($stats['unread_notif'] > 0)
        <form method="POST" action="{{ route('kepsek.notif.read-all') }}">
            @csrf
            <button type="submit" class="text-xs text-primary dark:text-primary hover:underline">Tandai semua sudah dibaca</button>
        </form>
        @endif
    </div>
    <div class="space-y-2">
        @foreach ($recentNotifications as $notif)
        <div class="flex items-start gap-3 p-3 rounded-lg {{ $notif->isRead() ? 'bg-white dark:bg-transparent' : 'bg-primary/10/50 dark:bg-primary/100/5 border border-primary/10 dark:border-primary/10' }}">
            <div class="mt-0.5">
                @if ($notif->type === 'kaldik_approval')
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-amber-100 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                @elseif ($notif->type === 'kaldik_approved')
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-primary/15 dark:bg-primary/100/10 text-primary dark:text-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </span>
                @else
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-red-100 dark:bg-red-500/10 text-red-600 dark:text-red-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </span>
                @endif
            </div>
            <div class="flex-1 min-w-0">
                @if ($notif->type === 'kaldik_approval')
                    <p class="text-sm"><span class="font-medium">{{ $notif->data['submitted_by'] ?? '-' }}</span> mengajukan kalender <span class="font-medium">"{{ $notif->data['calendar_name'] ?? '-' }}"</span> untuk approval.</p>
                @elseif ($notif->type === 'kaldik_approved')
                    <p class="text-sm">Kalender <span class="font-medium">"{{ $notif->data['calendar_name'] ?? '-' }}"</span> telah di-approve oleh {{ $notif->data['approved_by'] ?? '-' }}.</p>
                @else
                    <p class="text-sm">Kalender <span class="font-medium">"{{ $notif->data['calendar_name'] ?? '-' }}"</span> ditolak oleh {{ $notif->data['rejected_by'] ?? '-' }}.</p>
                    @if (!empty($notif->data['reason']))
                        <p class="text-xs text-slate-500 dark:text-white/40 mt-0.5">Alasan: {{ $notif->data['reason'] }}</p>
                    @endif
                @endif
                <p class="text-xs text-slate-400 dark:text-white/30 mt-1">{{ $notif->created_at->diffForHumans() }}</p>
            </div>
            <div class="flex items-center gap-2">
                @if ($notif->type === 'kaldik_approval' && !$notif->isRead())
                    <a href="{{ route('kepsek.kaldik') }}" class="text-xs text-primary dark:text-primary hover:underline">Review</a>
                @endif
                @if (!$notif->isRead())
                    <form method="POST" action="{{ route('kepsek.notif.read', $notif) }}">
                        @csrf
                        <button type="submit" class="text-xs text-slate-400 hover:text-slate-600 dark:hover:text-white/60" title="Tandai sudah dibaca">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </button>
                    </form>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif
@endsection

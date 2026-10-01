@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
@include('dashboard.subscription-notice')

<div class="mb-8">
    <h1 class="text-2xl font-bold tracking-tight">Dashboard</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Ringkasan data sekolah Anda.</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <a href="{{ route('admin.siswa.index') }}" class="group rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5 transition-all duration-200 hover:border-primary/30 dark:hover:border-primary/30 hover:shadow-lg hover:shadow-primary/5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm text-slate-500 dark:text-white/40">Total Siswa</span>
            <span class="w-8 h-8 rounded-lg bg-primary/10 dark:bg-primary/100/10 flex items-center justify-center group-hover:bg-primary/15 dark:group-hover:bg-primary/100/20 transition">
                <svg class="w-4 h-4 text-primary dark:text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 7.292 4 4 0 010-7.292zM15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </span>
        </div>
        <p class="text-3xl font-bold tracking-tight">{{ number_format($stats['siswa']) }}</p>
    </a>

    <a href="{{ route('admin.guru.index') }}" class="group rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5 transition-all duration-200 hover:border-blue-300 dark:hover:border-blue-500/30 hover:shadow-lg hover:shadow-blue-500/5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm text-slate-500 dark:text-white/40">Total Guru</span>
            <span class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center group-hover:bg-blue-100 dark:group-hover:bg-blue-500/20 transition">
                <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </span>
        </div>
        <p class="text-3xl font-bold tracking-tight">{{ number_format($stats['guru']) }}</p>
    </a>

    <a href="{{ route('admin.kelas.index') }}" class="group rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5 transition-all duration-200 hover:border-violet-300 dark:hover:border-violet-500/30 hover:shadow-lg hover:shadow-violet-500/5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm text-slate-500 dark:text-white/40">Total Kelas</span>
            <span class="w-8 h-8 rounded-lg bg-violet-50 dark:bg-violet-500/10 flex items-center justify-center group-hover:bg-violet-100 dark:group-hover:bg-violet-500/20 transition">
                <svg class="w-4 h-4 text-violet-600 dark:text-violet-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </span>
        </div>
        <p class="text-3xl font-bold tracking-tight">{{ number_format($stats['kelas']) }}</p>
    </a>

    <a href="{{ route('admin.subject.index') }}" class="group rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5 transition-all duration-200 hover:border-amber-300 dark:hover:border-amber-500/30 hover:shadow-lg hover:shadow-amber-500/5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm text-slate-500 dark:text-white/40">Total Mapel</span>
            <span class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-500/10 flex items-center justify-center group-hover:bg-amber-100 dark:group-hover:bg-amber-500/20 transition">
                <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            </span>
        </div>
        <p class="text-3xl font-bold tracking-tight">{{ number_format($stats['mapel']) }}</p>
    </a>
</div>

@php
    $showDash = fn ($v) => is_null($v) || $v === 0 && $v !== 0.0 ? '-' : $v;
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-4">
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm text-slate-500 dark:text-white/40">Kehadiran Pekan Ini</span>
            <span class="w-8 h-8 rounded-lg bg-primary/10 dark:bg-primary/100/10 flex items-center justify-center">
                <svg class="w-4 h-4 text-primary dark:text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </span>
        </div>
        <p class="text-3xl font-bold tracking-tight">{{ $attendancePct !== null ? number_format($attendancePct, 1) . '%' : '-' }}</p>
        <p class="mt-1 text-xs text-slate-500 dark:text-white/50">Hadir {{ $attendanceHadir }} dari {{ $attendance }} catatan</p>
    </div>

    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm text-slate-500 dark:text-white/40">Total Setoran Tahfidz</span>
            <span class="w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-500/10 flex items-center justify-center">
                <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
            </span>
        </div>
        <p class="text-3xl font-bold tracking-tight">{{ $showDash($tahfidz['total']) }}</p>
        <p class="mt-1 text-xs text-slate-500 dark:text-white/50">{{ $showDash($tahfidz['siswa']) }} siswa aktif</p>
    </div>

    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm text-slate-500 dark:text-white/40">Rata-rata Skor Tahfidz</span>
            <span class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center">
                <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.563.563 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/></svg>
            </span>
        </div>
        <p class="text-3xl font-bold tracking-tight">{{ $showDash($tahfidz['avg_score']) }}</p>
        <p class="mt-1 text-xs text-slate-500 dark:text-white/50">TA aktif</p>
    </div>

    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm text-slate-500 dark:text-white/40">Rata-rata Nilai</span>
            <span class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center">
                <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
            </span>
        </div>
        <p class="text-3xl font-bold tracking-tight">{{ $showDash($averageGrade) }}</p>
        <p class="mt-1 text-xs text-slate-500 dark:text-white/50">TA aktif</p>
    </div>
</div>
@endsection

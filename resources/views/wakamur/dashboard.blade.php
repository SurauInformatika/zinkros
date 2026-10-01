@extends('layouts.app')

@section('title', 'Dashboard ' . auth()->user()->school->roleLabel('wakamur'))

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold tracking-tight">Dashboard {{ auth()->user()->school->roleLabel('wakamur') }}</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Selamat datang, {{ auth()->user()->name }}.</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5">
        <div class="text-sm text-slate-500 dark:text-white/40">Total Siswa</div>
        <div class="mt-1 text-3xl font-bold text-slate-900 dark:text-white">{{ $stats['siswa'] }}</div>
    </div>
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5">
        <div class="text-sm text-slate-500 dark:text-white/40">Total Kelas</div>
        <div class="mt-1 text-3xl font-bold text-slate-900 dark:text-white">{{ $stats['kelas'] }}</div>
    </div>
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5">
        <div class="text-sm text-slate-500 dark:text-white/40">Total Guru</div>
        <div class="mt-1 text-3xl font-bold text-slate-900 dark:text-white">{{ $stats['guru'] }}</div>
    </div>
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5">
        <div class="text-sm text-slate-500 dark:text-white/40">Kehadiran Pekan Ini</div>
        <div class="mt-1 text-3xl font-bold {{ is_null($stats['kehadiran_pct']) ? 'text-slate-400 dark:text-white/40' : 'text-primary' }}">
            {{ is_null($stats['kehadiran_pct']) ? '-' : $stats['kehadiran_pct'] . '%' }}
        </div>
        <div class="mt-1 text-xs text-slate-500 dark:text-white/40">Hadir {{ $stats['hadir_pekan'] }} dari {{ $stats['catatan_pekan'] }} catatan</div>
    </div>
</div>

<div class="flex items-center gap-3 mb-8">
    <a href="{{ route('wakamur.siswa') }}" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-primary to-secondary px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition hover:shadow-xl hover:shadow-primary/30">
        Data Siswa
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    </a>
    <a href="{{ route('wakamur.kelas') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-[#141414] px-6 py-3 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-white/5 transition">
        Lihat Kelas
    </a>
</div>
@endsection
@extends('layouts.app')

@php
    $user = auth()->user();
@endphp

@section('title', 'Dashboard ' . $user->school->roleLabel('wakasek'))

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold tracking-tight">Dashboard {{ $user->school->roleLabel('wakasek') }}</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Selamat datang, {{ $user->name }}.</p>
    @if ($user->wakasekPositionLabel())
        <span class="mt-2 inline-flex items-center gap-1.5 rounded-lg bg-primary/10 dark:bg-primary/100/10 border border-primary/20 dark:border-primary/20 px-2.5 py-1 text-xs font-medium text-primary dark:text-primary">
            {{ $user->wakasekPositionLabel() }}
        </span>
    @endif
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5">
        <div class="text-sm text-slate-500 dark:text-white/40">Total Kalender</div>
        <div class="mt-1 text-3xl font-bold text-slate-900 dark:text-white">{{ $stats['calendars'] }}</div>
    </div>
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5">
        <div class="text-sm text-slate-500 dark:text-white/40">Draft</div>
        <div class="mt-1 text-3xl font-bold text-slate-500 dark:text-white/50">{{ $stats['draft'] }}</div>
    </div>
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5">
        <div class="text-sm text-slate-500 dark:text-white/40">Menunggu Approval</div>
        <div class="mt-1 text-3xl font-bold text-amber-500">{{ $stats['pending'] }}</div>
    </div>
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5">
        <div class="text-sm text-slate-500 dark:text-white/40">Final</div>
        <div class="mt-1 text-3xl font-bold text-primary">{{ $stats['final'] }}</div>
    </div>
</div>

<a href="{{ route('wakasek.kaldik.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-primary to-secondary px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition hover:shadow-xl hover:shadow-primary/30">
    Kelola Kalender Pendidikan
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
</a>
@endsection

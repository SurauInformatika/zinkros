@extends('layouts.app')

@section('title', 'Input Nilai')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold tracking-tight">Input Nilai</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Input nilai siswa untuk mata pelajaran yang diampu.</p>
</div>

@if (session('success'))
<div class="mb-4 rounded-lg bg-primary/10 dark:bg-primary/100/10 border border-primary/20 dark:border-primary/20 p-4 text-sm text-primary dark:text-primary">{{ session('success') }}</div>
@endif
@if (session('error'))
<div class="mb-4 rounded-lg bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 p-4 text-sm text-red-700 dark:text-red-400">{{ session('error') }}</div>
@endif

@if ($recentGrades->isNotEmpty())
<div class="mb-6 grid grid-cols-2 sm:grid-cols-4 gap-4">
    @foreach ($recentGrades as $rg)
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4">
        <div class="text-xs text-slate-500 dark:text-white/40 mb-1">{{ $rg['subject'] }}</div>
        <div class="text-lg font-bold text-primary dark:text-primary">{{ $rg['count'] }} nilai</div>
        <div class="text-xs text-slate-400 dark:text-white/30 mt-1">Rata-rata: {{ $rg['avg'] }} &middot; Tertinggi: {{ $rg['max'] }}</div>
    </div>
    @endforeach
</div>
@endif

<div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5 mb-6">
    <h2 class="font-semibold mb-4">Input Nilai Baru</h2>
    @if ($plottedClasses->isEmpty())
    <div class="text-center py-6 text-slate-400 dark:text-white/30 text-sm">Anda belum terploting mengampu mata pelajaran apapun.</div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach ($plottedClasses as $item)
        @foreach ($item['subjects'] as $subject)
        <a href="{{ route('guru.nilai.create', ['class_id' => $item['class']->id, 'subject_id' => $subject->id]) }}"
           class="flex items-center gap-3 rounded-lg border border-slate-200 dark:border-white/10 p-4 hover:border-primary/30 dark:hover:border-primary/30 transition-all duration-200 hover:shadow-lg hover:shadow-primary/5">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-primary to-secondary shadow-lg shadow-primary/25">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
                <div class="font-medium text-sm">{{ $item['class']->class_name }}</div>
                <div class="text-xs text-slate-500 dark:text-white/40">{{ $subject->name }}</div>
            </div>
            <div class="ml-auto">
                <svg class="w-4 h-4 text-slate-300 dark:text-white/15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </div>
        </a>
        @endforeach
        @endforeach
    </div>
    @endif
</div>

<div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5">
    <div class="flex items-center justify-between">
        <h2 class="font-semibold">Riwayat Nilai</h2>
        <a href="{{ route('guru.nilai.history') }}" class="text-sm text-primary dark:text-primary hover:underline">Lihat Semua</a>
    </div>
</div>
@endsection

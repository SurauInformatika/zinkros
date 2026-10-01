@extends('layouts.app')

@section('title', 'Rekap Laporan')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold tracking-tight">Rekap Laporan</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Ringkasan akademik per kelas. Klik kelas untuk melihat rekap lengkap.</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse ($classes as $class)
    <a href="{{ route('admin.rekap.class', $class->id) }}"
        class="group rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5 hover:border-primary/30 dark:hover:border-primary/30 hover:shadow-sm transition-all">
        <div class="flex items-center justify-between mb-3">
            <span class="inline-flex items-center rounded-md bg-purple-50 dark:bg-purple-500/10 px-2.5 py-0.5 text-xs font-medium text-purple-700 dark:text-purple-400">{{ $class->class_name }}</span>
            <span class="text-xs text-slate-400 dark:text-white/30">{{ $class->students_count }} siswa</span>
        </div>
        <p class="text-sm text-slate-500 dark:text-white/40 mb-3">Tingkat {{ $class->grade_level }}</p>
        @if ($class->walis->isNotEmpty())
        <p class="text-xs text-slate-400 dark:text-white/30">Wali Kelas: {{ $class->waliNames() }}</p>
        @endif
        <div class="mt-3 flex items-center gap-1 text-xs font-medium text-primary dark:text-primary opacity-0 group-hover:opacity-100 transition-opacity">
            Lihat Rekap
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </div>
    </a>
    @empty
    <div class="col-span-full rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-8 text-center">
        <svg class="w-10 h-10 mx-auto text-slate-300 dark:text-white/20 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
        <p class="text-sm text-slate-500 dark:text-white/40">Belum ada kelas.</p>
    </div>
    @endforelse
</div>
@endsection

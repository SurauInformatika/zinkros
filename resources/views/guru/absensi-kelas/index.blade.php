@extends('layouts.app')

@section('title', 'Absensi Kelas')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold tracking-tight">Absensi Kelas</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Isi absensi harian untuk kelas yang Anda wali.</p>
</div>

@if (session('success'))
<div class="mb-6 rounded-xl border border-primary/20 bg-primary/10 p-4 text-sm text-primary dark:border-primary/30 dark:bg-primary/100/10 dark:text-primary">
    {{ session('success') }}
</div>
@endif

@if (session('error'))
<div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-400">
    {{ session('error') }}
</div>
@endif

@if ($waliClasses->isEmpty())
<div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-8 text-center">
    <svg class="w-12 h-12 mx-auto mb-3 text-slate-300 dark:text-white/10" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
    <p class="text-slate-500 dark:text-white/40 font-medium">Anda belum menjadi wali kelas</p>
    <p class="text-sm text-slate-400 dark:text-white/30 mt-1">Hubungi admin untuk menetapkan Anda sebagai wali kelas.</p>
</div>
@else
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
    @foreach ($waliClasses as $item)
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5 hover:border-primary/30 dark:hover:border-primary/30 transition-all duration-200 hover:shadow-lg hover:shadow-primary/5">
        <div class="flex items-center gap-3 mb-4">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg shadow-blue-500/25">
                <span class="text-sm font-bold text-white">{{ substr($item['class']->class_name, 0, 2) }}</span>
            </div>
            <div>
                <h3 class="font-semibold">{{ $item['class']->class_name }}</h3>
                <p class="text-xs text-slate-500 dark:text-white/40">{{ $item['total_students'] }} siswa</p>
            </div>
        </div>

        @if ($item['has_today'])
        <div class="mb-3 grid grid-cols-4 gap-2 text-center text-xs">
            <div class="rounded-lg bg-primary/10 dark:bg-primary/100/10 py-1.5">
                <p class="font-bold text-primary dark:text-primary">{{ $item['hadir'] }}</p>
                <p class="text-primary/60 dark:text-primary/60">Hadir</p>
            </div>
            <div class="rounded-lg bg-blue-50 dark:bg-blue-500/10 py-1.5">
                <p class="font-bold text-blue-600 dark:text-blue-400">{{ $item['izin'] }}</p>
                <p class="text-blue-600/60 dark:text-blue-400/60">Izin</p>
            </div>
            <div class="rounded-lg bg-amber-50 dark:bg-amber-500/10 py-1.5">
                <p class="font-bold text-amber-600 dark:text-amber-400">{{ $item['sakit'] }}</p>
                <p class="text-amber-600/60 dark:text-amber-400/60">Sakit</p>
            </div>
            <div class="rounded-lg bg-red-50 dark:bg-red-500/10 py-1.5">
                <p class="font-bold text-red-600 dark:text-red-400">{{ $item['alpa'] }}</p>
                <p class="text-red-600/60 dark:text-red-400/60">Alpa</p>
            </div>
        </div>
        @endif

        <div class="flex gap-2">
            <a href="{{ route('guru.absensi-kelas.create', ['class_id' => $item['class']->id]) }}"
               class="flex-1 block rounded-lg {{ $item['has_today'] ? 'bg-amber-500 hover:bg-amber-600' : 'bg-primary/100 hover:bg-primary' }} text-white text-center py-2.5 text-sm font-medium transition-colors">
                {{ $item['has_today'] ? 'Revisi Absensi' : 'Isi Absensi' }}
            </a>
        </div>
    </div>
    @endforeach
</div>
@endif
@endsection

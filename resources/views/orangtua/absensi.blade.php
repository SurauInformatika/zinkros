@extends('layouts.app')

@section('title', 'Absensi ' . ($child?->name ?? 'Anak'))

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold">Absensi {{ $child->name }}</h1>
        <p class="text-sm text-slate-500 dark:text-white/50 mt-1">{{ $child->classRoom?->class_name ?? '-' }} · NIS {{ $child->nis }}</p>
    </div>
    @include('orangtua._child-switcher')
</div>

<p class="text-sm text-slate-500 dark:text-white/50 mb-4">Pilih jenis absensi yang ingin Anda lihat:</p>

<a href="{{ route('ortu.absensi.kelas') }}" class="block rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5 mb-4 hover:border-primary dark:hover:border-primary/40 transition group">
    <div class="flex items-center justify-between">
        <div>
            <p class="font-semibold group-hover:text-primary dark:group-hover:text-primary transition">Absensi Kelas</p>
            <p class="text-sm text-slate-500 dark:text-white/50 mt-1">Kehadiran harian siswa di kelas</p>
        </div>
        <svg class="w-5 h-5 text-slate-400 group-hover:text-primary transition" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    </div>
</a>

<a href="{{ route('ortu.absensi.mapel') }}" class="block rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5 hover:border-primary dark:hover:border-primary/40 transition group">
    <div class="flex items-center justify-between">
        <div>
            <p class="font-semibold group-hover:text-primary dark:group-hover:text-primary transition">Absensi Mapel</p>
            <p class="text-sm text-slate-500 dark:text-white/50 mt-1">Kehadiran siswa per mata pelajaran</p>
        </div>
        <svg class="w-5 h-5 text-slate-400 group-hover:text-primary transition" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    </div>
</a>
@endsection

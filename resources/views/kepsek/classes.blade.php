@extends('layouts.app')

@section('title', 'Daftar Kelas')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold tracking-tight">Daftar Kelas</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Lihat semua kelas di sekolah.</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse ($classes as $class)
    <a href="{{ route('kepsek.class-detail', $class) }}" class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5 hover:border-primary/30 dark:hover:border-primary/30 transition group">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-lg font-bold group-hover:text-primary dark:group-hover:text-primary transition">{{ $class->class_name }}</h3>
            <span class="rounded-full bg-slate-100 dark:bg-white/10 px-2.5 py-0.5 text-xs font-medium text-slate-600 dark:text-white/50">{{ $class->grade_level }}</span>
        </div>
        <div class="space-y-1 text-sm text-slate-500 dark:text-white/40">
            <div>Wali: <span class="text-slate-700 dark:text-white/60 font-medium">{{ $class->waliNames() }}</span></div>
            <div>Siswa: <span class="text-slate-700 dark:text-white/60 font-medium">{{ $class->students_count }}</span></div>
        </div>
    </a>
    @empty
    <div class="col-span-full text-center py-10 text-slate-400 dark:text-white/30 text-sm">Belum ada data kelas.</div>
    @endforelse
</div>
@endsection

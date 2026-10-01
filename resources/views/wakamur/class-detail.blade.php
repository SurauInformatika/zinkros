@extends('layouts.app')

@section('title', $kelas->class_name)

@section('content')
<div class="mb-6 flex items-center gap-3">
    <a href="{{ route('wakamur.kelas') }}" class="text-slate-400 hover:text-slate-600 dark:hover:text-white/60">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
    </a>
    <div>
        <h1 class="text-2xl font-bold tracking-tight">{{ $kelas->class_name }}</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Wali Kelas: {{ $kelas->waliNames() }} &middot; {{ $students->count() }} siswa</p>
    </div>
</div>

<div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm whitespace-nowrap">
            <thead>
                <tr class="border-b border-slate-100 dark:border-white/5 bg-slate-50 dark:bg-white/[0.02]">
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">#</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Nama</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">NIS</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">NISN</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($students as $student)
                <tr class="border-b border-slate-50 dark:border-white/5 last:border-0 hover:bg-slate-50 dark:hover:bg-white/[0.02]">
                    <td class="px-4 py-2 text-slate-400 dark:text-white/30">{{ $loop->iteration }}</td>
                    <td class="px-4 py-2 font-medium">{{ $student->name }}</td>
                    <td class="px-4 py-2 text-slate-500 dark:text-white/40">{{ $student->nis ?? '-' }}</td>
                    <td class="px-4 py-2 text-slate-500 dark:text-white/40">{{ $student->nisn ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-8 text-center text-slate-400 dark:text-white/30">Belum ada siswa.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
@extends('layouts.app')

@section('title', 'Preview Import Siswa')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.siswa.create') }}" class="text-sm text-primary dark:text-primary hover:underline">&larr; Kembali</a>
    <h1 class="mt-2 text-2xl font-bold">Preview Import Siswa</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Review data sebelum diimport.</p>
</div>

<div class="grid gap-6 lg:grid-cols-2">
    {{-- Kelas Baru --}}
    <div>
        <h2 class="text-lg font-bold text-slate-800 dark:text-white mb-3">
            Kelas Baru
            <span class="ml-2 rounded-full bg-amber-100 dark:bg-amber-500/20 px-2 py-0.5 text-xs font-semibold text-amber-700 dark:text-amber-400">{{ $newClasses->count() }}</span>
        </h2>

        @forelse ($newClasses as $class)
            <div class="mb-4 rounded-xl border border-amber-200 dark:border-amber-500/20 bg-white dark:bg-[#141414] overflow-hidden">
                <div class="flex items-center justify-between border-b border-amber-100 dark:border-amber-500/10 px-4 py-3 bg-amber-50 dark:bg-amber-500/5">
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="font-bold text-amber-800 dark:text-amber-300">{{ $class['class_name'] }}</span>
                        <span class="rounded-full bg-amber-200 dark:bg-amber-500/30 px-2 py-0.5 text-[10px] font-semibold text-amber-700 dark:text-amber-300">BARU</span>
                    </div>
                    <span class="text-xs text-amber-600 dark:text-amber-400">{{ $class['count'] }} siswa</span>
                </div>
                <div class="p-3 max-h-48 overflow-y-auto">
<div class="overflow-x-auto">                    <table class="w-full text-xs whitespace-nowrap">
                        <thead class="text-left text-slate-500 dark:text-white/40">
                            <tr><th class="pb-1">Nama</th><th class="pb-1">NIS</th><th class="pb-1">L/P</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                            @foreach ($class['students'] as $s)
                                <tr>
                                    <td class="py-1 text-slate-700 dark:text-white/70">{{ $s['nama'] }}</td>
                                    <td class="py-1 text-slate-500 dark:text-white/40">{{ $s['nis'] ?: '-' }}</td>
                                    <td class="py-1 text-slate-500 dark:text-white/40">{{ $s['gender'] ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table></div>
                </div>
            </div>
        @empty
            <div class="rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 p-6 text-center text-sm text-slate-400">
                Semua kelas sudah ada di sistem.
            </div>
        @endforelse
    </div>

    {{-- Kelas Sudah Ada --}}
    <div>
        <h2 class="text-lg font-bold text-slate-800 dark:text-white mb-3">
            Kelas Sudah Ada
            <span class="ml-2 rounded-full bg-primary/15 dark:bg-primary/100/20 px-2 py-0.5 text-xs font-semibold text-primary dark:text-primary">{{ $existingClassRows->count() }}</span>
        </h2>

        @forelse ($existingClassRows as $class)
            <div class="mb-4 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-[#141414] overflow-hidden">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-white/5 px-4 py-3">
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="font-bold text-slate-800 dark:text-white">{{ $class['class_name'] }}</span>
                    </div>
                    <span class="text-xs text-slate-500 dark:text-white/40">{{ $class['count'] }} siswa</span>
                </div>
                <div class="p-3 max-h-48 overflow-y-auto">
<div class="overflow-x-auto">                    <table class="w-full text-xs whitespace-nowrap">
                        <thead class="text-left text-slate-500 dark:text-white/40">
                            <tr><th class="pb-1">Nama</th><th class="pb-1">NIS</th><th class="pb-1">L/P</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                            @foreach ($class['students'] as $s)
                                <tr>
                                    <td class="py-1 text-slate-700 dark:text-white/70">{{ $s['nama'] }}</td>
                                    <td class="py-1 text-slate-500 dark:text-white/40">{{ $s['nis'] ?: '-' }}</td>
                                    <td class="py-1 text-slate-500 dark:text-white/40">{{ $s['gender'] ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table></div>
                </div>
            </div>
        @empty
            <div class="rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 p-6 text-center text-sm text-slate-400">
                Tidak ada kelas yang sudah ada.
            </div>
        @endforelse
    </div>
</div>

<div class="mt-8 flex items-center gap-3">
    <form method="POST" action="{{ route('admin.siswa.import.confirm') }}">
        @csrf
        <button type="submit"
            class="rounded-xl bg-gradient-to-r from-primary to-secondary px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition hover:shadow-xl hover:shadow-primary/30">
            Konfirmasi & Import
        </button>
    </form>
    <a href="{{ route('admin.siswa.create') }}" class="text-sm text-slate-600 dark:text-white/50 hover:text-slate-800">Batal</a>
</div>
@endsection

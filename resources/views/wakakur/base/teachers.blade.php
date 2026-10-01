@extends('layouts.app')

@section('title', 'Guru & Pembagian Tugas')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold tracking-tight">Guru & Pembagian Tugas</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Lihat guru dan penugasannya, atau jelajah penugasan per kelas.</p>
</div>

<div class="flex gap-1 mb-5 p-1 bg-slate-100 dark:bg-white/5 rounded-xl w-fit">
    <a href="{{ route($routeGroup . '.teachers', ['view' => 'guru']) }}"
        class="whitespace-nowrap rounded-lg px-4 py-2 text-sm font-semibold transition-all duration-150
            {{ $segment === 'guru' ? 'bg-white dark:bg-[#141414] text-slate-900 dark:text-white shadow-sm' : 'text-slate-500 dark:text-white/40 hover:text-slate-900 dark:hover:text-white' }}">
        Guru
    </a>
    <a href="{{ route($routeGroup . '.teachers', ['view' => 'kelas']) }}"
        class="whitespace-nowrap rounded-lg px-4 py-2 text-sm font-semibold transition-all duration-150
            {{ $segment === 'kelas' ? 'bg-white dark:bg-[#141414] text-slate-900 dark:text-white shadow-sm' : 'text-slate-500 dark:text-white/40 hover:text-slate-900 dark:hover:text-white' }}">
        Per Kelas
    </a>
</div>

@if ($segment === 'kelas')

<div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm whitespace-nowrap">
            <thead>
                <tr class="border-b border-slate-100 dark:border-white/5 bg-slate-50 dark:bg-white/[0.02]">
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Kelas</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Wali Kelas</th>
                    <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Mapel Diampu</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($classes as $class)
                <tr class="border-b border-slate-50 dark:border-white/5 last:border-0 hover:bg-slate-50 dark:hover:bg-white/[0.02]">
                    <td class="px-4 py-3 font-medium">{{ $class->class_name }}</td>
                    <td class="px-4 py-3 text-slate-500 dark:text-white/40">{{ $class->waliNames() }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center rounded-md bg-primary/10 dark:bg-primary/100/10 px-2 py-0.5 text-xs font-medium text-primary dark:text-primary">{{ $class->subjects_count }} mapel</span>
                    </td>
                    <td class="px-4 py-3">
                        <a href="{{ route($routeGroup . '.plotting-detail', $class) }}" class="text-primary dark:text-primary hover:underline text-xs">Lihat Detail</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-8 text-center text-slate-400 dark:text-white/30">Belum ada data.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@else

@php($selectedGender = $filters['gender'] ?? null)
@php($selectedTugas = $filters['tugas'] ?? [])
@php($selectedQ = $filters['q'] ?? '')

<form method="GET" action="{{ route($routeGroup . '.teachers') }}"
    class="mb-4 p-4 rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10">
    <input type="hidden" name="view" value="guru">
    <div class="flex flex-wrap items-end gap-4">
        <div class="flex-1 min-w-[200px]">
            <label for="q" class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Cari Guru</label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400 dark:text-white/30">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input type="text" id="q" name="q" value="{{ $selectedQ }}" placeholder="Nama atau email..."
                    class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white pl-10 pr-10 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none">
                @if ($selectedQ !== '')
                    <a href="{{ route($routeGroup . '.teachers', ['view' => 'guru']) }}" class="absolute inset-y-0 right-3 flex items-center text-slate-400 dark:text-white/30 hover:text-slate-600 dark:hover:text-white/60">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                @endif
            </div>
        </div>

        <div>
            <label for="gender" class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Jenis Kelamin</label>
            <select id="gender" name="gender"
                class="rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none">
                <option value="">Semua</option>
                <option value="L" {{ $selectedGender === 'L' ? 'selected' : '' }}>Laki-laki</option>
                <option value="P" {{ $selectedGender === 'P' ? 'selected' : '' }}>Perempuan</option>
            </select>
        </div>

        <div>
            <span class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Tugas</span>
            <div class="flex flex-wrap items-center gap-4">
                <label class="inline-flex items-center">
                    <input type="checkbox" name="tugas[]" value="wali" {{ in_array('wali', $selectedTugas, true) ? 'checked' : '' }}
                        class="rounded border-slate-300 text-primary focus:ring-primary">
                    <span class="ml-2 text-sm text-slate-600 dark:text-white/50">Wali Kelas</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="checkbox" name="tugas[]" value="mapel" {{ in_array('mapel', $selectedTugas, true) ? 'checked' : '' }}
                        class="rounded border-slate-300 text-primary focus:ring-primary">
                    <span class="ml-2 text-sm text-slate-600 dark:text-white/50">Guru Mapel</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="checkbox" name="tugas[]" value="quran" {{ in_array('quran', $selectedTugas, true) ? 'checked' : '' }}
                        class="rounded border-slate-300 text-primary focus:ring-primary">
                    <span class="ml-2 text-sm text-slate-600 dark:text-white/50">Guru Al-Quran</span>
                </label>
            </div>
        </div>

        <div class="flex items-end gap-2">
            <button type="submit"
                class="rounded-lg bg-gradient-to-r from-primary to-secondary px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition hover:shadow-xl hover:shadow-primary/30">
                Terapkan
            </button>
            <a href="{{ route($routeGroup . '.teachers', ['view' => 'guru']) }}" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-500 dark:text-white/40 hover:text-slate-700 dark:hover:text-white/70">Reset</a>
        </div>
    </div>
</form>

<div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm whitespace-nowrap">
            <thead>
                <tr class="border-b border-slate-100 dark:border-white/5 bg-slate-50 dark:bg-white/[0.02]">
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">#</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Nama</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Jenis Kelamin</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Tugas</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Email</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($teachers as $teacher)
                <tr class="border-b border-slate-50 dark:border-white/5 last:border-0 hover:bg-slate-50 dark:hover:bg-white/[0.02]">
                    <td class="px-4 py-2 text-slate-400 dark:text-white/30">{{ $loop->iteration + ($teachers->currentPage() - 1) * $teachers->perPage() }}</td>
                    <td class="px-4 py-2 font-medium">
                        @if ($teacher->isGuru())
                            <a href="{{ route($routeGroup . '.teacher-detail', $teacher) }}" class="text-primary dark:text-primary hover:underline">{{ $teacher->name }}</a>
                        @else
                            <span>{{ $teacher->name }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-2">
                        @if ($teacher->gender === 'L')
                            <span class="inline-flex items-center rounded-md bg-blue-50 dark:bg-blue-500/10 px-2 py-0.5 text-xs font-medium text-blue-700 dark:text-blue-400">Laki-laki</span>
                        @elseif ($teacher->gender === 'P')
                            <span class="inline-flex items-center rounded-md bg-pink-50 dark:bg-pink-500/10 px-2 py-0.5 text-xs font-medium text-pink-700 dark:text-pink-400">Perempuan</span>
                        @else
                            <span class="text-slate-400 dark:text-white/30">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-2">
                        @forelse ($teacher->tugas as $tugasLabel)
                            <span class="inline-flex items-center rounded-md bg-primary/10 dark:bg-primary/100/10 px-2 py-0.5 text-xs font-medium text-primary dark:text-primary mr-1 mb-1">{{ $tugasLabel }}</span>
                        @empty
                            <span class="text-slate-400 dark:text-white/30">Tanpa tugas</span>
                        @endforelse
                    </td>
                    <td class="px-4 py-2 text-slate-500 dark:text-white/40">{{ $teacher->email }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-slate-400 dark:text-white/30">Belum ada guru yang cocok.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3 flex flex-wrap items-center justify-between gap-3">
    <p class="text-xs text-slate-400 dark:text-white/30">
        Menampilkan {{ $teachers->firstItem() ?? 0 }}–{{ $teachers->lastItem() ?? 0 }} dari {{ $teachers->total() }} guru &amp; pimpinan
    </p>
    {{ $teachers->withQueryString()->links() }}
</div>

@endif
@endsection

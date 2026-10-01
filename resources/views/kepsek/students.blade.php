@extends('layouts.app')

@section('title', 'Daftar Siswa')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold tracking-tight">Daftar Siswa</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Semua siswa aktif di sekolah.</p>
</div>

<form method="GET" class="mb-4 flex flex-wrap items-center gap-3">
    <div class="relative flex-1 min-w-[200px] max-w-sm">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 dark:text-white/30" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, NIS, atau NISN..."
            class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white pl-9 pr-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none">
    </div>
    <select name="class_id"
        class="rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none">
        <option value="">Semua Kelas</option>
        @foreach ($classes as $class)
            <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->class_name }}</option>
        @endforeach
    </select>
    <select name="gender"
        class="rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none">
        <option value="">Semua Gender</option>
        <option value="L" {{ request('gender') == 'L' ? 'selected' : '' }}>Laki-laki</option>
        <option value="P" {{ request('gender') == 'P' ? 'selected' : '' }}>Perempuan</option>
    </select>
    <button type="submit"
        class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white hover:bg-primary-dark transition">
        Filter
    </button>
    @if (request()->hasAny(['search', 'class_id', 'gender']))
        <a href="{{ route('kepsek.students') }}"
            class="rounded-lg border border-slate-200 dark:border-white/10 px-3 py-2 text-xs font-semibold hover:bg-slate-50 dark:hover:bg-white/5 transition">
            Reset
        </a>
    @endif
</form>

<div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm whitespace-nowrap">
            <thead>
                <tr class="border-b border-slate-100 dark:border-white/5 bg-slate-50 dark:bg-white/[0.02]">
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">#</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Nama</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Jenis Kelamin</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Kelas</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">NIS</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">NISN</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($students as $student)
                <tr class="border-b border-slate-50 dark:border-white/5 last:border-0 hover:bg-slate-50 dark:hover:bg-white/[0.02]">
                    <td class="px-4 py-2 text-slate-400 dark:text-white/30">{{ $students->firstItem() + $loop->index }}</td>
                    <td class="px-4 py-2 font-medium">{{ $student->name }}</td>
                    <td class="px-4 py-2">
                        @if ($student->gender === 'L')
                            <span class="inline-flex items-center rounded-full bg-blue-50 dark:bg-blue-500/10 px-2.5 py-0.5 text-xs font-medium text-blue-700 dark:text-blue-400">L</span>
                        @elseif ($student->gender === 'P')
                            <span class="inline-flex items-center rounded-full bg-pink-50 dark:bg-pink-500/10 px-2.5 py-0.5 text-xs font-medium text-pink-700 dark:text-pink-400">P</span>
                        @else
                            <span class="text-slate-400">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-2 text-slate-500 dark:text-white/40">{{ $student->classRoom?->class_name ?? '-' }}</td>
                    <td class="px-4 py-2 text-slate-500 dark:text-white/40">{{ $student->nis ?? '-' }}</td>
                    <td class="px-4 py-2 text-slate-500 dark:text-white/40">{{ $student->nisn ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-slate-400 dark:text-white/30">Belum ada siswa.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">
    {{ $students->links() }}
</div>
@endsection

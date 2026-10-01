@extends('layouts.app')

@section('title', 'Kelas & Siswa')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold tracking-tight">Kelas & Siswa</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Lihat daftar kelas dan siswa beserta filternya.</p>
</div>

<div class="mb-5 flex items-center gap-1 border-b border-slate-200 dark:border-white/10">
    <a href="{{ route('wakasek.base.classes', ['tab' => 'kelas']) }}"
       class="-mb-px border-b-2 px-4 py-2.5 text-sm font-semibold transition {{ $tab === 'kelas' ? 'border-primary text-primary dark:border-primary dark:text-primary' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-white/40 dark:hover:text-white/70' }}">
        Kelas
    </a>
    <a href="{{ route('wakasek.base.classes', ['tab' => 'siswa']) }}"
       class="-mb-px border-b-2 px-4 py-2.5 text-sm font-semibold transition {{ $tab === 'siswa' ? 'border-primary text-primary dark:border-primary dark:text-primary' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-white/40 dark:hover:text-white/70' }}">
        Siswa
    </a>
</div>

@if ($tab === 'kelas')
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse ($classes as $class)
    <a href="{{ route('wakasek.base.class-detail', $class) }}" class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5 hover:border-primary/30 dark:hover:border-primary/30 transition group">
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
@else
<div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 overflow-hidden">
    <div class="p-4 border-b border-slate-100 dark:border-white/5">
        <form method="GET" action="{{ route('wakasek.base.classes') }}" class="flex flex-wrap items-center gap-3">
            <input type="hidden" name="tab" value="siswa">
            <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Cari nama, NIS, atau NISN..." class="rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#1c1c1c] px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40 flex-1 min-w-[180px]">
            <select name="kelas" class="rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#1c1c1c] px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
                <option value="">Semua Kelas</option>
                @foreach ($classes as $class)
                <option value="{{ $class->id }}" {{ $filters['kelas'] === $class->id ? 'selected' : '' }}>{{ $class->class_name }}</option>
                @endforeach
            </select>
            <select name="gender" class="rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#1c1c1c] px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
                <option value="">Semua Gender</option>
                <option value="L" {{ $filters['gender'] === 'L' ? 'selected' : '' }}>Laki-laki</option>
                <option value="P" {{ $filters['gender'] === 'P' ? 'selected' : '' }}>Perempuan</option>
            </select>
            <button type="submit" class="rounded-lg bg-primary hover:bg-primary-dark px-4 py-2 text-sm font-semibold text-white transition">Filter</button>
            <a href="{{ route('wakasek.base.classes', ['tab' => 'siswa']) }}" class="rounded-lg border border-slate-300 dark:border-white/10 px-4 py-2 text-sm text-slate-600 dark:text-white/50 transition hover:bg-slate-50 dark:hover:bg-white/5">Reset</a>
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm whitespace-nowrap">
            <thead>
                <tr class="border-b border-slate-100 dark:border-white/5 bg-slate-50 dark:bg-white/[0.02]">
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">#</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Nama</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Kelas</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">NIS</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">NISN</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Gender</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($students as $student)
                <tr class="border-b border-slate-50 dark:border-white/5 last:border-0 hover:bg-slate-50 dark:hover:bg-white/[0.02]">
                    <td class="px-4 py-2 text-slate-400 dark:text-white/30">{{ $loop->iteration }}</td>
                    <td class="px-4 py-2 font-medium">{{ $student->name }}</td>
                    <td class="px-4 py-2 text-slate-500 dark:text-white/40">{{ $student->classRoom?->class_name ?? '-' }}</td>
                    <td class="px-4 py-2 text-slate-500 dark:text-white/40">{{ $student->nis ?? '-' }}</td>
                    <td class="px-4 py-2 text-slate-500 dark:text-white/40">{{ $student->nisn ?? '-' }}</td>
                    <td class="px-4 py-2">
                        <span class="rounded-full bg-slate-100 dark:bg-white/10 px-2.5 py-0.5 text-xs font-medium text-slate-600 dark:text-white/50">{{ $student->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-slate-400 dark:text-white/30">Tidak ada siswa yang cocok.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection

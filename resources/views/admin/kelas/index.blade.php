@extends('layouts.app')

@section('title', 'Manajemen Kelas')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold tracking-tight">Manajemen Kelas</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Kelola kelas dan wali kelas.</p>
    </div>
    <a href="{{ route('admin.kelas.create') }}"
        class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-primary to-secondary px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition hover:shadow-xl hover:shadow-primary/30 hover:-translate-y-0.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        Tambah Kelas
    </a>
</div>

<div class="overflow-hidden rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10">
<div class="overflow-x-auto">    <table class="min-w-full divide-y divide-slate-200 dark:divide-white/10 text-sm whitespace-nowrap">
        <thead class="bg-slate-50 dark:bg-white/5 text-left text-xs uppercase text-slate-500 dark:text-white/40">
            <tr>
                <th class="px-5 py-3 font-semibold">Nama Kelas</th>
                <th class="px-5 py-3 font-semibold">Wali Kelas</th>
                <th class="px-5 py-3 font-semibold">Siswa</th>
                <th class="px-5 py-3 font-semibold text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
            @forelse ($classes as $class)
                <tr class="hover:bg-slate-50 dark:hover:bg-white/5 transition">
                    <td class="px-5 py-3 font-medium">{{ $class->class_name }}</td>
                    <td class="px-5 py-3 text-slate-600 dark:text-white/50">{{ $class->waliNames() }}</td>
                    <td class="px-5 py-3 text-slate-600 dark:text-white/50">{{ $class->students_count ?? 0 }}</td>
                    <td class="px-5 py-3">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('admin.kelas.edit', $class) }}"
                                class="rounded-lg border border-slate-200 dark:border-white/10 px-3 py-1.5 text-xs font-semibold hover:bg-slate-50 dark:hover:bg-white/5 transition">
                                Edit
                            </a>
                            <form method="POST" action="{{ route('admin.kelas.destroy', $class) }}"
                                onsubmit="return confirm('Hapus kelas {{ $class->class_name }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="rounded-lg border border-red-200 dark:border-red-500/20 px-3 py-1.5 text-xs font-semibold text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-5 py-10 text-center text-slate-500 dark:text-white/30">Belum ada data kelas.</td>
                </tr>
            @endforelse
        </tbody>
    </table></div>
</div>

<div class="mt-4">
    {{ $classes->links() }}
</div>
@endsection

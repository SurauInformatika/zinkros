@extends('layouts.app')

@section('title', 'Manajemen Siswa')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold tracking-tight">Manajemen Siswa</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Kelola data siswa sekolah Anda.</p>
    </div>
    <div class="flex items-center gap-3">
        <button type="button" id="btn-delete-selected"
            class="hidden items-center gap-2 rounded-xl border border-red-200 dark:border-red-500/20 bg-white dark:bg-[#141414] px-4 py-2.5 text-sm font-semibold text-red-600 dark:text-red-400 shadow transition hover:bg-red-50 dark:hover:bg-red-500/10">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            Hapus Terpilih (<span id="selected-count">0</span>)
        </button>
        <a href="{{ route('admin.siswa.create') }}"
            class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-primary to-secondary px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition hover:shadow-xl hover:shadow-primary/30 hover:-translate-y-0.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Tambah Siswa
        </a>
    </div>
</div>

@include('admin.pengguna._role-tabs', ['activeRole' => 'siswa'])

@include('admin.partials._import-form', [
    'importTitle' => 'Import Siswa',
    'templateRoute' => 'admin.excel.template.siswa',
    'importRoute' => 'admin.siswa.import.preview',
    'submitLabel' => 'Preview Import Siswa',
    'helptext' => 'Pratinjau data sebelum disimpan.',
    'accent' => 'rose',
])

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
        <a href="{{ route('admin.siswa.index') }}"
            class="rounded-lg border border-slate-200 dark:border-white/10 px-3 py-2 text-xs font-semibold hover:bg-slate-50 dark:hover:bg-white/5 transition">
            Reset
        </a>
    @endif
</form>

<div class="overflow-hidden rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10">
<div class="overflow-x-auto">    <table class="min-w-full divide-y divide-slate-200 dark:divide-white/10 text-sm whitespace-nowrap">
        <thead class="bg-slate-50 dark:bg-white/5 text-left text-xs uppercase text-slate-500 dark:text-white/40">
            <tr>
                <th class="px-5 py-3 w-10">
                    <input type="checkbox" id="select-all"
                        class="rounded border-slate-300 dark:border-white/20 text-primary focus:ring-primary bg-white dark:bg-[#0a0a0a]">
                </th>
                <th class="px-5 py-3 font-semibold w-12">No</th>
                <th class="px-5 py-3 font-semibold">Nama</th>
                <th class="px-5 py-3 font-semibold">Gender</th>
                <th class="px-5 py-3 font-semibold">NIS</th>
                <th class="px-5 py-3 font-semibold">NISN</th>
                <th class="px-5 py-3 font-semibold">Kelas</th>
                <th class="px-5 py-3 font-semibold">Orang Tua</th>
                <th class="px-5 py-3 font-semibold text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
            @forelse ($students as $i => $student)
                <tr class="hover:bg-slate-50 dark:hover:bg-white/5 transition">
                    <td class="px-5 py-3">
                        <input type="checkbox" name="student_ids[]" value="{{ $student->id }}"
                            class="student-checkbox rounded border-slate-300 dark:border-white/20 text-primary focus:ring-primary bg-white dark:bg-[#0a0a0a]">
                    </td>
                    <td class="px-5 py-3 text-slate-500 dark:text-white/40 font-medium">{{ $students->firstItem() + $i }}</td>
                    <td class="px-5 py-3 font-medium">{{ $student->name }}</td>
                    <td class="px-5 py-3 text-slate-600 dark:text-white/50">
                        @if ($student->gender === 'L')
                            <span class="inline-flex items-center rounded-full bg-blue-50 dark:bg-blue-500/10 px-2 py-0.5 text-xs font-medium text-blue-700 dark:text-blue-400">L</span>
                        @elseif ($student->gender === 'P')
                            <span class="inline-flex items-center rounded-full bg-pink-50 dark:bg-pink-500/10 px-2 py-0.5 text-xs font-medium text-pink-700 dark:text-pink-400">P</span>
                        @else
                            <span class="text-slate-400">-</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-slate-600 dark:text-white/50">{{ $student->nis ?? '-' }}</td>
                    <td class="px-5 py-3 text-slate-600 dark:text-white/50">{{ $student->nisn ?? '-' }}</td>
                    <td class="px-5 py-3 text-slate-600 dark:text-white/50">{{ $student->classRoom?->class_name ?? '-' }}</td>
                    <td class="px-5 py-3 text-slate-600 dark:text-white/50">
                        @if ($student->parents->count() === 0)
                            <span class="text-slate-400">-</span>
                        @else
                            <span class="flex flex-wrap gap-1">
                                @foreach ($student->parents as $parent)
                                    <span class="inline-flex items-center rounded-full bg-primary/10 dark:bg-primary/100/10 px-2 py-0.5 text-xs font-medium text-primary dark:text-primary">
                                        {{ $parent->name }}
                                        @if ($parent->pivot->relation)
                                            <span class="ml-1 text-primary/70 dark:text-primary/60">{{ $parent->pivot->relation }}</span>
                                        @endif
                                    </span>
                                @endforeach
                            </span>
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('admin.siswa.edit', $student) }}"
                                class="rounded-lg border border-slate-200 dark:border-white/10 px-3 py-1.5 text-xs font-semibold hover:bg-slate-50 dark:hover:bg-white/5 transition">
                                Edit
                            </a>
                            <form method="POST" action="{{ route('admin.siswa.destroy', $student) }}"
                                onsubmit="return confirm('Hapus siswa {{ $student->name }}?')">
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
                    <td colspan="9" class="px-5 py-10 text-center text-slate-500 dark:text-white/30">Belum ada data siswa.</td>
                </tr>
            @endforelse
        </tbody>
    </table></div>
</div>

<div class="mt-4">
    {{ $students->links() }}
</div>

<form id="bulk-delete-form" method="POST" action="{{ route('admin.siswa.bulk-delete') }}" class="hidden">
    @csrf
    <div id="bulk-ids-container"></div>
</form>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.student-checkbox');
    const btnDelete = document.getElementById('btn-delete-selected');
    const countSpan = document.getElementById('selected-count');
    const form = document.getElementById('bulk-delete-form');
    const idsContainer = document.getElementById('bulk-ids-container');

    function updateUI() {
        const checked = document.querySelectorAll('.student-checkbox:checked');
        const count = checked.length;
        countSpan.textContent = count;
        btnDelete.classList.toggle('hidden', count === 0);
        btnDelete.classList.toggle('inline-flex', count > 0);
        selectAll.checked = count === checkboxes.length && checkboxes.length > 0;
        selectAll.indeterminate = count > 0 && count < checkboxes.length;
    }

    selectAll.addEventListener('change', () => {
        checkboxes.forEach(cb => cb.checked = selectAll.checked);
        updateUI();
    });

    checkboxes.forEach(cb => cb.addEventListener('change', updateUI));

    btnDelete.addEventListener('click', () => {
        const checked = document.querySelectorAll('.student-checkbox:checked');
        if (checked.length === 0) return;
        if (!confirm(`Hapus ${checked.length} siswa terpilih?`)) return;

        idsContainer.innerHTML = '';
        checked.forEach(cb => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = cb.value;
            idsContainer.appendChild(input);
        });

        form.submit();
    });
});
</script>
@endpush

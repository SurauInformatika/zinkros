@extends('layouts.app')

@section('title', 'Manajemen Mata Pelajaran')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold tracking-tight">Manajemen Mata Pelajaran</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Kelola daftar mata pelajaran sekolah.</p>
        <p class="mt-1 text-xs text-slate-400 dark:text-white/30">Klik <strong>"Isi Template Mapel"</strong> untuk memuat mapel standar Indonesia, lalu edit atau hapus yang tidak dibutuhkan.</p>
    </div>
    <div class="flex flex-wrap items-center gap-3">
        <form method="POST" action="{{ route('admin.subject.template') }}">
            @csrf
            <button type="submit"
                class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-primary to-secondary px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition hover:shadow-xl hover:shadow-primary/30 hover:-translate-y-0.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                Isi Template Mapel
            </button>
        </form>
        <a href="{{ route('admin.subject.create') }}"
            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 dark:border-white/10 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-white/70 transition hover:bg-slate-50 dark:hover:bg-white/5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Tambah Mapel
        </a>
    </div>
</div>

<form method="POST" action="{{ route('admin.subject.destroy-many') }}" id="bulk-delete-form">
    @csrf
    @method('DELETE')

    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10">
        <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-3 border-b border-slate-100 dark:border-white/5">
            <div class="flex items-center gap-3">
                <label class="flex items-center gap-2 text-sm cursor-pointer">
                    <input type="checkbox" id="select-all"
                        class="rounded border-slate-300 dark:border-white/20 text-primary focus:ring-primary h-4 w-4">
                    <span class="text-slate-500 dark:text-white/40">Pilih semua</span>
                </label>
                <span class="text-xs text-slate-400 dark:text-white/30" id="selected-count">0 dipilih</span>
            </div>
            <button type="submit" id="delete-selected"
                class="inline-flex items-center gap-2 rounded-lg border border-red-200 dark:border-red-500/20 px-4 py-2 text-xs font-semibold text-red-600 dark:text-red-400 transition hover:bg-red-50 dark:hover:bg-red-500/10 disabled:opacity-40 disabled:cursor-not-allowed"
                disabled>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Hapus Terpilih
            </button>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-white/10 text-sm whitespace-nowrap">
                <thead class="bg-slate-50 dark:bg-white/5 text-left text-xs uppercase text-slate-500 dark:text-white/40">
                    <tr>
                        <th class="px-5 py-3 w-10"></th>
                        <th class="px-5 py-3 font-semibold">Nama Mapel</th>
                        <th class="px-5 py-3 font-semibold">Tipe</th>
                        <th class="px-5 py-3 font-semibold">Guru</th>
                        <th class="px-5 py-3 font-semibold">Kelas</th>
                        <th class="px-5 py-3 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                    @forelse ($subjects as $subject)
                        <tr class="hover:bg-slate-50 dark:hover:bg-white/5 transition">
                            <td class="px-5 py-3">
                                <input type="checkbox" name="ids[]" value="{{ $subject->id }}" data-row-check
                                    class="row-check rounded border-slate-300 dark:border-white/20 text-primary focus:ring-primary h-4 w-4">
                            </td>
                            <td class="px-5 py-3 font-medium">{{ $subject->name }}</td>
                            <td class="px-5 py-3">
                                @if ($subject->isQuran())
                                    <span class="rounded-full bg-purple-50 dark:bg-purple-500/10 px-2.5 py-0.5 text-xs font-semibold text-purple-700 dark:text-purple-400">Quran</span>
                                @else
                                    <span class="rounded-full bg-slate-100 dark:bg-white/5 px-2.5 py-0.5 text-xs font-semibold text-slate-600 dark:text-white/50">Umum</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-slate-600 dark:text-white/50">{{ $subject->assigned_teachers_count ?? 0 }}</td>
                            <td class="px-5 py-3 text-slate-600 dark:text-white/50">{{ $subject->classes_count ?? 0 }}</td>
                            <td class="px-5 py-3">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.subject.edit', $subject) }}"
                                        class="rounded-lg border border-slate-200 dark:border-white/10 px-3 py-1.5 text-xs font-semibold hover:bg-slate-50 dark:hover:bg-white/5 transition">
                                        Edit
                                    </a>
                                    <button type="button" data-delete-row="{{ $subject->id }}"
                                        class="rounded-lg border border-red-200 dark:border-red-500/20 px-3 py-1.5 text-xs font-semibold text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition">
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center">
                                <div class="text-slate-500 dark:text-white/30">Belum ada data mata pelajaran.</div>
                                <p class="mt-1 text-xs text-slate-400 dark:text-white/30">Klik <strong>"Isi Template Mapel"</strong> untuk memuat mapel standar, lalu edit atau hapus yang tidak dibutuhkan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</form>

<div class="mt-4">
    {{ $subjects->links() }}
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('bulk-delete-form');
    var selectAll = document.getElementById('select-all');
    var deleteBtn = document.getElementById('delete-selected');
    var countLabel = document.getElementById('selected-count');
    var rowChecks = Array.prototype.slice.call(document.querySelectorAll('[data-row-check]'));

    function refresh() {
        var n = rowChecks.filter(function (c) { return c.checked; }).length;
        countLabel.textContent = n + ' dipilih';
        deleteBtn.disabled = n === 0;
        selectAll.checked = rowChecks.length > 0 && n === rowChecks.length;
    }

    selectAll.addEventListener('change', function () {
        rowChecks.forEach(function (c) { c.checked = selectAll.checked; });
        refresh();
    });

    rowChecks.forEach(function (c) { c.addEventListener('change', refresh); });

    form.addEventListener('submit', function (e) {
        var n = rowChecks.filter(function (c) { return c.checked; }).length;
        if (n === 0) {
            e.preventDefault();
            return;
        }
        if (!confirm('Hapus ' + n + ' mata pelajaran terpilih?')) {
            e.preventDefault();
        }
    });

    document.querySelectorAll('[data-delete-row]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = btn.getAttribute('data-delete-row');
            if (!confirm('Hapus mata pelajaran ini?')) return;
            rowChecks.forEach(function (c) { c.checked = (c.value === id); });
            form.submit();
        });
    });

    refresh();
});
</script>
@endpush

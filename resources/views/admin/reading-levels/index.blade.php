@extends('layouts.app')

@section('title', 'Jenjang Baca')

@php
    $typeLabels = \App\Models\QuranReadingLevel::TYPES;
@endphp

@section('content')
<div class="mb-8">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Jenjang Baca</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Kelola jenjang/buku bacaan tilawah sekolah: Al-Qur'an (jilid/juz), Iqra, atau UMMi.</p>
        </div>
        <form method="POST" action="{{ route('admin.reading-levels.load-defaults') }}">
            @csrf
            <button type="submit"
                class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-primary to-secondary px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition hover:shadow-xl hover:shadow-primary/30">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                Muat Standar Al-Qur'an
            </button>
        </form>
    </div>
    <p class="mt-2 text-xs text-slate-400 dark:text-white/30">
        Tabel di bawah menampilkan katalog yang sedang dipakai guru di menu Input Tilawah. Kamu bisa <strong>ceklis</strong>
        satu per satu atau pakai <strong>"Pilih semua"</strong> lalu klik <strong>"Hapus Terpilih"</strong> untuk menghapus sekaligus —
        biasanya saat pindah ke metode <strong>Iqra</strong> atau <strong>UMMi</strong>. Baris <em>Bawaan</em> akan otomatis
        menjadi milik sekolah saat diubah; jika semua dihapus, katalog sekolah menjadi kosong (guru memakai baris yang kamu tambahkan sendiri).
    </p>
</div>

@if (session('success') || session('error') || $errors->any())
<div id="flash-toast" class="fixed top-4 right-4 z-50 max-w-sm w-full space-y-2">
    @if (session('success'))
    <div class="rounded-xl bg-gradient-to-r from-primary/10 to-secondary/10 dark:from-primary/10 dark:to-secondary/10 border border-primary/20 dark:border-primary/20 px-4 py-3 text-sm text-primary dark:text-primary flex items-center gap-2.5 shadow-lg">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif
    @if (session('error'))
    <div class="rounded-xl bg-gradient-to-r from-red-50 to-rose-50 dark:from-red-500/10 dark:to-rose-500/10 border border-red-200 dark:border-red-500/20 px-4 py-3 text-sm text-red-700 dark:text-red-400 flex items-center gap-2.5 shadow-lg">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        <span>{{ session('error') }}</span>
    </div>
    @endif
    @if ($errors->any())
    <div class="rounded-xl bg-gradient-to-r from-red-50 to-rose-50 dark:from-red-500/10 dark:to-rose-500/10 border border-red-200 dark:border-red-500/20 px-4 py-3 text-sm text-red-700 dark:text-red-400 shadow-lg">
        <div class="flex items-center gap-2.5 mb-1">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            <span class="font-medium">Terjadi kesalahan:</span>
        </div>
        <ul class="ml-6 list-disc text-xs space-y-0.5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
</div>
@endif

<div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 overflow-hidden mb-6">
    <div class="p-4 border-b border-slate-100 dark:border-white/5 flex items-center justify-between">
        <h2 class="font-semibold">
            Jenjang Baca Alquran
        </h2>
        <span class="text-xs text-slate-400 dark:text-white/30">Total halaman: {{ $levels->sum('pages') }} ({{ $levels->count() }} jenjang dipakai guru)</span>
    </div>

    <form method="POST" action="{{ route('admin.reading-levels.bulk-destroy') }}" id="bulkForm" class="hidden items-center justify-between gap-3 border-b border-slate-100 dark:border-white/5 bg-red-50/40 dark:bg-red-500/5 px-4 py-2.5 sm:flex">
        @csrf
        @method('DELETE')
        <select name="ids[]" id="bulkIds" multiple class="hidden"></select>
        <span class="text-sm text-slate-600 dark:text-white/60"><span id="bulkCount">0</span> jenjang terpilih</span>
            <button type="button" id="bulkButton" disabled
                class="inline-flex items-center gap-1 rounded-lg bg-red-600 hover:bg-red-700 px-3 py-1.5 text-xs font-semibold text-white transition-colors disabled:opacity-50">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Hapus Terpilih
            </button>
    </form>

    <div class="overflow-x-auto">
        <table class="w-full text-sm whitespace-nowrap">
            <thead>
            <tr class="border-b border-slate-100 dark:border-white/5 bg-slate-50 dark:bg-white/[0.02]">
                <th class="px-4 py-3 w-10">
                    <input type="checkbox" id="checkAll" title="Pilih semua"
                        class="rounded border-slate-300 dark:border-white/20 text-primary focus:ring-primary/20">
                </th>
                <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Kategori</th>
                <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">No</th>
                <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Label</th>
                <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Halaman</th>
                <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Aksi</th>
            </tr>
            </thead>
            <tbody>
                @forelse ($levels as $level)
                <tr class="border-b border-slate-50 dark:border-white/5 last:border-0 hover:bg-slate-50 dark:hover:bg-white/[0.02] transition-colors {{ $level->school_id ? '' : 'opacity-60' }}">
                    <td class="px-4 py-2.5">
                        <input type="checkbox" value="{{ $level->id }}" class="row-check rounded border-slate-300 dark:border-white/20 text-primary focus:ring-primary/20">
                    </td>
                    <td class="px-4 py-2.5 cursor-move text-slate-300 dark:text-white/20" title="{{ $level->school_id ? 'Milik sekolah' : 'Bawaan (Al-Qur\u2019an)' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 8h16M4 16h16"/></svg>
                    </td>
                    <td class="px-4 py-2.5">
                        <span class="inline-flex items-center rounded-md bg-indigo-50 dark:bg-indigo-500/10 px-2 py-0.5 text-xs font-semibold text-indigo-700 dark:text-indigo-400">{{ $typeLabels[$level->kind] ?? $level->kind }}</span>
                    </td>
                    <td class="px-4 py-2.5 text-slate-500 dark:text-white/40">{{ $level->number }}</td>
                    <td class="px-4 py-2.5">
                        <form method="POST" action="{{ route('admin.reading-levels.update', $level) }}" class="inline-flex items-center gap-2">
                            @csrf @method('PUT')
                            <input type="text" name="label" value="{{ $level->label }}"
                                class="rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-1.5 text-sm focus:border-primary focus:ring-primary/20 dark:text-white/80 w-36">
                            <span class="text-xs text-slate-400 dark:text-white/30">hal</span>
                            <input type="number" name="pages" value="{{ $level->pages }}" min="1" max="9999"
                                class="rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-1.5 text-sm text-center focus:border-primary focus:ring-primary/20 dark:text-white/80 w-16">
                            <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-primary/100 hover:bg-primary text-white px-3 py-1.5 text-xs font-medium transition-colors" data-loading-text="Menyimpan...">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                Simpan
                            </button>
                        </form>
                    </td>
                    <td class="px-4 py-2.5 text-center text-slate-600 dark:text-white/60">{{ $level->pages }}</td>
                    <td class="px-4 py-2.5">
                        <div class="flex items-center justify-center gap-1">
                            <form method="POST" action="{{ route('admin.reading-levels.destroy', $level) }}" class="single-delete-form">
                                @csrf @method('DELETE')
                                <button type="button" class="single-delete-btn inline-flex h-8 w-8 items-center justify-center rounded-lg border border-red-200 dark:border-red-500/20 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition" data-label="{{ $level->label }}" title="Hapus">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-10 text-center">
                        <div class="text-slate-400 dark:text-white/30 text-sm mb-2">Katalog jenjang sekolah kosong.</div>
                        <p class="text-xs text-slate-400 dark:text-white/30">Tambahkan jenjang Iqra/UMMi di bawah, atau klik <strong>"Muat Standar Al-Qur'an"</strong> untuk memakai jenjang bawaan.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5">
    <h3 class="font-semibold mb-4">Tambah Jenjang Baca</h3>
    <form method="POST" action="{{ route('admin.reading-levels.store') }}">
        @csrf
        <div class="grid grid-cols-1 sm:grid-cols-6 gap-4 items-end">
            <div>
                <label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Kategori</label>
                <select name="kind" required
                    class="w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-2 text-sm focus:border-primary focus:ring-primary/20 dark:text-white/80">
                    @foreach ($typeLabels as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">No</label>
                <input type="number" name="number" required min="1" max="999" placeholder="1"
                    class="w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-2 text-sm focus:border-primary focus:ring-primary/20 dark:text-white/80">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Label</label>
                <input type="text" name="label" required maxlength="100" placeholder="Iqra 1"
                    class="w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-2 text-sm focus:border-primary focus:ring-primary/20 dark:text-white/80">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Halaman</label>
                <input type="number" name="pages" required min="1" max="9999" placeholder="40"
                    class="w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-2 text-sm focus:border-primary focus:ring-primary/20 dark:text-white/80">
            </div>
            <div class="sm:col-span-2">
                <button type="submit" class="w-full rounded-lg bg-primary/100 hover:bg-primary text-white px-4 py-2 text-sm font-medium transition-colors" data-loading-text="Menambah...">
                    Tambah
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<div id="confirm-modal" class="fixed inset-0 z-50 hidden items-center justify-center">
    <div class="absolute inset-0 bg-slate-900/40 dark:bg-black/60" id="confirm-backdrop"></div>
    <div class="relative w-full max-w-sm mx-4 rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 shadow-xl">
        <div class="p-5">
            <h3 class="text-base font-semibold">Konfirmasi</h3>
            <p class="mt-1 text-sm text-slate-500 dark:text-white/40" id="confirm-message">Apakah Anda yakin?</p>
        </div>
        <div class="flex items-center justify-end gap-2 px-5 py-3 border-t border-slate-100 dark:border-white/5">
            <button type="button" id="confirm-cancel" class="rounded-lg border border-slate-200 dark:border-white/10 px-3 py-1.5 text-sm hover:bg-slate-50 dark:hover:bg-white/5">Batal</button>
            <button type="button" id="confirm-ok" class="rounded-lg bg-red-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-red-700">Hapus</button>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var flash = document.getElementById('flash-toast');
    if (flash) {
        setTimeout(function() {
            flash.style.transition = 'opacity 0.3s, transform 0.3s';
            flash.style.opacity = '0';
            flash.style.transform = 'translateY(-10px)';
            setTimeout(function() { flash.remove(); }, 300);
        }, 4000);
    }

    var checkAll = document.getElementById('checkAll');
    var rowChecks = Array.prototype.slice.call(document.querySelectorAll('.row-check'));
    var bulkForm = document.getElementById('bulkForm');
    var bulkBtn = document.getElementById('bulkButton');
    var modal = document.getElementById('confirm-modal');
    var backdrop = document.getElementById('confirm-backdrop');
    var btnCancel = document.getElementById('confirm-cancel');
    var btnOk = document.getElementById('confirm-ok');
    var msgEl = document.getElementById('confirm-message');
    var confirmAction = null;

    function openConfirm(message, action) {
        msgEl.textContent = message;
        confirmAction = action;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeConfirm() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        confirmAction = null;
    }

    if (backdrop) backdrop.addEventListener('click', closeConfirm);
    if (btnCancel) btnCancel.addEventListener('click', closeConfirm);
    if (btnOk) btnOk.addEventListener('click', function () {
        if (typeof confirmAction === 'function') confirmAction();
        closeConfirm();
    });

    function selected() {
        return rowChecks.filter(function(c) { return c.checked; });
    }

    function sync() {
        var n = selected().length;
        var countEl = document.getElementById('bulkCount');
        if (countEl) countEl.textContent = n;
        if (bulkBtn) bulkBtn.disabled = n === 0;
        if (bulkForm) bulkForm.style.display = n > 0 ? 'flex' : 'none';
        if (rowChecks.length > 0 && checkAll) {
            checkAll.checked = rowChecks.every(function(c) { return c.checked; });
            checkAll.indeterminate = n > 0 && n < rowChecks.length;
        }
    }

    if (checkAll) {
        checkAll.addEventListener('change', function() {
            rowChecks.forEach(function(c) { c.checked = checkAll.checked; });
            sync();
        });
    }

    rowChecks.forEach(function(c) { c.addEventListener('change', sync); });

    if (bulkBtn) {
        bulkBtn.addEventListener('click', function() {
            var n = selected().length;
            if (n === 0) return;
            openConfirm('Hapus ' + n + ' jenjang terpilih?', function() {
                var bulkIds = document.getElementById('bulkIds');
                if (bulkIds) {
                    bulkIds.innerHTML = '';
                    selected().forEach(function(c) {
                        var opt = document.createElement('option');
                        opt.value = c.value;
                        opt.selected = true;
                        bulkIds.appendChild(opt);
                    });
                }
                bulkForm.submit();
            });
        });
    }

    document.querySelectorAll('.single-delete-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var form = btn.closest('.single-delete-form');
            var label = btn.getAttribute('data-label') || 'jenjang ini';
            openConfirm('Hapus jenjang ' + label + '?', function() {
                form.submit();
            });
        });
    });

    sync();
});
</script>
@endpush
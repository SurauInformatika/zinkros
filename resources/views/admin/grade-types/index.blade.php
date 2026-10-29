@extends('layouts.app')

@section('title', 'Tipe Nilai')

@section('content')
<div class="mb-8">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Tipe Nilai</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Kelola tipe penilaian, bobot, dan urutan tampilan.</p>
        </div>
        <form method="POST" action="{{ route('admin.grade-types.template') }}">
            @csrf
            <button type="submit"
                class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-primary to-secondary px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition hover:shadow-xl hover:shadow-primary/30">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                Isi Template Standar
            </button>
        </form>
    </div>
    <p class="mt-2 text-xs text-slate-400 dark:text-white/30">Template berisi tipe penilaian standar. Setelah dimuat, kamu tinggal <strong>edit</strong> nama/bobot atau <strong>hapus</strong> yang tidak diperlukan.</p>
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
        <h2 class="font-semibold">Daftar Tipe Nilai</h2>
        <span class="text-xs text-slate-400 dark:text-white/30">Total bobot: {{ $gradeTypes->sum('weight') }}%</span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm whitespace-nowrap">
            <thead>
            <tr class="border-b border-slate-100 dark:border-white/5 bg-slate-50 dark:bg-white/[0.02]">
                <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40 w-8"></th>
                <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Kode</th>
                <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Nama / Bobot</th>
                <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40 w-24">Status</th>
                <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40 w-28">Aksi</th>
            </tr>
            </thead>
            <tbody id="grade-type-list">
                @forelse ($gradeTypes as $gt)
                <tr class="border-b border-slate-50 dark:border-white/5 last:border-0 hover:bg-slate-50 dark:hover:bg-white/[0.02] transition-colors" data-id="{{ $gt->id }}">
                    <td class="px-4 py-2.5 cursor-move text-slate-300 dark:text-white/20" title="Drag untuk mengurutkan">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 8h16M4 16h16"/></svg>
                    </td>
                    <td class="px-4 py-2.5">
                        <span class="inline-flex items-center rounded-md bg-slate-100 dark:bg-white/10 px-2 py-0.5 text-xs font-mono font-medium text-slate-700 dark:text-white/60">{{ $gt->code }}</span>
                    </td>
                    <td class="px-4 py-2.5">
                        <form method="POST" action="{{ route('admin.grade-types.update', $gt) }}" class="inline-flex items-center gap-2">
                            @csrf @method('PUT')
                            <input type="text" name="name" value="{{ $gt->name }}"
                                class="rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-1.5 text-sm focus:border-primary focus:ring-primary/20 dark:text-white/80 w-40">
                            <input type="number" name="weight" value="{{ $gt->weight }}" min="0" max="100" step="0.5"
                                class="rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-1.5 text-sm text-center focus:border-primary focus:ring-primary/20 dark:text-white/80 w-16">
                            <span class="text-xs text-slate-400 dark:text-white/30">%</span>
                            <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-primary/100 hover:bg-primary text-white px-3 py-1.5 text-xs font-medium transition-colors" data-loading-text="Menyimpan...">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                Simpan
                            </button>
                        </form>
                    </td>
                    <td class="px-4 py-2.5 text-center">
                        @if ($gt->is_active)
                        <span class="inline-flex items-center rounded-md bg-primary/10 dark:bg-primary/10 px-2 py-0.5 text-xs font-medium text-primary dark:text-primary">Aktif</span>
                        @else
                        <span class="inline-flex items-center rounded-md bg-slate-100 dark:bg-white/5 px-2 py-0.5 text-xs font-medium text-slate-500 dark:text-white/30">Nonaktif</span>
                        @endif
                    </td>
                    <td class="px-4 py-2.5">
                        <div class="flex items-center justify-center gap-1">
                            <form method="POST" action="{{ route('admin.grade-types.toggle', $gt) }}" class="toggle-form">
                                @csrf @method('PATCH')
                                <button type="submit" data-spin-only class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 dark:border-white/10 text-slate-600 dark:text-white/60 hover:bg-slate-50 dark:hover:bg-white/5 transition" title="{{ $gt->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.grade-types.destroy', $gt) }}" class="delete-form">
                                @csrf @method('DELETE')
                                <button type="button" class="delete-btn inline-flex h-8 w-8 items-center justify-center rounded-lg border border-red-200 dark:border-red-500/20 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition" data-name="{{ $gt->name }}" title="Hapus">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center">
                        <div class="text-slate-400 dark:text-white/30 text-sm mb-2">Belum ada tipe nilai.</div>
                        <p class="text-xs text-slate-400 dark:text-white/30">Klik <strong>"Isi Template Standar"</strong> di atas untuk memuat daftar tipe penilaian, lalu edit atau hapus yang tidak dibutuhkan.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5">
    <h3 class="font-semibold mb-4">Tambah Tipe Nilai</h3>
    <form method="POST" action="{{ route('admin.grade-types.store') }}">
        @csrf
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Nama</label>
                <input type="text" name="name" id="gt-name" required maxlength="100"
                    class="w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-2 text-sm focus:border-primary focus:ring-primary/20 dark:text-white/80"
                    placeholder="Ujian Akhir">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Kode <span class="text-[10px] text-slate-400 dark:text-white/30">(otomatis)</span></label>
                <input type="text" name="code" id="gt-code" readonly required maxlength="50"
                    class="w-full rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/[0.03] px-3 py-2 text-sm font-mono text-slate-500 dark:text-white/30 cursor-not-allowed"
                    placeholder="ujian_akhir">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Bobot (%)</label>
                <input type="number" name="weight" required min="0" max="100" step="0.5" value="0"
                    class="w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-2 text-sm focus:border-primary focus:ring-primary/20 dark:text-white/80">
            </div>
            <div>
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
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.4/Sortable.min.js"></script>
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

    var modal = document.getElementById('confirm-modal');
    var backdrop = document.getElementById('confirm-backdrop');
    var btnCancel = document.getElementById('confirm-cancel');
    var btnOk = document.getElementById('confirm-ok');
    var msgEl = document.getElementById('confirm-message');
    var confirmAction = null;

    function openConfirm(message, action) {
        if (!modal) return action();
        msgEl.textContent = message;
        confirmAction = action;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeConfirm() {
        if (!modal) return;
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

    document.querySelectorAll('.delete-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var form = btn.closest('.delete-form');
            var name = btn.getAttribute('data-name') || 'tipe ini';
            openConfirm('Hapus tipe nilai ' + name + '?', function() {
                form.submit();
            });
        });
    });

    var nameInput = document.getElementById('gt-name');
    var codeInput = document.getElementById('gt-code');
    if (nameInput && codeInput) {
        nameInput.addEventListener('input', function() {
            codeInput.value = this.value
                .toLowerCase()
                .replace(/[^a-z0-9\s_]/g, '')
                .replace(/\s+/g, '_')
                .replace(/_+/g, '_')
                .replace(/^_|_$/g, '');
        });
    }

    var list = document.getElementById('grade-type-list');
    if (list && window.Sortable) {
        Sortable.create(list, {
            handle: '.cursor-move',
            animation: 150,
            onEnd: function() {
                var order = Array.prototype.map.call(list.querySelectorAll('tr[data-id]'), function(tr) {
                    return tr.getAttribute('data-id');
                });
                fetch('{{ route('admin.grade-types.reorder') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ order: order })
                });
            }
        });
    }
});
</script>
@endpush

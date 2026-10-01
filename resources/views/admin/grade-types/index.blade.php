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
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Nama</th>
                    <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40 w-24">Bobot</th>
                    <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40 w-24">Status</th>
                    <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40 w-32">Aksi</th>
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
                                class="rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-1.5 text-sm focus:border-primary focus:ring-primary/20 dark:text-white/80 w-48">
                            <input type="number" name="weight" value="{{ $gt->weight }}" min="0" max="100" step="0.5"
                                class="rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-1.5 text-sm text-center focus:border-primary focus:ring-primary/20 dark:text-white/80 w-20">
                            <span class="text-xs text-slate-400 dark:text-white/30">%</span>
                            <button type="submit" class="rounded-lg bg-primary/100 hover:bg-primary text-white px-3 py-1.5 text-xs font-medium transition-colors">Simpan</button>
                        </form>
                    </td>
                    <td class="px-4 py-2.5 text-center">
                        @if ($gt->is_active)
                        <span class="inline-flex items-center rounded-md bg-primary/10 dark:bg-primary/100/10 px-2 py-0.5 text-xs font-medium text-primary dark:text-primary">Aktif</span>
                        @else
                        <span class="inline-flex items-center rounded-md bg-slate-100 dark:bg-white/5 px-2 py-0.5 text-xs font-medium text-slate-500 dark:text-white/30">Nonaktif</span>
                        @endif
                    </td>
                    <td class="px-4 py-2.5">
                        <div class="flex items-center justify-center gap-2">
                            <form method="POST" action="{{ route('admin.grade-types.toggle', $gt) }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="rounded-lg border border-slate-200 dark:border-white/10 px-3 py-1.5 text-xs font-medium hover:bg-slate-50 dark:hover:bg-white/5 transition-colors">
                                    {{ $gt->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.grade-types.destroy', $gt) }}" onsubmit="return confirm('Hapus tipe ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="rounded-lg border border-red-200 dark:border-red-500/20 text-red-600 dark:text-red-400 px-3 py-1.5 text-xs font-medium hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors">
                                    Hapus
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
                <button type="submit" class="w-full rounded-lg bg-primary/100 hover:bg-primary text-white px-4 py-2 text-sm font-medium transition-colors">
                    Tambah
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
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
});
</script>
@endpush

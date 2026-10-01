@extends('layouts.app')

@section('title', 'Paket Harga')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold tracking-tight">Paket Harga</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Kelola judul, subtitle, dan daftar paket pada section harga, serta toggle tampilannya di halaman beranda.</p>
</div>

@include('platform.partials.form-alert')

<form method="POST" action="{{ route('platform.content.pricing.update') }}" class="max-w-4xl space-y-6">
    @csrf
    @method('PUT')

    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-6 sm:p-8">
        <label class="mb-5 flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 p-4">
            <span>
                <span class="block text-sm font-semibold text-slate-700 dark:text-white/70">Tampilkan di landing page</span>
                <span class="block text-xs text-slate-500 dark:text-white/40 mt-0.5">Jika dinonaktifkan, seluruh section paket harga disembunyikan dari halaman beranda.</span>
            </span>
            <input type="checkbox" name="show_pricing" value="1" @checked(old('show_pricing', (bool) $settings->show_pricing))
                class="h-5 w-5 rounded border-slate-300 text-primary focus:ring-primary">
        </label>

        <div class="mb-6 grid gap-3 sm:grid-cols-2">
            <div>
                <label for="pricing_heading" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Judul</label>
                <input id="pricing_heading" type="text" name="pricing_heading" value="{{ old('pricing_heading', $settings->pricing_heading) }}" maxlength="150"
                    class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition @error('pricing_heading') border-red-400 @enderror">
            </div>
            <div>
                <label for="pricing_subtitle" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Subtitle</label>
                <input id="pricing_subtitle" type="text" name="pricing_subtitle" value="{{ old('pricing_subtitle', $settings->pricing_subtitle) }}" maxlength="300"
                    class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition @error('pricing_subtitle') border-red-400 @enderror">
            </div>
        </div>
    </div>

    @php
        $oldPlans = old('plans');
        $oldPlanList = is_array($oldPlans) ? array_values($oldPlans) : null;
        $decodedPlans = json_decode($settings->pricing_plans ?? '', true);
        $plansSource = $oldPlanList ?? (is_array($decodedPlans) && $decodedPlans !== [] ? $decodedPlans : config('platform.pricing_defaults'));
        $planRows = [];
        foreach ($plansSource as $i => $p) {
            $focus = $oldPlanList[$i] ?? $p;
            $planRows[] = [
                'index' => $i,
                'name' => $focus['name'] ?? '',
                'tagline' => $focus['tagline'] ?? '',
                'price' => $focus['price'] ?? '',
                'period' => $focus['period'] ?? '',
                'badge' => $focus['badge'] ?? '',
                'note' => $focus['note'] ?? '',
                'cta' => $focus['cta'] ?? '',
                'accent' => $focus['accent'] ?? 'none',
                'features' => $focus['features_lines'] ?? (is_array($focus['features'] ?? null) ? implode("\n", $focus['features']) : ''),
                'excludes' => $focus['excludes_lines'] ?? (is_array($focus['excludes'] ?? null) ? implode("\n", $focus['excludes']) : ''),
            ];
        }
    @endphp

    <div>
        <div class="mb-3 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-slate-700 dark:text-white/70">Daftar Paket</h2>
            <span class="text-xs text-slate-400 dark:text-white/30">Maks. 6 paket</span>
        </div>

        <div id="plans-rows" class="space-y-4">
            @foreach ($planRows as $plan)
                @include('platform.partials.plan-row', ['plan' => $plan])
            @endforeach
        </div>

        <button type="button" id="plans-add"
            class="mt-4 inline-flex items-center gap-2 rounded-xl border border-dashed border-slate-300 dark:border-white/15 px-4 py-2.5 text-sm font-medium text-slate-600 dark:text-white/50 transition hover:border-primary hover:text-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Tambah Paket
        </button>
        @error('plans')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-center gap-3 pb-8">
        <button type="submit"
            class="rounded-xl bg-gradient-to-r from-primary to-secondary px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition hover:shadow-xl hover:shadow-primary/30">
            Simpan
        </button>
        <a href="{{ route('platform.content.pricing') }}" class="rounded-xl px-4 py-2.5 text-sm font-medium text-slate-500 transition hover:text-slate-700">
            Batal
        </a>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const plansRows = document.getElementById('plans-rows');
        const plansAdd = document.getElementById('plans-add');

        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str == null ? '' : str;
            return div.innerHTML;
        }

        function textareaToValue(multiline) {
            return (multiline || '').split('\n').map((s) => escapeHtml(s)).join('\n');
        }

        function nextPlanIndex() {
            let max = -1;
            plansRows.querySelectorAll('[name^="plans["]').forEach((el) => {
                const m = el.name.match(/^plans\[(\d+)\]/);
                if (m) max = Math.max(max, parseInt(m[1], 10));
            });
            return max + 1;
        }

        function planRowHtml(index, p = { name: '', tagline: '', price: '', period: '', badge: '', note: '', cta: 'Mulai Gratis 14 Hari', accent: 'none', features: '', excludes: '' }) {
            const accentOptions = [
                ['none', 'Biasa'],
                ['primary', 'Brand (garis, menonjol)'],
                ['red', 'Brand (gradasi, premium)'],
            ];
            let accentOpts = '';
            for (const [val, label] of accentOptions) {
                accentOpts += '<option value="' + val + '"' + (val === p.accent ? ' selected' : '') + '>' + label + '</option>';
            }
            return '<div class="plan-row rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-[#141414] p-5">' +
                '<div class="flex items-start justify-between gap-3 mb-4">' +
                    '<h3 class="pt-1.5 text-sm font-semibold text-slate-700 dark:text-white/70">Paket <span class="plan-count text-primary">' + (index + 1) + '</span></h3>' +
                    '<button type="button" class="plan-remove rounded-lg p-1.5 text-slate-400 dark:text-white/30 transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-500/10 dark:hover:text-red-400" title="Hapus paket">' +
                        '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>' +
                    '</button>' +
                '</div>' +
                '<div class="grid gap-3 sm:grid-cols-2">' +
                    '<div class="sm:col-span-2 flex flex-wrap gap-3">' +
                        '<div class="flex-1 min-w-[130px]">' +
                            '<label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Nama</label>' +
                            '<input type="text" name="plans[' + index + '][name]" maxlength="50" value="' + escapeHtml(p.name) + '" placeholder="Pro" class="plan-input w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">' +
                        '</div>' +
                        '<div class="flex-1 min-w-[220px]">' +
                            '<label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Tagline</label>' +
                            '<input type="text" name="plans[' + index + '][tagline]" maxlength="200" value="' + escapeHtml(p.tagline) + '" class="plan-input w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">' +
                        '</div>' +
                    '</div>' +
                    '<div class="flex-1 min-w-[130px]">' +
                        '<label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Harga</label>' +
                        '<input type="text" name="plans[' + index + '][price]" maxlength="50" value="' + escapeHtml(p.price) + '" placeholder="Gratis / Rp 400.000" class="plan-input w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">' +
                    '</div>' +
                    '<div class="flex-1 min-w-[100px]">' +
                        '<label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Periode</label>' +
                        '<input type="text" name="plans[' + index + '][period]" maxlength="50" value="' + escapeHtml(p.period) + '" placeholder="/bulan" class="plan-input w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">' +
                    '</div>' +
                    '<div class="flex-1 min-w-[130px]">' +
                        '<label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Label Badge (opsional)</label>' +
                        '<input type="text" name="plans[' + index + '][badge]" maxlength="25" value="' + escapeHtml(p.badge) + '" placeholder="POPULER" class="plan-input w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">' +
                    '</div>' +
                    '<div class="flex-1 min-w-[130px]">' +
                        '<label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Gaya Kartu</label>' +
                        '<select name="plans[' + index + '][accent]" class="plan-input w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">' + accentOpts + '</select>' +
                    '</div>' +
                    '<div class="flex-1 min-w-[200px]">' +
                        '<label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Teks Tombol</label>' +
                        '<input type="text" name="plans[' + index + '][cta]" maxlength="60" value="' + escapeHtml(p.cta) + '" class="plan-input w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">' +
                    '</div>' +
                    '<div class="flex-1 min-w-[200px]">' +
                        '<label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Catatan (opsional)</label>' +
                        '<input type="text" name="plans[' + index + '][note]" maxlength="120" value="' + escapeHtml(p.note) + '" placeholder="Maks. 50 siswa" class="plan-input w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">' +
                    '</div>' +
                '</div>' +
                '<div class="mt-3 grid gap-3 sm:grid-cols-2">' +
                    '<div>' +
                        '<label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Fitur tersedia (satu per baris)</label>' +
                        '<textarea name="plans[' + index + '][features_lines]" rows="6" maxlength="3000" placeholder="Absensi Kelas (via ponsel)&#10;Data Siswa &amp; Guru dasar" class="plan-input w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition resize-y">' + textareaToValue(p.features) + '</textarea>' +
                    '</div>' +
                    '<div>' +
                        '<label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Fitur tidak tersedia (satu per baris)</label>' +
                        '<textarea name="plans[' + index + '][excludes_lines]" rows="6" maxlength="3000" placeholder="Gate RFID&#10;Pembayaran SPP" class="plan-input w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition resize-y">' + textareaToValue(p.excludes) + '</textarea>' +
                    '</div>' +
                '</div>' +
            '</div>';
        }

        function renumberPlanRows() {
            plansRows.querySelectorAll('.plan-row').forEach((row, idx) => {
                row.querySelectorAll('[name^="plans["]').forEach((el) => {
                    el.name = el.name.replace(/^plans\[\d+\]/, 'plans[' + idx + ']');
                });
                const count = row.querySelector('.plan-count');
                if (count) count.textContent = idx + 1;
            });
        }

        plansAdd.addEventListener('click', () => {
            plansRows.insertAdjacentHTML('beforeend', planRowHtml(nextPlanIndex()));
        });

        plansRows.addEventListener('click', (e) => {
            const btn = e.target.closest('.plan-remove');
            if (btn) {
                btn.closest('.plan-row').remove();
                renumberPlanRows();
            }
        });
    });
</script>
@endsection
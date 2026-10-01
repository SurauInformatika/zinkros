<div class="plan-row rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-[#141414] p-5">
    <div class="mb-4 flex items-start justify-between gap-3">
        <h3 class="pt-1.5 text-sm font-semibold text-slate-700 dark:text-white/70">Paket <span class="plan-count text-primary">{{ $plan['index'] + 1 }}</span></h3>
        <button type="button" class="plan-remove rounded-lg p-1.5 text-slate-400 dark:text-white/30 transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-500/10 dark:hover:text-red-400" title="Hapus paket">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
        </button>
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
        <div class="flex flex-wrap gap-3 sm:col-span-2">
            <div class="min-w-[130px] flex-1">
                <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-white/40">Nama</label>
                <input type="text" name="plans[{{ $plan['index'] }}][name]" maxlength="50"
                    value="{{ old('plans.' . $plan['index'] . '.name', $plan['name']) }}" placeholder="Pro"
                    class="plan-input w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
            </div>
            <div class="min-w-[220px] flex-1">
                <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-white/40">Tagline</label>
                <input type="text" name="plans[{{ $plan['index'] }}][tagline]" maxlength="200"
                    value="{{ old('plans.' . $plan['index'] . '.tagline', $plan['tagline']) }}"
                    class="plan-input w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
            </div>
        </div>
        <div class="min-w-[130px] flex-1">
            <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-white/40">Harga</label>
            <input type="text" name="plans[{{ $plan['index'] }}][price]" maxlength="50"
                value="{{ old('plans.' . $plan['index'] . '.price', $plan['price']) }}" placeholder="Gratis / Rp 400.000"
                class="plan-input w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
        </div>
        <div class="min-w-[100px] flex-1">
            <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-white/40">Periode</label>
            <input type="text" name="plans[{{ $plan['index'] }}][period]" maxlength="50"
                value="{{ old('plans.' . $plan['index'] . '.period', $plan['period']) }}" placeholder="/bulan"
                class="plan-input w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
        </div>
        <div class="min-w-[130px] flex-1">
            <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-white/40">Label Badge (opsional)</label>
            <input type="text" name="plans[{{ $plan['index'] }}][badge]" maxlength="25"
                value="{{ old('plans.' . $plan['index'] . '.badge', $plan['badge']) }}" placeholder="POPULER"
                class="plan-input w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
        </div>
        <div class="min-w-[130px] flex-1">
            <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-white/40">Gaya Kartu</label>
            <select name="plans[{{ $plan['index'] }}][accent]"
                class="plan-input w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
                <option value="none" @selected(old('plans.' . $plan['index'] . '.accent', $plan['accent']) === 'none')>Biasa</option>
                <option value="primary" @selected(old('plans.' . $plan['index'] . '.accent', $plan['accent']) === 'primary')>Brand (garis, menonjol)</option>
                <option value="red" @selected(old('plans.' . $plan['index'] . '.accent', $plan['accent']) === 'red')>Brand (gradasi, premium)</option>
            </select>
        </div>
        <div class="min-w-[200px] flex-1">
            <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-white/40">Teks Tombol</label>
            <input type="text" name="plans[{{ $plan['index'] }}][cta]" maxlength="60"
                value="{{ old('plans.' . $plan['index'] . '.cta', $plan['cta']) }}"
                class="plan-input w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
        </div>
        <div class="min-w-[200px] flex-1">
            <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-white/40">Catatan (opsional)</label>
            <input type="text" name="plans[{{ $plan['index'] }}][note]" maxlength="120"
                value="{{ old('plans.' . $plan['index'] . '.note', $plan['note']) }}" placeholder="Maks. 50 siswa"
                class="plan-input w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
        </div>
    </div>

    <div class="mt-3 grid gap-3 sm:grid-cols-2">
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-white/40">Fitur tersedia (satu per baris)</label>
            <textarea name="plans[{{ $plan['index'] }}][features_lines]" rows="6" maxlength="3000" placeholder="Absensi Kelas (via ponsel)&#10;Data Siswa &amp; Guru dasar"
                class="plan-input w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition resize-y">{{ old('plans.' . $plan['index'] . '.features_lines', $plan['features']) }}</textarea>
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-white/40">Fitur tidak tersedia (satu per baris)</label>
            <textarea name="plans[{{ $plan['index'] }}][excludes_lines]" rows="6" maxlength="3000" placeholder="Gate RFID&#10;Pembayaran SPP"
                class="plan-input w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition resize-y">{{ old('plans.' . $plan['index'] . '.excludes_lines', $plan['excludes']) }}</textarea>
        </div>
    </div>
</div>
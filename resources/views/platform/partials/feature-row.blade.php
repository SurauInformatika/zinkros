<div class="feature-row rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 p-4">
    <div class="flex items-start justify-between gap-3">
        <div class="flex flex-1 flex-wrap gap-3">
            <div>
                <label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Ikon</label>
                <select name="features[{{ $index }}][icon]"
                    class="w-40 rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
                    @foreach (config('platform.feature_icons') as $key => $path)
                        <option value="{{ $key }}" @selected($feature['icon'] === $key)>{{ ucwords(str_replace('-', ' ', $key)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Judul</label>
                <input type="text" name="features[{{ $index }}][title]" value="{{ $feature['title'] }}" maxlength="100"
                    class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
            </div>
        </div>
        <button type="button" class="feature-remove mt-6 rounded-lg p-1.5 text-slate-400 dark:text-white/30 transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-500/10 dark:hover:text-red-400" title="Hapus fitur">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
            </svg>
        </button>
    </div>
    <div class="mt-3">
        <label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Deskripsi</label>
        <textarea name="features[{{ $index }}][desc]" rows="2" maxlength="255"
            class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition resize-y">{{ $feature['desc'] }}</textarea>
    </div>
</div>
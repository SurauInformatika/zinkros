<div class="shot-row rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 p-4">
    <div class="flex items-start justify-between gap-3">
        <div class="flex flex-1 flex-wrap gap-3">
            <div>
                <label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Ikon</label>
                <select name="tampilan_shots[{{ $index }}][icon]"
                    class="w-40 rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
                    @foreach (config('platform.feature_icons') as $key => $path)
                        <option value="{{ $key }}" @selected($shot['icon'] === $key)>{{ ucwords(str_replace('-', ' ', $key)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Label (judul bar)</label>
                <input type="text" name="tampilan_shots[{{ $index }}][label]" value="{{ $shot['label'] }}" maxlength="100"
                    class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
            </div>
        </div>
        <button type="button" class="shot-remove mt-6 rounded-lg p-1.5 text-slate-400 dark:text-white/30 transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-500/10 dark:hover:text-red-400" title="Hapus kartu">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
            </svg>
        </button>
    </div>

    <div class="mt-3">
        <label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Caption</label>
        <input type="text" name="tampilan_shots[{{ $index }}][caption]" value="{{ $shot['caption'] }}" maxlength="200"
            class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
    </div>

    <div class="mt-3">
        <label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Gambar (screenshot)</label>
        <div class="shot-dropzone relative flex flex-col items-center justify-center w-full h-44 rounded-xl border-2 border-dashed border-slate-300 dark:border-white/15 bg-white dark:bg-[#0a0a0a] cursor-pointer transition-all duration-200 hover:border-primary dark:hover:border-primary/40 group overflow-hidden">
            <input type="file" name="tampilan_shots[{{ $index }}][image]" accept="image/png,image/jpeg,image/webp" class="shot-file absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20">
            @if ($shot['image'])
                <img src="{{ asset('storage/' . $shot['image']) }}" alt="Screenshot" class="shot-preview absolute inset-0 w-full h-full object-cover object-top">
                <div class="absolute inset-0 flex flex-col items-center justify-center bg-black/50 opacity-0 transition-opacity duration-200 group-hover:opacity-100 z-10 pointer-events-none">
                    <svg class="w-8 h-8 text-white mb-1" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                    </svg>
                    <p class="text-sm font-medium text-white">Ganti gambar</p>
                </div>
            @else
                <div class="shot-placeholder pointer-events-none relative z-10 text-center">
                    <svg class="w-10 h-10 mx-auto mb-2 text-slate-400 dark:text-white/25 group-hover:text-primary dark:group-hover:text-primary transition" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                    </svg>
                    <p class="text-sm font-medium text-slate-600 dark:text-white/50 group-hover:text-primary dark:group-hover:text-primary transition">
                        <span class="font-semibold">Klik untuk upload</span> screenshot
                    </p>
                    <p class="mt-1 text-xs text-slate-400 dark:text-white/30">PNG, JPG, atau WEBP. Maks 1MB.</p>
                </div>
            @endif
            @if ($shot['image'])
                <label class="shot-remove-image relative z-30 mt-2 pointer-events-auto inline-flex items-center gap-1.5 rounded-lg bg-white/90 dark:bg-black/60 px-2.5 py-1.5 text-xs font-medium text-red-600 dark:text-red-400 shadow-sm">
                    <input type="checkbox" name="tampilan_shots[{{ $index }}][remove_image]" value="1" class="accent-red-600">
                    Hapus gambar
                </label>
            @endif
        </div>
        @error('tampilan_shots.*.image')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>
</div>
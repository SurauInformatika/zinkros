<div class="parent-selected" data-idx="{{ $idx }}">
    <input type="hidden" name="parent_user_ids[]" value="{{ $id }}">
    <input type="hidden" name="parent_names[]" value="{{ $name }}">
    <div class="flex flex-col sm:flex-row sm:items-center gap-2 p-3 rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50/70 dark:bg-white/5">
        <div class="flex-1 min-w-0">
            <p class="text-sm font-medium truncate">{{ $name }}</p>
        </div>
        <select name="parent_relations[]" class="rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20">
            <option value="AYAH" {{ $relation == 'AYAH' ? 'selected' : '' }}>Ayah</option>
            <option value="IBU" {{ $relation == 'IBU' ? 'selected' : '' }}>Ibu</option>
            <option value="WALI" {{ $relation == 'WALI' ? 'selected' : '' }}>Wali</option>
        </select>
        <label class="inline-flex items-center whitespace-nowrap">
            <input type="radio" name="primary_parent" value="{{ $idx }}" {{ $isPrimary ? 'checked' : '' }}
                class="rounded-full border-slate-300 text-primary focus:ring-primary">
            <span class="ml-1 text-xs text-slate-600 dark:text-white/50">Utama</span>
        </label>
        <button type="button" onclick="removeSelectedParent(this)" class="text-red-400 hover:text-red-600 text-base leading-none">&times;</button>
    </div>
</div>

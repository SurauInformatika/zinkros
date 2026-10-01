@extends('layouts.app')

@section('title', 'Edit Template KALDIK')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold tracking-tight">Edit Template KALDIK</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-white/40">{{ $template->name }}</p>
</div>

@if ($errors->any())
<div class="mb-4 rounded-lg bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 p-4 text-sm text-red-700 dark:text-red-400">
    <ul class="list-disc list-inside">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="max-w-2xl rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-6 sm:p-8">
    <form method="POST" action="{{ route('platform.kaldik-templates.update', $template) }}" class="space-y-5">
        @csrf @method('PUT')

        <div>
            <label for="name" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Nama Template</label>
            <input id="name" type="text" name="name" value="{{ old('name', $template->name) }}" required
                class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition @error('name') border-red-400 @enderror">
            @error('name')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="source" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Sumber</label>
            <select id="source" name="source" required
                class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition @error('source') border-red-400 @enderror">
                <option value="custom" {{ old('source', $template->source) === 'custom' ? 'selected' : '' }}>Custom</option>
                <option value="kemenag" {{ old('source', $template->source) === 'kemenag' ? 'selected' : '' }}>Kemenag</option>
                <option value="dindik" {{ old('source', $template->source) === 'dindik' ? 'selected' : '' }}>Dinas Pendidikan</option>
            </select>
            @error('source')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Struktur JP per Hari (JSON)</label>
            <textarea name="default_day_structure" rows="3"
                class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm font-mono focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition @error('default_day_structure') border-red-400 @enderror">{{ is_array($template->default_day_structure) ? json_encode($template->default_day_structure, JSON_PRETTY_PRINT) : old('default_day_structure') }}</textarea>
            @error('default_day_structure')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Struktur Durasi per Level (JSON)</label>
            <textarea name="default_level_structure" rows="3"
                class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm font-mono focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition @error('default_level_structure') border-red-400 @enderror">{{ is_array($template->default_level_structure) ? json_encode($template->default_level_structure, JSON_PRETTY_PRINT) : old('default_level_structure') }}</textarea>
            @error('default_level_structure')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Libur Default (JSON)</label>
            <textarea name="default_holidays" rows="3"
                class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm font-mono focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition @error('default_holidays') border-red-400 @enderror">{{ is_array($template->default_holidays) ? json_encode($template->default_holidays, JSON_PRETTY_PRINT) : old('default_holidays') }}</textarea>
            @error('default_holidays')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center gap-2">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" {{ $template->is_active ? 'checked' : '' }}
                class="rounded border-slate-300 dark:border-white/10 text-primary focus:ring-primary/20">
            <label class="text-sm text-slate-700 dark:text-white/70">Aktif</label>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit"
                class="rounded-xl bg-gradient-to-r from-primary to-secondary px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition hover:shadow-xl hover:shadow-primary/30">
                Simpan Perubahan
            </button>
            <a href="{{ route('platform.kaldik-templates.index') }}" class="text-sm text-slate-500 hover:text-slate-700 dark:text-white/40 dark:hover:text-white/60">Batal</a>
        </div>
    </form>
</div>
@endsection

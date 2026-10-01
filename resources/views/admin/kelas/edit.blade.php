@extends('layouts.app')

@section('title', 'Edit Kelas')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.kelas.index') }}" class="text-sm text-primary dark:text-primary hover:underline">&larr; Kembali ke daftar kelas</a>
    <h1 class="mt-2 text-2xl font-bold">Edit Kelas</h1>
</div>

<div class="max-w-2xl rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-6 sm:p-8">
    <form method="POST" action="{{ route('admin.kelas.update', $kelas) }}" class="space-y-5">
        @csrf
        @method('PUT')

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="class_name" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Nama Kelas</label>
                <input id="class_name" type="text" name="class_name" value="{{ old('class_name', $kelas->class_name) }}" required autofocus
                    placeholder="Contoh: 7A, TK A"
                    class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none @error('class_name') border-red-400 @enderror">
                @error('class_name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="grade_level" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Jenjang</label>
                <select id="grade_level" name="grade_level" required
                    class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none @error('grade_level') border-red-400 @enderror">
                    <option value="">Pilih jenjang</option>
                    @foreach ($gradeOptions as $group)
                        <optgroup label="{{ $group['label'] }}">
                            @foreach ($group['levels'] as $value => $label)
                                <option value="{{ $value }}" {{ (string) old('grade_level', $kelas->grade_level ?? '') === (string) $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                @error('grade_level')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        @php
            $oldWali = old('wali', [0 => [], 1 => []]);
            $existingWali = $kelas->homerooms->mapWithKeys(fn ($h) => [($h->sort - 1) => ['user_id' => $h->user_id, 'label' => $h->label]]);
            $waliSlots = [0 => [], 1 => []];
            foreach ([0, 1] as $slot) {
                $waliSlots[$slot] = array_merge($existingWali[$slot] ?? [], $oldWali[$slot] ?? []);
            }
        @endphp
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-2">Wali Kelas <span class="text-xs font-normal text-slate-400">(maksimal 2, opsional)</span></label>
            @foreach ([0, 1] as $slot)
                <div class="mb-3 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/5 p-3 space-y-2">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold text-slate-500 dark:text-white/40 uppercase">Wali {{ $slot + 1 }}</p>
                    </div>
                    <div>
                        <label for="wali_{{ $slot }}_user_id" class="block text-xs text-slate-500 dark:text-white/50 mb-1">Guru</label>
                        <select id="wali_{{ $slot }}_user_id" name="wali[{{ $slot }}][user_id]"
                            class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none @error('wali.'.$slot.'.user_id') border-red-400 @enderror">
                            <option value="">Belum ditentukan</option>
                            @foreach ($waliKelasList as $guru)
                                <option value="{{ $guru->id }}" {{ ($waliSlots[$slot]['user_id'] ?? null) === $guru->id ? 'selected' : '' }}>
                                    {{ $guru->name }} @if ($guru->is_wali_kelas)(Wali Kelas)@endif
                                </option>
                            @endforeach
                        </select>
                        @error('wali.'.$slot.'.user_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="wali_{{ $slot }}_label" class="block text-xs text-slate-500 dark:text-white/50 mb-1">Label / Jabatan <span class="text-slate-400">(opsional)</span></label>
                        <input id="wali_{{ $slot }}_label" type="text" name="wali[{{ $slot }}][label]" value="{{ $waliSlots[$slot]['label'] ?? '' }}"
                            placeholder="Contoh: Wali Kelas Kurikulum, Wali Kelas Keislaman"
                            class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none">
                    </div>
                </div>
            @endforeach
            @error('wali')
                <p class="text-xs text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-xs text-slate-400 dark:text-white/30">Kosongkan guru untuk menghapus wali tersebut.</p>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit"
                class="rounded-xl bg-gradient-to-r from-primary to-secondary px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition hover:shadow-xl hover:shadow-primary/30">
                Simpan Perubahan
            </button>
            <a href="{{ route('admin.kelas.index') }}" class="text-sm text-slate-600 dark:text-white/50 hover:text-slate-800">Batal</a>
        </div>
    </form>
</div>
@endsection

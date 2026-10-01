@extends('layouts.app')

@section('title', 'Tambah Mata Pelajaran')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.subject.index') }}" class="text-sm text-primary dark:text-primary hover:underline">&larr; Kembali ke daftar mapel</a>
    <h1 class="mt-2 text-2xl font-bold">Tambah Mata Pelajaran</h1>
</div>

<div class="max-w-xl rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-6 sm:p-8">
    <form method="POST" action="{{ route('admin.subject.store') }}" class="space-y-5">
        @csrf

        <div>
            <label for="name" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Nama Mata Pelajaran</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                placeholder="Contoh: Matematika, Bahasa Arab, Tahfidz"
                class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none @error('name') border-red-400 @enderror">
            @error('name')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Tipe</label>
            <div class="space-y-2">
                <label class="inline-flex items-center">
                    <input type="radio" name="type" value="{{ \App\Models\Subject::TYPE_GENERAL }}"
                        {{ old('type', \App\Models\Subject::TYPE_GENERAL) === \App\Models\Subject::TYPE_GENERAL ? 'checked' : '' }}
                        class="rounded-full border-slate-300 dark:border-white/20 text-primary focus:ring-primary">
                    <span class="ml-2 text-sm text-slate-600 dark:text-white/50">Umum (matapelajaran reguler)</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="radio" name="type" value="{{ \App\Models\Subject::TYPE_QURAN }}"
                        {{ old('type') === \App\Models\Subject::TYPE_QURAN ? 'checked' : '' }}
                        class="rounded-full border-slate-300 dark:border-white/20 text-primary focus:ring-primary">
                    <span class="ml-2 text-sm text-slate-600 dark:text-white/50">Quran (Tahfidz/Tahsin)</span>
                </label>
            </div>
            @error('type')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit"
                class="rounded-xl bg-gradient-to-r from-primary to-secondary px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition hover:shadow-xl hover:shadow-primary/30">
                Simpan Mapel
            </button>
            <a href="{{ route('admin.subject.index') }}" class="text-sm text-slate-600 dark:text-white/50 hover:text-slate-800">Batal</a>
        </div>
    </form>
</div>
@endsection

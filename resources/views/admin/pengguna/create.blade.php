@extends('layouts.app')

@php
    $school = auth()->user()->school;
    $wakasekGroup = \App\Http\Controllers\Admin\PenggunaController::WAKASEK_GROUP;
    $isWakasekGroup = $role === $wakasekGroup || $role === \App\Models\User::ROLE_WAKASEK;
    $wakasekPositions = \App\Models\User::WAKASEK_POSITIONS;
    $roleLabel = $school->roleLabel($isWakasekGroup ? \App\Models\User::ROLE_WAKASEK : $role);
    $selectedJabatan = old('position', $isWakasekGroup ? \App\Models\User::POSITION_KURIKULUM : '');
@endphp

@section('title', 'Tambah ' . $roleLabel)

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.pengguna.index', ['role' => $role]) }}" class="text-sm text-primary dark:text-primary hover:underline">&larr; Kembali ke daftar</a>
    <h1 class="mt-2 text-2xl font-bold">Tambah {{ $roleLabel }}</h1>
</div>

<div class="max-w-xl rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-6 sm:p-8">
    <form method="POST" action="{{ route('admin.pengguna.store') }}" class="space-y-5">
        @csrf
        <input type="hidden" name="role" value="{{ $isWakasekGroup ? \App\Models\User::ROLE_WAKASEK : $role }}">

        @if ($isWakasekGroup)
        <div>
            <label for="jabatan" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Jabatan</label>
            <input type="hidden" name="position" id="position_hidden" value="{{ $selectedJabatan }}">
            <select id="jabatan"
                class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none">
                @foreach ($wakasekPositions as $key => $label)
                    <option value="{{ $key }}" data-value="{{ $key }}" @selected(old('position', $selectedJabatan) === $key)>{{ $label }}</option>
                @endforeach
                <option value="__custom__" @selected(! in_array(old('position', $selectedJabatan), array_keys($wakasekPositions), true))>Jabatan Lainnya (tulis manual)</option>
            </select>
            <input id="custom_position" type="text" placeholder="Tulis jabatan lain..."
                value="{{ ! in_array(old('position', ''), array_keys($wakasekPositions), true) ? old('position') : '' }}"
                class="mt-2 w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none">
            @error('position')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
        @endif

        <div>
            <label for="name" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Nama Lengkap</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none @error('name') border-red-400 @enderror">
            @error('name')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="gender" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Jenis Kelamin</label>
            <select id="gender" name="gender"
                class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none @error('gender') border-red-400 @enderror">
                <option value="">— Pilih —</option>
                <option value="L" {{ old('gender') === 'L' ? 'selected' : '' }}>Laki-laki</option>
                <option value="P" {{ old('gender') === 'P' ? 'selected' : '' }}>Perempuan</option>
            </select>
            @error('gender')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required
                class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none @error('email') border-red-400 @enderror">
            @error('email')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Password</label>
            <input id="password" type="password" name="password" required
                class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none @error('password') border-red-400 @enderror">
            @error('password')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Konfirmasi Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required
                class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none">
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit"
                class="rounded-xl bg-gradient-to-r from-primary to-secondary px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition hover:shadow-xl hover:shadow-primary/30">
                Simpan Akun
            </button>
            <a href="{{ route('admin.pengguna.index', ['role' => $role]) }}" class="text-sm text-slate-600 dark:text-white/50 hover:text-slate-800">Batal</a>
        </div>
    </form>
</div>

@if ($isWakasekGroup)
<script>
(function () {
    var select = document.getElementById('jabatan');
    var hidden = document.getElementById('position_hidden');
    var custom = document.getElementById('custom_position');

    function sync() {
        if (select.value === '__custom__') {
            custom.classList.remove('hidden');
            custom.focus();
        } else {
            custom.classList.add('hidden');
            hidden.value = select.value;
        }
    }

    select.addEventListener('change', sync);
    custom.addEventListener('input', function () {
        if (select.value === '__custom__') {
            hidden.value = custom.value;
        }
    });
    sync();
})();
</script>
@endif
@endsection

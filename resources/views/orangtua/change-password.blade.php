@extends('layouts.app')

@section('title', 'Ubah Password')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold tracking-tight">Ubah Password</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-white/40">
        Demi keamanan akun, silakan buat password baru Anda sebelum melanjutkan.
    </p>
</div>

<div class="max-w-xl rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-6 sm:p-8">
    <h2 class="text-lg font-semibold mb-1">Buat Password Baru</h2>
    <p class="text-sm text-slate-500 dark:text-white/40 mb-6">
        Password awal Anda bersifat sementara. Ganti dengan password yang lebih kuat.
    </p>

    <form method="POST" action="{{ route('ortu.password.change.store') }}" class="space-y-5">
        @csrf

        <div>
            <label for="current_password" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Password Saat Ini</label>
            <input id="current_password" type="password" name="current_password" required
                class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition @error('current_password') border-red-400 @enderror">
            @error('current_password')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Password Baru</label>
            <input id="password" type="password" name="password" required
                class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition @error('password') border-red-400 @enderror">
            @error('password')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Konfirmasi Password Baru</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required
                class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit"
                class="rounded-xl bg-gradient-to-r from-primary to-secondary px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition hover:shadow-xl hover:shadow-primary/30">
                Ubah Password
            </button>
        </div>
    </form>
</div>
@endsection

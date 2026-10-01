@extends('layouts.guest')

@section('title', 'Lupa Password - ' . \App\Models\PlatformSetting::appName())

@section('content')
<div class="min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-primary to-secondary shadow-lg shadow-primary/25 mb-4">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
            </div>
            <h1 class="text-2xl font-bold tracking-tight">Lupa Password</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Masukkan email Anda untuk menerima tautan reset password</p>
        </div>

        <div class="bg-white dark:bg-[#141414] rounded-2xl shadow-sm border border-slate-200 dark:border-white/10 p-8">
            <div class="flex justify-end mb-2">
                <button onclick="toggleDarkMode()" class="p-2 -mt-2 -mr-2 rounded-lg text-slate-400 dark:text-white/40 hover:bg-slate-100 dark:hover:bg-white/10 transition" title="Toggle dark mode">
                    <svg class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <svg class="w-5 h-5 block dark:hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                </button>
            </div>

            @if (session('status'))
                <div class="mb-4 rounded-xl bg-gradient-to-r from-primary/10 to-secondary/10 dark:from-primary/10 dark:to-secondary/10 border border-primary/20 dark:border-primary/20 px-4 py-3 text-sm text-primary dark:text-primary">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('auth.forgot.post') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition placeholder:text-slate-400 dark:placeholder:text-white/30 @error('email') border-red-400 @enderror">
                    @error('email')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                    class="w-full rounded-xl bg-gradient-to-r from-primary to-secondary hover:from-primary-dark hover:to-secondary-dark px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition hover:shadow-xl hover:shadow-primary/30">
                    Kirim Tautan Reset
                </button>

                <p class="text-center text-sm">
                    <a href="{{ route('auth.login') }}" class="text-primary dark:text-primary hover:underline">Kembali ke halaman masuk</a>
                </p>
            </form>
        </div>
    </div>
</div>
@endsection

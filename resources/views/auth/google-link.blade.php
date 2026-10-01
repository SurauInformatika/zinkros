@extends('layouts.guest')

@section('title', 'Hubungkan Akun Google - ' . \App\Models\PlatformSetting::appName())

@section('content')
<div class="min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-primary to-secondary shadow-lg shadow-primary/25 mb-4">
                <svg class="w-7 h-7 text-white" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.27-4.74 3.27-8.1z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84A11 11 0 0 0 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.1a6.56 6.56 0 0 1 0-4.2V7.06H2.18a11 11 0 0 0 0 9.88l3.66-2.84z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1A11 11 0 0 0 2.18 7.06l3.66 2.84C6.71 7.31 9.14 5.38 12 5.38z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold tracking-tight">{{ \App\Models\PlatformSetting::appName() }}</h1>
        </div>

        <div class="bg-white dark:bg-[#141414] rounded-2xl shadow-sm border border-slate-200 dark:border-white/10 p-8">
            <div class="flex items-center gap-4 mb-5">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-slate-100 dark:bg-white/10 overflow-hidden">
                    @if ($pending['avatar'])
                        <img src="{{ $pending['avatar'] }}" alt="" class="h-full w-full object-cover">
                    @else
                        <svg class="h-6 w-6 text-slate-400 dark:text-white/40" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                    @endif
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-slate-800 dark:text-white truncate">{{ $pending['name'] }}</p>
                    <p class="text-sm text-slate-500 dark:text-white/40 truncate">{{ $pending['email'] }}</p>
                </div>
            </div>

            @if (session('error'))
                <div class="mb-4 rounded-xl bg-gradient-to-r from-red-50 to-rose-50 dark:from-red-500/10 dark:to-rose-500/10 border border-red-200 dark:border-red-500/20 px-4 py-3 text-sm text-red-700 dark:text-red-400">
                    {{ session('error') }}
                </div>
            @endif

            <h2 class="text-base font-semibold mb-2">Akun sudah terdaftar</h2>
            <p class="text-sm text-slate-500 dark:text-white/40 mb-6">
                Email <b class="text-slate-700 dark:text-white/70">{{ $pending['email'] }}</b> sudah terdaftar di
                {{ \App\Models\PlatformSetting::appName() }}. Untuk menghubungkan akun Google Anda, masukkan
                password aplikasi akun tersebut.
            </p>

            <form method="POST" action="{{ route('auth.google.link.post') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="email" value="{{ $pending['email'] }}">

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Password aplikasi</label>
                    <input id="password" type="password" name="password" required autofocus autocomplete="current-password"
                        class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition placeholder:text-slate-400 dark:placeholder:text-white/30 @error('password') border-red-400 @enderror">
                    @error('password')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                    class="w-full rounded-xl bg-gradient-to-r from-primary to-secondary hover:from-primary-dark hover:to-secondary-dark px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition hover:shadow-xl hover:shadow-primary/30">
                    Hubungkan &amp; Masuk
                </button>
            </form>

            <form method="POST" action="{{ route('auth.google.link.cancel') }}" class="mt-3">
                @csrf
                <button type="submit"
                    class="w-full rounded-xl border border-slate-300 dark:border-white/15 px-4 py-2.5 text-sm font-medium text-slate-600 dark:text-white/60 hover:bg-slate-50 dark:hover:bg-white/5 transition">
                    Batal
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
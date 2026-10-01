@extends('layouts.guest')

@section('title', 'Masuk - ' . \App\Models\PlatformSetting::appName())

@section('content')
<div class="min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-primary to-secondary shadow-lg shadow-primary/25 mb-4">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            </div>
<h1 class="text-2xl font-bold tracking-tight">{{ \App\Models\PlatformSetting::appName() }}</h1>
@if (\App\Models\PlatformSetting::tagline())
<p class="mt-1 text-sm text-slate-500 dark:text-white/40">{{ \App\Models\PlatformSetting::tagline() }}</p>
@endif
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

            @if (session('error'))
                <div class="mb-4 rounded-xl bg-gradient-to-r from-red-50 to-rose-50 dark:from-red-500/10 dark:to-rose-500/10 border border-red-200 dark:border-red-500/20 px-4 py-3 text-sm text-red-700 dark:text-red-400">
                    {{ session('error') }}
                </div>
            @endif

            <h2 class="text-lg font-semibold mb-6">Masuk ke Akun Anda</h2>

            <form method="POST" action="{{ route('auth.login.post') }}" id="loginForm" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email"
                        class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition placeholder:text-slate-400 dark:placeholder:text-white/30 @error('email') border-red-400 @enderror">
                    @error('email')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Password</label>
                    <input id="password" type="password" name="password" required autocomplete="current-password"
                        class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition placeholder:text-slate-400 dark:placeholder:text-white/30">
                    @error('password')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between text-sm">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="remember" value="1"
                            class="rounded border-slate-300 dark:border-white/20 text-primary focus:ring-primary">
                        <span class="ml-2 text-slate-600 dark:text-white/50">Ingat saya</span>
                    </label>
                    <a href="{{ route('auth.forgot') }}" class="text-primary dark:text-primary hover:underline">Lupa password?</a>
                </div>

                <button type="submit" id="loginButton"
                    class="w-full rounded-xl bg-gradient-to-r from-primary to-secondary hover:from-primary-dark hover:to-secondary-dark px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition hover:shadow-xl hover:shadow-primary/30">
                    <span class="inline-flex items-center justify-center gap-2">
                        <svg id="loginSpinner" class="w-4 h-4 animate-spin hidden" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span id="loginLabel">Masuk</span>
                    </span>
                </button>
            </form>

            <div class="flex items-center gap-3 my-4">
                <div class="flex-1 h-px bg-slate-200 dark:bg-white/10"></div>
                <span class="text-xs text-slate-400 dark:text-white/30">atau</span>
                <div class="flex-1 h-px bg-slate-200 dark:bg-white/10"></div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @if (config('services.google.client_id'))
                    <a href="{{ route('auth.google.redirect') }}" id="googleButton" title="Masuk dengan Google"
                        class="flex items-center justify-center w-full rounded-xl border border-slate-300 dark:border-white/15 bg-white dark:bg-[#0a0a0a] px-4 py-2.5 text-sm font-medium text-slate-700 dark:text-white/80 hover:bg-slate-50 dark:hover:bg-white/5 transition">
                        <svg class="w-5 h-5" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.27-4.74 3.27-8.1z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84A11 11 0 0 0 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.1a6.56 6.56 0 0 1 0-4.2V7.06H2.18a11 11 0 0 0 0 9.88l3.66-2.84z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1A11 11 0 0 0 2.18 7.06l3.66 2.84C6.71 7.31 9.14 5.38 12 5.38z"/>
                        </svg>
                    </a>
                @endif
                <button type="button" id="passkeyButton" title="Masuk dengan Passkey"
                    class="flex items-center justify-center w-full rounded-xl border border-slate-300 dark:border-white/15 bg-white dark:bg-[#0a0a0a] px-4 py-2.5 text-sm font-medium text-slate-700 dark:text-white/80 hover:bg-slate-50 dark:hover:bg-white/5 transition">
                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 10a2 2 0 0 0-2 2c0 1.02-.1 2.51-.26 4"/><path d="M14 13.12c0 2.38 0 6.38-1 8.88"/><path d="M17.29 21.02c.12-.6.43-2.3.5-3.02"/><path d="M2 12a10 10 0 0 1 18-6"/><path d="M2 16h.01"/><path d="M21.8 16c.2-2 .131-5.354 0-6"/><path d="M5 19.5C5.5 18 6 15 6 12a6 6 0 0 1 .34-2"/><path d="M8.65 22c.21-.66.45-1.32.57-2"/><path d="M9 6.8a6 6 0 0 1 9 5.2v2"/></svg>
                </button>
            </div>
            <p id="passkeyError" class="hidden mt-3 text-center text-xs text-red-600 dark:text-red-400"></p>

            <p class="mt-6 text-center text-sm text-slate-500 dark:text-white/40">
                Belum punya akun sekolah?
                <a href="{{ route('auth.register') }}" class="text-primary dark:text-primary hover:underline">Daftar sekolah Anda</a>
            </p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('loginForm');
    var button = document.getElementById('loginButton');

    form.addEventListener('submit', function() {
        button.disabled = true;
        button.classList.add('opacity-75', 'cursor-wait');
        var spinner = '<svg class="w-4 h-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
        button.innerHTML = '<span class="inline-flex items-center justify-center gap-2">' + spinner + '<span>Memproses</span></span>';
    });
});
</script>

<script src="{{ asset('vendor/webauthn/webauthn.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('passkeyButton');
    var errorBox = document.getElementById('passkeyError');
    if (!btn) return;

    function showError(message) {
        errorBox.textContent = message;
        errorBox.classList.remove('hidden');
    }

    function csrfToken() {
        return document.querySelector('meta[name="csrf-token"]').content;
    }

    btn.addEventListener('click', function () {
        errorBox.classList.add('hidden');

        if (!window.PublicKeyCredential) {
            showError('Browser ini tidak mendukung login passkey.');
            return;
        }

        btn.disabled = true;

        fetch("{{ route('webauthn.auth.options') }}", {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: JSON.stringify({}),
        })
        .then(function (response) {
            if (response.status === 422) {
                showError('Email tersebut belum memiliki passkey. Masuk dulu dengan password, lalu daftarkan passkey di halaman Keamanan Akun.');
                btn.disabled = false;
                return null;
            }
            return response.json();
        })
        .then(function (data) {
            if (!data) return;

            var webauthn = new WebAuthn(function (name, message) {
                showError(name === 'NotAllowedError'
                    ? 'Autentikasi dibatalkan atau perangkat tidak dikenali.'
                    : message);
            });

            webauthn.sign(data.publicKey, function (credential) {
                fetch("{{ route('webauthn.auth') }}", {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken(),
                    },
                    body: JSON.stringify(credential),
                })
                .then(function (response) {
                    return response.json().then(function (body) {
                        return { ok: response.ok, body: body };
                    });
                })
                .then(function (result) {
                    if (result.ok && result.body.callback) {
                        window.location.href = result.body.callback;
                        return;
                    }
                    var first = Object.values((result.body || {}).errors || {})[0];
                    showError(first ? first[0] : 'Autentikasi gagal. Coba lagi.');
                    btn.disabled = false;
                })
                .catch(function () {
                    showError('Autentikasi gagal. Coba lagi.');
                    btn.disabled = false;
                });
            });
        })
        .catch(function () {
            showError('Gagal menghubungi server. Coba lagi.');
            btn.disabled = false;
        });
    });
});
</script>
@endsection

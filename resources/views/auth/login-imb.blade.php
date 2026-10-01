@extends('layouts.guest')

@section('title', 'Masuk - ' . $school->name)

@section('content')
@php
    $schoolLogo = $school->logo ?? null;
@endphp
<style>
    :root {
        --color-primary: {{ $school->primary_color ?: \App\Models\PlatformSetting::primaryColor() }};
        --color-secondary: {{ $school->secondary_color ?: \App\Models\PlatformSetting::secondaryColor() }};
        --color-primary-dark: {{ \App\Models\PlatformSetting::darken($school->primary_color ?: \App\Models\PlatformSetting::primaryColor(), 0.88) }};
        --color-secondary-dark: {{ \App\Models\PlatformSetting::darken($school->secondary_color ?: \App\Models\PlatformSetting::secondaryColor(), 0.88) }};
    }
</style>
<div class="flex min-h-[100svh] flex-col bg-[#fafafa] dark:bg-black text-zinc-900 dark:text-white lg:flex-row lg:overflow-hidden">

    <aside class="order-1 flex flex-col items-center justify-center px-8 pt-14 pb-10 lg:min-h-screen lg:w-1/2 lg:px-16 lg:py-0">
        <span class="flex items-center gap-2 text-[11px] font-mono uppercase tracking-[0.22em] text-zinc-400 dark:text-zinc-500">
            <span class="h-1.5 w-1.5 rounded-full bg-primary"></span>
            Portal Sekolah
        </span>

        <div class="mt-8 flex flex-col items-center gap-6">
            @if ($schoolLogo)
                <img src="{{ asset('storage/' . $schoolLogo) }}" alt="{{ $school->name }}"
                    class="h-24 w-24 rounded-xl border border-zinc-200 dark:border-white/10 bg-white dark:bg-zinc-950 object-contain p-2.5 shadow-sm">
            @else
                <div class="flex h-24 w-24 items-center justify-center rounded-xl border border-zinc-200 dark:border-white/10 bg-white dark:bg-zinc-950 shadow-sm">
                    <svg class="h-10 w-10 text-primary" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
            @endif
            <h1 class="max-w-md text-center text-3xl font-semibold leading-tight tracking-tight dark:text-white">{{ $school->name }}</h1>
        </div>

        <div class="mt-10 hidden h-px w-20 bg-zinc-200 dark:bg-white/10 lg:block"></div>
        <p class="mt-6 hidden max-w-xs text-center text-sm leading-relaxed text-zinc-500 dark:text-zinc-400 lg:block">
            Masuk untuk mengelola sistem {{ $school->name }}.
        </p>

        <p class="mt-10 text-[11px] font-mono text-zinc-400 dark:text-zinc-600 lg:mt-14">zinkros</p>
    </aside>

    <section class="order-2 flex flex-col justify-center px-6 py-10 sm:px-10 lg:min-h-screen lg:w-1/2 lg:py-0">
        <div class="mx-auto w-full max-w-sm">
            <div class="mb-8 flex items-center justify-between">
                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Masuk</p>
                <button onclick="toggleDarkMode()" class="rounded-md p-1.5 text-zinc-400 dark:text-zinc-500 hover:bg-zinc-200/60 hover:text-zinc-600 dark:hover:bg-white/10 dark:hover:text-zinc-300 transition" title="Toggle dark mode" aria-label="Toggle dark mode">
                    <svg class="h-4 w-4 hidden dark:block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <svg class="h-4 w-4 block dark:hidden" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                </button>
            </div>

            <h2 class="text-xl font-semibold tracking-tight leading-none">Login ke akun Anda</h2>
            <p class="mt-1.5 text-sm text-zinc-500 dark:text-zinc-400">Masukkan email dan kata sandi.</p>

            @if (session('status'))
                <div class="mt-4 rounded-md border border-emerald-200 bg-emerald-50 px-3.5 py-2.5 text-sm text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-400">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mt-4 rounded-md border border-red-200 bg-red-50 px-3.5 py-2.5 text-sm text-red-700 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-400">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('auth.imb.post') }}" id="loginForm" class="mt-6 space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="nama@sekolah.sch.id" required autofocus autocomplete="email"
                        class="mt-1.5 w-full rounded-md border border-zinc-300 dark:border-white/10 bg-white dark:bg-zinc-950 px-3.5 py-2.5 text-sm shadow-sm outline-none transition placeholder:text-zinc-400 dark:placeholder:text-zinc-500 focus:border-zinc-400 dark:focus:border-white/20 focus:ring-4 focus:ring-zinc-500/10 dark:focus:ring-white/10 @error('email') border-red-400 @enderror">
                    @error('email')
                        <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Password</label>
                    <input id="password" type="password" name="password" placeholder="••••••••" required autocomplete="current-password"
                        class="mt-1.5 w-full rounded-md border border-zinc-300 dark:border-white/10 bg-white dark:bg-zinc-950 px-3.5 py-2.5 text-sm shadow-sm outline-none transition placeholder:text-zinc-400 dark:placeholder:text-zinc-500 focus:border-zinc-400 dark:focus:border-white/20 focus:ring-4 focus:ring-zinc-500/10 dark:focus:ring-white/10 @error('password') border-red-400 @enderror">
                    @error('password')
                        <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between text-sm">
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" name="remember" value="1"
                            class="h-4 w-4 rounded border-zinc-300 dark:border-white/20 bg-white dark:bg-zinc-950 text-primary focus:ring-primary/30">
                        <span class="text-zinc-600 dark:text-zinc-400">Ingat saya</span>
                    </label>
                    <a href="{{ route('auth.forgot') }}" class="font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white transition">Lupa password?</a>
                </div>

                <button type="submit" id="loginButton"
                    class="w-full rounded-md bg-zinc-900 dark:bg-white px-4 py-2.5 text-sm font-medium text-white dark:text-zinc-900 shadow-sm transition hover:bg-zinc-800 dark:hover:bg-zinc-200">
                    <span class="inline-flex items-center justify-center gap-2">
                        <svg id="loginSpinner" class="h-4 w-4 animate-spin hidden" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span id="loginLabel">Login</span>
                    </span>
                </button>
            </form>

            <div class="my-6 flex items-center gap-3 text-[11px] uppercase tracking-widest text-zinc-400 dark:text-zinc-600">
                <div class="flex-1 h-px bg-zinc-200 dark:bg-white/10"></div>
                <span>atau</span>
                <div class="flex-1 h-px bg-zinc-200 dark:bg-white/10"></div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                @if (config('services.google.client_id'))
                    <a href="{{ route('auth.google.redirect') }}" id="googleButton" title="Masuk dengan Google"
                        class="flex items-center justify-center rounded-md border border-zinc-300 dark:border-white/10 bg-white dark:bg-zinc-950 px-4 py-2.5 text-sm font-medium text-zinc-700 dark:text-zinc-300 shadow-sm transition hover:bg-zinc-50 dark:hover:bg-white/5">
                        <svg class="h-4 w-4" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.27-4.74 3.27-8.1z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84A11 11 0 0 0 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.1a6.56 6.56 0 0 1 0-4.2V7.06H2.18a11 11 0 0 0 0 9.88l3.66-2.84z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1A11 11 0 0 0 2.18 7.06l3.66 2.84C6.71 7.31 9.14 5.38 12 5.38z"/>
                        </svg>
                    </a>
                @endif
                <button type="button" id="passkeyButton" title="Masuk dengan Passkey"
                    class="flex items-center justify-center rounded-md border border-zinc-300 dark:border-white/10 bg-white dark:bg-zinc-950 px-4 py-2.5 text-sm font-medium text-zinc-700 dark:text-zinc-300 shadow-sm transition hover:bg-zinc-50 dark:hover:bg-white/5">
                    <svg class="h-4 w-4 text-primary" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 10a2 2 0 0 0-2 2c0 1.02-.1 2.51-.26 4"/><path d="M14 13.12c0 2.38 0 6.38-1 8.88"/><path d="M17.29 21.02c.12-.6.43-2.3.5-3.02"/><path d="M2 12a10 10 0 0 1 18-6"/><path d="M2 16h.01"/><path d="M21.8 16c.2-2 .131-5.354 0-6"/><path d="M5 19.5C5.5 18 6 15 6 12a6 6 0 0 1 .34-2"/><path d="M8.65 22c.21-.66.45-1.32.57-2"/><path d="M9 6.8a6 6 0 0 1 9 5.2v2"/></svg>
                </button>
            </div>
            <p id="passkeyError" class="hidden mt-3 text-center text-xs text-red-600 dark:text-red-400"></p>

            <p class="pt-8 text-center text-xs text-zinc-400 dark:text-zinc-600">
                <a href="{{ route('auth.login') }}" class="font-mono hover:text-zinc-700 dark:hover:text-zinc-300 transition">← Login umum</a>
            </p>
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('loginForm');
    var button = document.getElementById('loginButton');

    form.addEventListener('submit', function() {
        button.disabled = true;
        button.classList.add('opacity-75', 'cursor-wait');
        var spinner = '<svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
        button.innerHTML = '<span class="inline-flex items-center justify-center gap-2">' + spinner + '<span>Login</span></span>';
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
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
        --color-primary-dark: {{ \App\Models\PlatformSetting::darken($school->primary_color ?: \App\Models\PlatformSetting::primaryColor(), 0.9) }};
        --color-secondary-dark: {{ \App\Models\PlatformSetting::darken($school->secondary_color ?: \App\Models\PlatformSetting::secondaryColor(), 0.9) }};
    }
</style>
<main class="min-h-screen grid grid-cols-1 lg:grid-cols-2 overflow-x-hidden bg-white text-slate-800 dark:bg-black dark:text-slate-200 font-sans antialiased">

    <section class="flex flex-col justify-center items-center px-6 py-10 sm:px-12 md:px-16 lg:px-20 xl:px-24 order-1" data-purpose="login-form-container">
        <div class="w-full max-w-[420px] flex flex-col justify-center my-auto">

            <div class="flex items-center gap-3 mb-8" data-purpose="brand-logo">
                @if ($schoolLogo)
                    <img src="{{ asset('storage/' . $schoolLogo) }}" alt="{{ $school->name }}" class="h-10 w-10 rounded-xl border border-slate-200 dark:border-white/10 object-contain bg-white dark:bg-zinc-950 p-1 shadow-sm">
                @else
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary text-white">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </div>
                @endif
                <span class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">{{ $school->name }}</span>
                <button onclick="toggleDarkMode()" class="ml-auto p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 dark:hover:bg-white/10 transition" title="Toggle dark mode" aria-label="Toggle dark mode">
                    <svg class="w-4 h-4 hidden dark:block" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <svg class="w-4 h-4 block dark:hidden" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                </button>
            </div>

            <div class="mb-7" data-purpose="form-intro">
                <h1 class="text-[28px] sm:text-[30px] font-bold text-slate-900 dark:text-white leading-tight tracking-tight">Masuk ke Akun Anda</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-2">Selamat datang kembali! Pilih metode untuk masuk:</p>
            </div>

            @if (session('status'))
                <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-400">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-400">
                    {{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-2 gap-3.5 mb-6" data-purpose="social-login-actions">
                @if (config('services.google.client_id'))
                    <a href="{{ route('auth.google.redirect') }}" id="googleButton"
                        class="flex items-center justify-center gap-2.5 py-2.5 px-4 border border-slate-200 dark:border-white/10 hover:border-slate-300 dark:hover:border-white/20 rounded-xl bg-white dark:bg-zinc-950 text-slate-700 dark:text-slate-300 font-medium text-sm transition-all duration-150 hover:bg-slate-50 dark:hover:bg-white/5 active:scale-[0.98] shadow-sm">
                        <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24">
                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"></path>
                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"></path>
                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z" fill="#FBBC05"></path>
                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z" fill="#EA4335"></path>
                        </svg>
                    </a>
                @endif
                <button type="button" id="passkeyButton"
                    class="flex items-center justify-center gap-2.5 py-2.5 px-4 border border-slate-200 dark:border-white/10 hover:border-slate-300 dark:hover:border-white/20 rounded-xl bg-white dark:bg-zinc-950 text-slate-700 dark:text-slate-300 font-medium text-sm transition-all duration-150 hover:bg-slate-50 dark:hover:bg-white/5 active:scale-[0.98] shadow-sm">
                    <svg class="w-4 h-4 shrink-0 text-primary" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 10a2 2 0 0 0-2 2c0 1.02-.1 2.51-.26 4"/><path d="M14 13.12c0 2.38 0 6.38-1 8.88"/><path d="M17.29 21.02c.12-.6.43-2.3.5-3.02"/><path d="M2 12a10 10 0 0 1 18-6"/><path d="M2 16h.01"/><path d="M21.8 16c.2-2 .131-5.354 0-6"/><path d="M5 19.5C5.5 18 6 15 6 12a6 6 0 0 1 .34-2"/><path d="M8.65 22c.21-.66.45-1.32.57-2"/><path d="M9 6.8a6 6 0 0 1 9 5.2v2"/></svg>
                    <span>Passkey</span>
                </button>
            </div>
            <p id="passkeyError" class="hidden mb-4 text-center text-xs text-red-600 dark:text-red-400"></p>

            <div class="relative flex items-center justify-center my-5" data-purpose="divider">
                <div class="border-t border-slate-200 dark:border-white/10 w-full"></div>
                <span class="bg-white dark:bg-black px-3 text-xs text-slate-400 dark:text-slate-500 absolute uppercase tracking-wider font-medium">atau lanjutkan dengan email</span>
            </div>

            <form action="{{ route('auth.imb.post') }}" method="POST" id="loginForm" class="space-y-4" data-purpose="credential-form">
                @csrf

                <div data-purpose="input-email-wrapper">
                    <label class="sr-only" for="email">Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                        </div>
                        <input class="block w-full pl-10 pr-3.5 py-2.5 text-sm bg-white dark:bg-zinc-950 border border-slate-200 dark:border-white/10 rounded-xl placeholder-slate-400 dark:placeholder-slate-500 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition duration-150 @error('email') border-red-400 @enderror" id="email" name="email" type="email" placeholder="Email" value="{{ old('email') }}" required autofocus autocomplete="email"/>
                    </div>
                    @error('email')
                        <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div data-purpose="input-password-wrapper">
                    <label class="sr-only" for="password">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                        </div>
                        <input class="block w-full pl-10 pr-10 py-2.5 text-sm bg-white dark:bg-zinc-950 border border-slate-200 dark:border-white/10 rounded-xl placeholder-slate-400 dark:placeholder-slate-500 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition duration-150 @error('password') border-red-400 @enderror" id="password" name="password" type="password" placeholder="Password" required autocomplete="current-password"/>
                        <button type="button" id="passwordToggle" aria-label="Toggle password visibility" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 focus:outline-none">
                            <svg id="iconShow" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                            <svg id="iconHide" class="w-4 h-4 hidden" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" stroke-linecap="round" stroke-linejoin="round"></path><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between text-xs sm:text-sm pt-1">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" name="remember" value="1" checked
                            class="w-4 h-4 rounded text-primary border-slate-300 dark:border-white/20 focus:ring-primary/30 focus:ring-offset-0 bg-white dark:bg-zinc-950"/>
                        <span class="text-slate-600 dark:text-slate-400">Ingat saya</span>
                    </label>
                    <a href="{{ route('auth.forgot') }}" class="text-primary hover:text-primary-dark font-medium transition duration-150">Lupa password?</a>
                </div>

                <div class="pt-2">
                    <button type="submit" id="loginButton"
                        class="w-full bg-primary hover:bg-primary-dark text-white font-medium py-2.5 px-4 rounded-xl shadow-sm hover:shadow transition duration-150">
                        <span class="inline-flex items-center justify-center gap-2">
                            <svg id="loginSpinner" class="w-4 h-4 animate-spin hidden" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span id="loginLabel">Log In</span>
                        </span>
                    </button>
                </div>
            </form>

            <p class="text-center text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-8" data-purpose="registration-prompt">
                Belum punya akun sekolah?
                <a href="{{ route('auth.register') }}" class="text-primary hover:text-primary-dark font-semibold transition duration-150">Daftar sekolah Anda</a>
            </p>
            <p class="text-center text-xs text-slate-400 dark:text-slate-600 mt-3">
                <a href="{{ route('auth.login') }}" class="font-mono hover:text-slate-600 dark:hover:text-slate-300 transition duration-150">← Login umum</a>
            </p>
        </div>
    </section>

    <section class="relative hidden lg:flex flex-col justify-between items-center overflow-hidden text-white bg-gradient-to-br from-primary via-primary-dark to-secondary-dark p-10 xl:p-14 order-2" data-purpose="feature-showcase-panel">
        <div class="absolute inset-0 pointer-events-none flex items-center justify-center">
            <div class="w-[520px] h-[520px] rounded-full bg-white/15 blur-2xl opacity-80"></div>
            <div class="w-[380px] h-[380px] rounded-full border border-white/20 absolute"></div>
            <div class="w-[520px] h-[520px] rounded-full border border-white/10 absolute"></div>
        </div>

        <div class="w-full h-4"></div>

        <div class="relative z-10 w-full max-w-[420px] flex flex-col items-center gap-8 py-6" data-purpose="dashboard-mockup">
            @if ($schoolLogo)
                <img src="{{ asset('storage/' . $schoolLogo) }}" alt="{{ $school->name }}" class="h-16 w-16 object-contain rounded-2xl bg-white p-2 shadow-lg">
            @else
                <div class="h-16 w-16 rounded-2xl bg-white/15 border border-white/20 flex items-center justify-center">
                    <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
            @endif

            <div class="w-full bg-white rounded-2xl shadow-2xl p-3 text-slate-800 border border-white/40">
                <div class="flex items-center gap-1.5 pb-2.5 mb-2.5 border-b border-slate-100">
                    <span class="w-2 h-2 rounded-full bg-red-400"></span>
                    <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                    <div class="ml-auto flex items-center gap-1">
                        <span class="h-1.5 w-10 bg-slate-200 rounded-full"></span>
                    </div>
                </div>
                <div class="flex gap-2 mb-3">
                    <div class="h-4 w-14 bg-slate-200 rounded-md"></div>
                    <div class="h-4 w-12 bg-slate-100 rounded-md"></div>
                </div>
                <div class="flex items-center gap-2.5 p-1.5 rounded-lg bg-slate-50/70 mb-1.5 border border-slate-100">
                    <div class="w-6 h-6 rounded-full bg-amber-100 flex items-center justify-center text-[10px] font-bold text-amber-700">SD</div>
                    <div class="flex-1 space-y-1">
                        <div class="h-2 w-16 bg-slate-300 rounded"></div>
                        <div class="h-1.5 w-10 bg-slate-200 rounded"></div>
                    </div>
                </div>
                <div class="flex items-center gap-2.5 p-1.5 rounded-lg bg-slate-50/70 mb-1.5 border border-slate-100">
                    <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center text-[10px] font-bold text-blue-700">MK</div>
                    <div class="flex-1 space-y-1">
                        <div class="h-2 w-14 bg-slate-300 rounded"></div>
                        <div class="h-1.5 w-12 bg-slate-200 rounded"></div>
                    </div>
                </div>
                <div class="flex items-center gap-2.5 p-1.5 rounded-lg bg-slate-50/70 border border-slate-100">
                    <div class="w-6 h-6 rounded-full bg-emerald-100 flex items-center justify-center text-[10px] font-bold text-emerald-700">AL</div>
                    <div class="flex-1 space-y-1">
                        <div class="h-2 w-12 bg-slate-300 rounded"></div>
                        <div class="h-1.5 w-16 bg-slate-200 rounded"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="relative z-10 text-center max-w-sm mb-4" data-purpose="showcase-caption">
            <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-white mb-2">Satu platform untuk seluruh administrasi sekolah.</h2>
            <p class="text-xs sm:text-sm text-white/80 leading-relaxed">Rapor, kehadiran, kalender akademik, dan pengguna — dikelola dengan rapi dan aman.</p>
            <div class="flex items-center justify-center gap-1.5 mt-6">
                <span class="w-2 h-2 rounded-full bg-white"></span>
                <span class="w-1.5 h-1.5 rounded-full bg-white/40"></span>
                <span class="w-1.5 h-1.5 rounded-full bg-white/40"></span>
            </div>
        </div>
    </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var toggle = document.getElementById('passwordToggle');
    if (toggle) {
        toggle.addEventListener('click', function() {
            var input = document.getElementById('password');
            var show = document.getElementById('iconShow');
            var hide = document.getElementById('iconHide');
            if (input.type === 'password') {
                input.type = 'text';
                show.classList.add('hidden');
                hide.classList.remove('hidden');
            } else {
                input.type = 'password';
                hide.classList.add('hidden');
                show.classList.remove('hidden');
            }
        });
    }

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
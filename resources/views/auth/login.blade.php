@extends('layouts.guest')

@php
    // Dipanggil sekali, bukan berulang di markup.
    $appName = \App\Models\PlatformSetting::appName();
    $tagline = \App\Models\PlatformSetting::tagline();
    $logo = \App\Models\PlatformSetting::logo();
    $hasGoogle = filled(config('services.google.client_id'));
    $primary = \App\Models\PlatformSetting::primaryColor();
    $secondary = \App\Models\PlatformSetting::secondaryColor();
    $primaryRgbStr = '5,150,105';
    if (is_string($primary) && strlen($primary) === 7 && $primary[0] === '#') {
        $r = hexdec(substr($primary, 1, 2));
        $g = hexdec(substr($primary, 3, 2));
        $b = hexdec(substr($primary, 5, 2));
        $primaryRgbStr = $r . ',' . $g . ',' . $b;
    }
    $peranHeading = \App\Models\PlatformSetting::hero('peran_heading', 'Dibuat untuk Setiap Peran');
    $peranSubtitle = \App\Models\PlatformSetting::hero('peran_subtitle', '');
    $peranRoles = \App\Models\PlatformSetting::roles();

    $label = 'text-sm font-medium text-slate-700 dark:text-white/70';
    $control = 'h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 text-sm outline-none transition placeholder:text-slate-400 focus:border-[color:var(--login-primary)] focus:ring-2 focus:ring-[color:rgba(var(--login-primary-rgb),0.15)] aria-invalid:border-red-500 dark:border-white/10 dark:bg-[#0a0a0a] dark:text-white dark:placeholder:text-white/30';
    $altButton = 'inline-flex h-11 w-full items-center justify-center gap-2.5 rounded-xl border border-slate-300 bg-white px-4 text-sm font-medium text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-60 dark:border-white/15 dark:bg-[#0a0a0a] dark:text-white/80 dark:hover:bg-white/5';
    $focus = 'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[color:var(--login-primary)]';
    $muted = 'text-slate-500 dark:text-white/50';
    $errorText = 'mt-1.5 text-xs text-red-600 dark:text-red-400';

    // Ikon sama seperti sebelumnya.
    $icons = [
        'book' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
        'clipboard' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
        'chart' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
    ];

    // Isi panel keterangan: edit di sini.
    $highlights = [
        ['icon' => 'clipboard', 'title' => 'Absensi kelas', 'desc' => 'Catat kehadiran siswa per kelas dan mata pelajaran.'],
        ['icon' => 'chart', 'title' => 'Nilai siswa', 'desc' => 'Input dan kelola nilai siswa dengan rapi.'],
        ['icon' => 'book', 'title' => 'Tahfidz dan Al-Quran', 'desc' => 'Pantau setoran hafalan dan rekap kehadiran Al-Quran.'],
    ];
@endphp

@section('title', 'Masuk - ' . $appName)

@section('content')
<style>
    :root {
        --login-primary: {{ $primary }};
        --login-secondary: {{ $secondary }};
        --login-primary-rgb: {{ $primaryRgbStr }};
    }

    @media (min-width: 1024px) {
        .login-page {
            height: 100vh;
            height: 100dvh;
            min-height: 0;
        }

        .login-section,
        .login-info {
            height: 100%;
            min-height: 0;
        }
    }

    @media (min-width: 1024px) and (max-height: 800px) {
        .login-section.login-section {
            padding-block: 1.5rem;
        }

        .login-card.login-card {
            padding: 1.25rem;
        }

        .login-intro h1 {
            font-size: 1.5rem;
        }

        .login-intro + .login-auth-content.login-auth-content {
            margin-top: 1.25rem;
        }

        #loginForm > :not([hidden]) ~ :not([hidden]) {
            margin-block-start: 0.875rem;
        }

        .login-divider.login-divider {
            margin-block: 0.875rem;
        }

        .login-register.login-register {
            margin-top: 1rem;
        }

        .login-info.login-info {
            padding-block: 1.5rem;
        }

        .login-highlights.login-highlights {
            margin-top: 1.25rem;
        }

        .login-highlights.login-highlights li {
            padding-block: 0.625rem;
        }
    }

    @media (min-width: 1024px) and (max-height: 650px) {
        .login-highlights,
        .login-info-description {
            display: none;
        }
    }
</style>
<div class="login-page grid min-h-screen lg:grid-cols-2">

    {{-- Sisi kiri: login --}}
    <section class="login-section relative flex min-h-screen items-center justify-center bg-gradient-to-br from-white via-slate-50 to-[rgba(var(--login-primary-rgb),0.06)] px-5 py-14 sm:px-8 sm:py-16 lg:px-12 xl:px-16 dark:from-[#0a0a0a] dark:via-[#0a0a0a] dark:to-[rgba(var(--login-primary-rgb),0.14)]">
        <button onclick="toggleDarkMode()" type="button" title="Toggle dark mode" aria-label="Ganti tema"
            class="absolute right-4 top-4 inline-flex size-9 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 dark:text-white/50 dark:hover:bg-white/10 {{ $focus }}">
            <svg class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            <svg class="w-5 h-5 block dark:hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
        </button>

        <div class="login-card w-full max-w-md rounded-2xl border border-slate-200/80 bg-white p-6 shadow-lg shadow-slate-200/40 sm:p-8 dark:border-white/10 dark:bg-[#111] dark:shadow-black/25">
            {{-- Merek (hanya di layar kecil; di layar lebar ada di panel kanan) --}}
            <div class="mb-8 flex items-center gap-3 lg:hidden">
                @if ($logo)
                    <img src="{{ asset('storage/' . $logo) }}" alt="{{ $appName }}" class="size-10 rounded-xl object-contain">
                @else
                    <span class="flex size-10 items-center justify-center rounded-xl shadow-sm" style="background: linear-gradient(135deg, var(--login-primary), var(--login-secondary)); box-shadow: 0 10px 40px -10px rgba(var(--login-primary-rgb),0.4);">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['book'] }}"/></svg>
                    </span>
                @endif
                <span class="font-semibold tracking-tight">{{ $appName }}</span>
            </div>

            <div class="login-intro">
                <h1 class="text-2xl font-semibold tracking-tight sm:text-3xl">Masuk ke akun Anda</h1>
                <p class="mt-2 text-sm leading-6 {{ $muted }}">Masukkan email dan password untuk melanjutkan.</p>
            </div>

            <div class="login-auth-content mt-8">
                @if (session('status'))
                    <div role="status" class="mb-5 rounded-lg px-3 py-2.5 text-sm" style="border: 1px solid rgba(var(--login-primary-rgb),0.25); background: rgba(var(--login-primary-rgb),0.1); color: var(--login-primary);">
                        {{ session('status') }}
                    </div>
                @endif

                @if (session('error'))
                    <div role="alert" class="mb-5 rounded-lg border border-red-200 bg-red-50 px-3 py-2.5 text-sm text-red-700 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-400">
                        {{ session('error') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('auth.login.post') }}" id="loginForm" class="login-form space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="{{ $label }} mb-1.5 block">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                            @error('email') aria-invalid="true" aria-describedby="email-error" @enderror
                            class="{{ $control }}">
                        @error('email')
                            <p id="email-error" class="{{ $errorText }}">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="{{ $label }} mb-1.5 block">Password</label>
                        <div class="relative">
                            <input id="password" type="password" name="password" required autocomplete="current-password"
                                @error('password') aria-invalid="true" aria-describedby="password-error" @enderror
                                class="{{ $control }} pr-12">
                            <button type="button" id="togglePassword" aria-label="Tampilkan password" aria-controls="password" aria-pressed="false"
                                class="absolute inset-y-0 right-1 flex w-10 items-center justify-center rounded-lg text-slate-500 transition hover:text-slate-800 dark:text-white/50 dark:hover:text-white {{ $focus }}">
                                <svg id="showPasswordIcon" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.5-6.25 9.75-6.25S21.75 12 21.75 12 18.25 18.25 12 18.25 2.25 12 2.25 12Z"/>
                                    <circle cx="12" cy="12" r="2.75"/>
                                </svg>
                                <svg id="hidePasswordIcon" class="hidden size-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.6 10.6a2 2 0 002.8 2.8M9.9 5.9A10.8 10.8 0 0112 5.75c6.25 0 9.75 6.25 9.75 6.25a16.3 16.3 0 01-3.05 3.75M6.2 6.2C3.7 7.75 2.25 12 2.25 12s3.5 6.25 9.75 6.25a10.8 10.8 0 004.15-.82"/>
                                </svg>
                            </button>
                        </div>
                        <div class="mt-1 text-right">
                            <a href="{{ route('auth.forgot') }}" class="text-sm hover:underline {{ $focus }}" style="color: var(--login-primary);">Lupa password?</a>
                        </div>
                        @error('password')
                            <p id="password-error" class="{{ $errorText }}">{{ $message }}</p>
                        @enderror
                    </div>

                    <label class="inline-flex items-center gap-2 text-sm {{ $muted }}">
                        <input type="checkbox" name="remember" value="1"
                            class="size-4 rounded border-slate-300 focus:ring-[color:rgba(var(--login-primary-rgb),0.25)] dark:border-white/20" style="accent-color: var(--login-primary);">
                        Ingat saya
                    </label>

                    <button type="submit" id="loginButton"
                        class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-xl px-4 text-sm font-medium text-white shadow-sm transition disabled:cursor-wait disabled:opacity-75 {{ $focus }}" style="background: linear-gradient(90deg, var(--login-primary), var(--login-secondary)); box-shadow: 0 10px 40px -10px rgba(var(--login-primary-rgb),0.4);">
                        <svg id="loginSpinner" class="w-4 h-4 animate-spin hidden" fill="none" viewBox="0 0 24 24" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span id="loginLabel">Masuk</span>
                    </button>
                </form>

                <div class="login-divider my-5 flex items-center gap-3 text-xs {{ $muted }}">
                    <span class="h-px flex-1 bg-slate-200 dark:bg-white/10"></span>
                    atau
                    <span class="h-px flex-1 bg-slate-200 dark:bg-white/10"></span>
                </div>

                <div @class(['grid gap-3', 'sm:grid-cols-2' => $hasGoogle])>
                    @if ($hasGoogle)
                        <a href="{{ route('auth.google.redirect') }}" id="googleButton" title="Masuk dengan Google" class="{{ $altButton }} {{ $focus }}">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" aria-hidden="true">
                                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.27-4.74 3.27-8.1z"/>
                                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84A11 11 0 0 0 12 23z"/>
                                <path fill="#FBBC05" d="M5.84 14.1a6.56 6.56 0 0 1 0-4.2V7.06H2.18a11 11 0 0 0 0 9.88l3.66-2.84z"/>
                                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1A11 11 0 0 0 2.18 7.06l3.66 2.84C6.71 7.31 9.14 5.38 12 5.38z"/>
                            </svg>
                            Google
                        </a>
                    @endif

                    <button type="button" id="passkeyButton" title="Masuk dengan Passkey" class="{{ $altButton }} {{ $focus }}">
                        <svg class="w-5 h-5" style="color: var(--login-primary);" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 10a2 2 0 0 0-2 2c0 1.02-.1 2.51-.26 4"/><path d="M14 13.12c0 2.38 0 6.38-1 8.88"/><path d="M17.29 21.02c.12-.6.43-2.3.5-3.02"/><path d="M2 12a10 10 0 0 1 18-6"/><path d="M2 16h.01"/><path d="M21.8 16c.2-2 .131-5.354 0-6"/><path d="M5 19.5C5.5 18 6 15 6 12a6 6 0 0 1 .34-2"/><path d="M8.65 22c.21-.66.45-1.32.57-2"/><path d="M9 6.8a6 6 0 0 1 9 5.2v2"/></svg>
                        Passkey
                    </button>
                </div>
                <p id="passkeyError" role="alert" class="hidden mt-3 text-center text-xs text-red-600 dark:text-red-400"></p>
            </div>

            <p class="login-register mt-8 text-center text-sm {{ $muted }}">
                Belum punya akun sekolah?
                <a href="{{ route('auth.register') }}" class="font-medium hover:underline {{ $focus }}" style="color: var(--login-primary);">Daftar sekolah Anda</a>
            </p>
        </div>
    </section>

    {{-- Sisi kanan: keterangan singkat aplikasi (disembunyikan di layar kecil) --}}
    <aside aria-label="Tentang aplikasi"
        class="login-info relative isolate hidden min-h-screen overflow-hidden text-white lg:flex lg:flex-col lg:justify-between lg:p-12 xl:p-16 dark:border-l dark:border-white/10" style="background: linear-gradient(135deg, var(--login-primary) 0%, var(--login-secondary) 100%);">
        {{-- Dekorasi: grid tipis di kiri atas, cahaya warna tema di kanan bawah --}}
        <div aria-hidden="true" class="pointer-events-none absolute inset-0 -z-10 text-white/[0.06] [background-image:linear-gradient(to_right,currentColor_1px,transparent_1px),linear-gradient(to_bottom,currentColor_1px,transparent_1px)] [background-size:48px_48px] [mask-image:radial-gradient(ellipse_at_top_left,black,transparent_70%)]"></div>
        <div aria-hidden="true" class="pointer-events-none absolute -bottom-40 -right-32 -z-10 size-[28rem] rounded-full blur-3xl" style="background: radial-gradient(circle, rgba(255,255,255,0.18), transparent 70%);"></div>

            <div class="relative flex items-center gap-3">
                @if ($logo)
                    <img src="{{ asset('storage/' . $logo) }}" alt="{{ $appName }}" class="size-10 rounded-xl bg-white/90 p-1 object-contain">
                @else
                    <span class="flex size-10 items-center justify-center rounded-xl bg-white/90">
                        <svg class="w-5 h-5" style="color: var(--login-primary);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['book'] }}"/></svg>
                    </span>
                @endif
                <span class="text-lg font-semibold tracking-tight text-white">{{ $appName }}</span>
            </div>

        <div class="relative max-w-xl">
            <p class="text-3xl font-semibold leading-tight tracking-tight text-balance xl:text-4xl">
                {{ $peranHeading }}
            </p>
            @if (filled($peranSubtitle))
                <p class="login-info-description mt-4 max-w-md leading-7 text-white/90">{{ $peranSubtitle }}</p>
            @else
                <p class="login-info-description mt-4 max-w-md leading-7 text-white/90">{{ $tagline ?: 'Administrasi sekolah dalam satu tempat.' }}</p>
            @endif

            <div class="mt-8 grid gap-3 sm:grid-cols-2">
                @forelse ($peranRoles as $item)
                    @php
                        $iconKey = $item['icon'] ?? 'book';
                        $iconPath = $icons[$iconKey] ?? $icons['book'];
                    @endphp
                    <div class="flex items-start gap-3 rounded-2xl border border-white/25 bg-white/12 p-4 shadow-lg shadow-black/10 backdrop-blur-sm">
                        <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-white/90">
                            <svg class="w-4 h-4" style="color: var(--login-primary);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath }}"/></svg>
                        </span>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold">{{ $item['title'] ?? '' }}</p>
                            <p class="mt-0.5 text-xs leading-relaxed text-white/80">{{ $item['desc'] ?? '' }}</p>
                        </div>
                    </div>
                @empty
                    @foreach ($highlights as $item)
                        <div class="flex items-start gap-3 rounded-2xl border border-white/25 bg-white/12 p-4 shadow-lg shadow-black/10 backdrop-blur-sm">
                            <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-white/90">
                                <svg class="w-4 h-4" style="color: var(--login-primary);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$item['icon']] }}"/></svg>
                            </span>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold">{{ $item['title'] }}</p>
                                <p class="mt-0.5 text-xs leading-relaxed text-white/80">{{ $item['desc'] }}</p>
                            </div>
                        </div>
                    @endforeach
                @endforelse
            </div>
        </div>

        <p class="relative text-sm text-slate-400">&copy; {{ now()->year }} {{ $appName }}</p>
    </aside>
</div>

<script src="{{ asset('vendor/webauthn/webauthn.js') }}"></script>
<script src="{{ asset('js/passkey-bridge.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // --- Form login: cegah submit ganda ---
    const form = document.getElementById('loginForm');
    const submit = document.getElementById('loginButton');
    const spinner = document.getElementById('loginSpinner');
    const label = document.getElementById('loginLabel');
    const password = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');
    const showPasswordIcon = document.getElementById('showPasswordIcon');
    const hidePasswordIcon = document.getElementById('hidePasswordIcon');

    togglePassword.addEventListener('click', () => {
        const isVisible = password.type === 'text';
        password.type = isVisible ? 'password' : 'text';
        togglePassword.setAttribute('aria-pressed', String(!isVisible));
        togglePassword.setAttribute('aria-label', isVisible ? 'Tampilkan password' : 'Sembunyikan password');
        showPasswordIcon.classList.toggle('hidden', !isVisible);
        hidePasswordIcon.classList.toggle('hidden', isVisible);
        password.focus();
    });

    form.addEventListener('submit', () => {
        submit.disabled = true;
        spinner.classList.remove('hidden');
        label.textContent = 'Memproses';
    });

    // Tombol Kembali memulihkan halaman dari cache dengan tombol masih nonaktif.
    window.addEventListener('pageshow', (event) => {
        if (!event.persisted) return;
        submit.disabled = false;
        spinner.classList.add('hidden');
        label.textContent = 'Masuk';
    });

    // --- Login passkey ---
    const btn = document.getElementById('passkeyButton');
    const errorBox = document.getElementById('passkeyError');
    if (!btn) return;

    const optionsUrl = @js(route('webauthn.auth.options'));
    const authUrl = @js(route('webauthn.auth'));
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    const post = (url, payload) => fetch(url, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf,
        },
        body: JSON.stringify(payload),
    });

    // Menampilkan pesan sekaligus mengaktifkan kembali tombol.
    const fail = (message) => {
        errorBox.textContent = message;
        errorBox.classList.remove('hidden');
        btn.disabled = false;
    };

    const finish = async (credential) => {
        try {
            const response = await post(authUrl, credential);
            const body = await response.json().catch(() => ({}));

            if (response.ok && body.callback) {
                window.location.href = body.callback;
                return;
            }

            const first = Object.values(body.errors ?? {})[0];
            fail(first?.[0] ?? 'Autentikasi gagal. Coba lagi.');
        } catch {
            fail('Autentikasi gagal. Coba lagi.');
        }
    };

    btn.addEventListener('click', async () => {
        errorBox.classList.add('hidden');

        if (!window.PublicKeyCredential) {
            return fail('Browser ini tidak mendukung login passkey.');
        }

        btn.disabled = true;

        try {
            const response = await post(optionsUrl, {});

            if (response.status === 422) {
                return fail('Email tersebut belum memiliki passkey. Masuk dulu dengan password, lalu daftarkan passkey di halaman Keamanan Akun.');
            }

            const data = await response.json();

            new WebAuthn((name, message) => fail(
                name === 'NotAllowedError'
                    ? 'Autentikasi dibatalkan atau perangkat tidak dikenali.'
                    : message
            )).sign(data.publicKey, finish);
        } catch {
            fail('Gagal menghubungi server. Coba lagi.');
        }
    });
});
</script>
@endsection
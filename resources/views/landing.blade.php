<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ \App\Models\PlatformSetting::appName() }} - Sistem Manajemen Sekolah Islam Terpadu</title>
    <meta name="description" content="Kelola kehadiran, absensi, hafalan Al-Qur'an, nilai, e-Rapor, dan pembayaran SPP secara terpadu untuk Sekolah Islam Terpadu.">
    @include('partials._vite')
@php $favicon = \App\Models\PlatformSetting::logo(); @endphp
@if ($favicon)
<link rel="icon" type="image/png" href="{{ asset('storage/' . $favicon) }}">
@endif
<style>
:root {
    --color-primary: {{ \App\Models\PlatformSetting::primaryColor() }};
    --color-secondary: {{ \App\Models\PlatformSetting::secondaryColor() }};
    --color-primary-dark: {{ \App\Models\PlatformSetting::darken(\App\Models\PlatformSetting::primaryColor(), 0.88) }};
    --color-secondary-dark: {{ \App\Models\PlatformSetting::darken(\App\Models\PlatformSetting::secondaryColor(), 0.88) }};
}
</style>
</head>
<body class="font-sans antialiased bg-white text-slate-900" style="font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;">

    {{-- HEADER --}}
    <header class="sticky top-0 z-50 border-b border-slate-100 bg-white/80 backdrop-blur">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
<img src="{{ \App\Models\PlatformSetting::logo() ? asset('storage/' . \App\Models\PlatformSetting::logo()) : asset('images/logo.svg') }}" alt="Logo" class="h-9 w-9">
<div>
<p class="text-sm font-bold leading-tight text-primary-dark">{{ \App\Models\PlatformSetting::appName() }}</p>
@if (\App\Models\PlatformSetting::tagline())
<p class="text-xs text-slate-500">{{ \App\Models\PlatformSetting::tagline() }}</p>
@endif
                    </div>
                </a>

                <nav class="hidden items-center gap-6 text-sm text-slate-600 md:flex">
                    @if (\App\Models\PlatformSetting::showSection('fitur'))
                        <a href="#fitur" class="hover:text-primary">Fitur</a>
                    @endif
                    @if (\App\Models\PlatformSetting::showSection('tampilan'))
                        <a href="#tampilan" class="hover:text-primary">Tampilan</a>
                    @endif
                    <a href="#harga" class="hover:text-primary">Harga</a>
                    <a href="{{ route('blog.index') }}" class="hover:text-primary">Blog</a>
                    @if (\App\Models\PlatformSetting::showSection('sekolah'))
                        <a href="#sekolah" class="hover:text-primary">Sekolah</a>
                    @endif
                    <a href="#faq" class="hover:text-primary">FAQ</a>
                </nav>

                <div class="flex items-center gap-3">
                    @auth
                        <a href="{{ route(auth()->user()->role === 'superadmin' ? 'platform.dashboard' : auth()->user()->role . '.dashboard') }}"
                            class="rounded-lg bg-primary-dark px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-dark">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('auth.login') }}"
                            class="hidden rounded-lg border border-primary-dark px-4 py-2 text-sm font-semibold text-primary transition hover:bg-primary/10 sm:inline-block">
                            Masuk
                        </a>
                        <a href="{{ route('auth.register') }}"
                            class="rounded-lg bg-primary-dark px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-dark">
                            Daftar
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    {{-- HERO --}}
    @if (\App\Models\PlatformSetting::showSection('hero'))
    <section class="relative overflow-hidden bg-gradient-to-b from-primary/10 via-white to-white">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(16,185,129,0.15),transparent_50%)]"></div>
        <div class="relative mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-28">
            <div class="grid items-center gap-12 lg:grid-cols-2">
                @php
    $hBadge = \App\Models\PlatformSetting::hero('hero_badge', 'Solusi Digital untuk Sekolah Islam');
    $hTitle = \App\Models\PlatformSetting::hero('hero_title', 'Manajemen Sekolah Islam Terpadu');
    $hHighlight = \App\Models\PlatformSetting::hero('hero_title_highlight', 'dalam Satu Platform');
    $hDesc = \App\Models\PlatformSetting::hero('hero_desc', 'Kelola kehadiran, absensi pembelajaran, hafalan Al-Qur\'an, nilai & e-Rapor, serta pembayaran SPP secara terpadu untuk guru, siswa, orang tua, dan manajemen sekolah.');
    $hMicro = \App\Models\PlatformSetting::hero('hero_microcopy', 'Tanpa kartu kredit · Setup 5 menit · Support WhatsApp');
    $hCtaGuest = \App\Models\PlatformSetting::hero('hero_cta_guest', 'Mulai Gratis 14 Hari');
    $hCtaFeatures = \App\Models\PlatformSetting::hero('hero_cta_features', 'Lihat Fitur');
    $hCtaAuth = \App\Models\PlatformSetting::hero('hero_cta_auth', 'Buka Dashboard');
@endphp
<div>
                    <span class="inline-flex items-center rounded-full bg-primary/15 px-3 py-1 text-xs font-semibold text-primary-dark">
                        {{ $hBadge }}
                    </span>
                    <h1 class="mt-6 text-4xl font-extrabold leading-tight tracking-tight text-slate-900 sm:text-5xl">
                        {{ $hTitle }}
                        @if ($hHighlight)
                            <span class="text-primary">{{ $hHighlight }}</span>
                        @endif
                    </h1>
                    <p class="mt-6 text-lg leading-relaxed text-slate-600">
                        {{ $hDesc }}
                    </p>
                    <div class="mt-8 flex flex-wrap gap-4">
                        @auth
                            <a href="{{ route(auth()->user()->role === 'superadmin' ? 'platform.dashboard' : auth()->user()->role . '.dashboard') }}"
                                class="rounded-lg bg-primary-dark px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-dark">
                                {{ $hCtaAuth }}
                            </a>
                        @else
                            <a href="{{ route('auth.register') }}"
                                class="rounded-lg bg-primary-dark px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-dark">
                                {{ $hCtaGuest }}
                            </a>
                            <a href="#fitur"
                                class="rounded-lg border border-slate-300 bg-white px-6 py-3 text-sm font-semibold text-slate-700 transition hover:border-primary hover:text-primary">
                                {{ $hCtaFeatures }}
                            </a>
                        @endauth
                    </div>
                    <p class="mt-3 text-xs text-slate-400">{{ $hMicro }}</p>
                </div>

                <div class="relative hidden lg:block">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xl">
                        <div class="mb-4 flex items-center justify-between border-b border-slate-100 pb-4">
                            <p class="text-sm font-semibold text-slate-800">Satu Platform, Semua Peran</p>
                            <span class="rounded-full bg-primary/15 px-2 py-0.5 text-xs font-semibold text-primary-dark">4 Peran</span>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4 transition hover:border-primary/40 hover:bg-primary/5">
                                <svg class="h-6 w-6 text-primary" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/>
                                </svg>
                                <p class="mt-3 text-sm font-bold text-slate-800">Guru</p>
                                <p class="mt-1 text-xs leading-relaxed text-slate-500">Absensi, hafalan &amp; nilai jadi cepat.</p>
                            </div>
                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4 transition hover:border-primary/40 hover:bg-primary/5">
                                <svg class="h-6 w-6 text-primary" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342"/>
                                </svg>
                                <p class="mt-3 text-sm font-bold text-slate-800">Murid</p>
                                <p class="mt-1 text-xs leading-relaxed text-slate-500">Jadwal, nilai &amp; e-Rapor di genggaman.</p>
                            </div>
                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4 transition hover:border-primary/40 hover:bg-primary/5">
                                <svg class="h-6 w-6 text-primary" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/>
                                </svg>
                                <p class="mt-3 text-sm font-bold text-slate-800">Orang Tua</p>
                                <p class="mt-1 text-xs leading-relaxed text-slate-500">Pantau kehadiran, SPP &amp; perkembangan anak.</p>
                            </div>
                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4 transition hover:border-primary/40 hover:bg-primary/5">
                                <svg class="h-6 w-6 text-primary" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/>
                                </svg>
                                <p class="mt-3 text-sm font-bold text-slate-800">Kepala Sekolah</p>
                                <p class="mt-1 text-xs leading-relaxed text-slate-500">Laporan &amp; keputusan dalam satu papan.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @endif

    {{-- STATISTICS BAR --}}
    <section class="border-y border-slate-100 bg-slate-50 py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 gap-8 md:grid-cols-5">
                <div class="text-center">
                    <p class="text-3xl font-extrabold text-primary">{{ $stats['schools'] ?? 4 }}+</p>
                    <p class="mt-1 text-sm text-slate-500">Sekolah Aktif</p>
                </div>
                <div class="text-center">
                    <p class="text-3xl font-extrabold text-primary">{{ $stats['students'] ?? 97 }}+</p>
                    <p class="mt-1 text-sm text-slate-500">Siswa Terdaftar</p>
                </div>
                <div class="text-center">
                    <p class="text-3xl font-extrabold text-primary">{{ $stats['teachers'] ?? 10 }}+</p>
                    <p class="mt-1 text-sm text-slate-500">Guru Aktif</p>
                </div>
                <div class="text-center">
                    <p class="text-3xl font-extrabold text-primary">{{ $stats['classes'] ?? 10 }}+</p>
                    <p class="mt-1 text-sm text-slate-500">Kelola Kelas</p>
                </div>
                <div class="text-center">
                    <p class="text-3xl font-extrabold text-primary">{{ number_format($visitorStats['total']) }}</p>
                    <p class="mt-1 text-sm text-slate-500">Total Kunjungan</p>
                </div>
            </div>
        </div>
    </section>

    {{-- FITUR --}}
    @if (\App\Models\PlatformSetting::showSection('fitur'))
    <section id="fitur" class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
            <h2 class="text-3xl font-bold tracking-tight text-slate-900">{{ \App\Models\PlatformSetting::hero('fitur_heading', 'Fitur Utama') }}</h2>
            <p class="mt-4 text-slate-600">{{ \App\Models\PlatformSetting::hero('fitur_subtitle', 'Semua kebutuhan administrasi sekolah modern dalam satu aplikasi yang mudah digunakan.') }}</p>
        </div>

        <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach (\App\Models\PlatformSetting::features() as $feature)
                @php
                    $featureIcon = config('platform.feature_icons')[$feature['icon']]
                        ?? config('platform.feature_icons')[array_key_first(config('platform.feature_icons'))];
                @endphp
                <div class="rounded-2xl border border-slate-200 p-6 transition hover:border-primary/30 hover:shadow-md">
                    <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-primary/15">
                        <svg class="h-6 w-6 text-primary" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $featureIcon }}"/>
                        </svg>
                    </div>
                    <h3 class="mt-4 text-lg font-semibold text-slate-900">{{ $feature['title'] }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $feature['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </section>
    @endif

    {{-- TAMPILAN / SCREENSHOTS --}}
    @if (\App\Models\PlatformSetting::showSection('tampilan'))
    <section id="tampilan" class="bg-slate-50 py-20">
        @php $shots = \App\Models\PlatformSetting::shots(); @endphp
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-3xl font-bold tracking-tight text-slate-900">{{ \App\Models\PlatformSetting::hero('tampilan_heading', 'Tampilan Aplikasi') }}</h2>
                <p class="mt-4 text-slate-600">{{ \App\Models\PlatformSetting::hero('tampilan_subtitle', 'Antarmuka yang intuitif untuk setiap peran pengguna.') }}</p>
            </div>

            <div class="mt-14 grid gap-8 md:grid-cols-2">
                @foreach ($shots as $shot)
                    @php $shotIcon = config('platform.feature_icons')[$shot['icon']] ?? config('platform.feature_icons')[array_key_first(config('platform.feature_icons'))]; @endphp
                    <div class="group">
                        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition group-hover:shadow-lg">
                            <div class="bg-slate-100 px-4 py-2">
                                <div class="flex items-center gap-1.5">
                                    <div class="h-2.5 w-2.5 rounded-full bg-red-400"></div>
                                    <div class="h-2.5 w-2.5 rounded-full bg-amber-400"></div>
                                    <div class="h-2.5 w-2.5 rounded-full bg-primary"></div>
                                    <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $shotIcon }}"/></svg><span class="ml-2 truncate text-[10px] text-slate-400">{{ $shot['label'] ?? 'Tampilan Aplikasi' }}</span>
                                </div>
                            </div>
                            <div class="relative h-64 bg-slate-50">
                                @if (! empty($shot['image']))
                                    <img src="{{ asset('storage/' . $shot['image']) }}" alt="{{ $shot['label'] ?? 'Tampilan Aplikasi' }}" class="h-full w-full object-cover object-top">
                                @else
                                    <div class="flex h-full w-full flex-col items-center justify-center text-center">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/15">
                                            <svg class="h-6 w-6 text-primary/50" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $shotIcon }}"/>
                                            </svg>
                                        </div>
                                        <p class="mt-3 text-xs font-medium text-slate-400">Belum ada screenshot</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <p class="mt-3 text-center text-sm font-medium text-slate-600">{{ $shot['caption'] ?? '' }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- MODUL PERAN --}}
    @if (\App\Models\PlatformSetting::showSection('peran'))
    <section id="modul" class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
            <h2 class="text-3xl font-bold tracking-tight text-slate-900">{{ \App\Models\PlatformSetting::hero('peran_heading', 'Dibuat untuk Setiap Peran') }}</h2>
            <p class="mt-4 text-slate-600">{{ \App\Models\PlatformSetting::hero('peran_subtitle', 'Setiap peran memiliki dashboard dan akses yang disesuaikan dengan tugasnya.') }}</p>
        </div>

        @php
            $roles = \App\Models\PlatformSetting::roles();
            $roleChips = [
                'bg-primary/15 text-primary',
                'bg-blue-100 text-blue-700',
                'bg-violet-100 text-violet-700',
                'bg-amber-100 text-amber-700',
            ];
        @endphp

        <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($roles as $i => $role)
                @php $roleIcon = config('platform.feature_icons')[$role['icon']] ?? config('platform.feature_icons')[array_key_first(config('platform.feature_icons'))]; @endphp
                <div class="rounded-2xl border border-slate-200 bg-white p-6 transition hover:border-primary/30 hover:shadow-md">
                    <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-lg {{ $roleChips[$i % count($roleChips)] }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $roleIcon }}"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-semibold text-slate-900">{{ $role['title'] }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $role['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </section>
    @endif

    {{-- CARA KERJA --}}
    <section class="bg-slate-50 py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-3xl font-bold tracking-tight text-slate-900">Cara Kerja</h2>
                <p class="mt-4 text-slate-600">Alur sederhana yang menghubungkan sekolah, guru, dan orang tua.</p>
            </div>

            <div class="mt-14 grid gap-8 md:grid-cols-3">
                <div class="text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-primary-dark text-lg font-bold text-white">1</div>
                    <h3 class="mt-4 text-lg font-semibold text-slate-900">Registrasi Sekolah</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">
                        Daftarkan sekolah Anda. Tim kami akan mengatur akun dan memandu proses setup awal.
                    </p>
                </div>
                <div class="text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-primary-dark text-lg font-bold text-white">2</div>
                    <h3 class="mt-4 text-lg font-semibold text-slate-900">Input Data</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">
                        Import data siswa dan guru via Excel. Setup kelas, mapel, dan plotting guru dalam hitungan menit.
                    </p>
                </div>
                <div class="text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-primary-dark text-lg font-bold text-white">3</div>
                    <h3 class="mt-4 text-lg font-semibold text-slate-900">Gunakan Setiap Hari</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">
                        Guru absensi dari ponsel, admin kelola data, orang tua pantau anak. Semua terhubung secara real-time.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- HARGA / PRICING --}}
    @if (\App\Models\PlatformSetting::showSection('pricing'))
    <section id="harga" class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
            <h2 class="text-3xl font-bold tracking-tight text-slate-900">{{ \App\Models\PlatformSetting::pricingHeading() }}</h2>
            <p class="mt-4 text-slate-600">{{ \App\Models\PlatformSetting::pricingSubtitle() }}</p>
        </div>

        <div class="mt-14 grid gap-8 lg:grid-cols-2 xl:grid-cols-4">
            @foreach (\App\Models\PlatformSetting::plans() as $plan)
            @php
                $accent = $plan['accent'] ?? 'none';
                $isPrimary = $accent === 'primary';
                $isFeatured = $accent === 'red';
                $cardClass = $isFeatured
                    ? 'relative flex flex-col rounded-2xl border-2 border-primary-dark bg-gradient-to-br from-primary to-primary-dark p-8 shadow-xl text-white'
                    : ($isPrimary
                        ? 'relative flex flex-col rounded-2xl border-2 border-primary bg-white p-8 shadow-lg'
                        : 'flex flex-col rounded-2xl border border-slate-200 bg-white p-8');
            @endphp
            <div class="{{ $cardClass }}">
                @if (!empty($plan['badge']))
                    <div class="absolute -top-3 right-6 rounded-full px-3 py-1 text-xs font-bold {{ $isFeatured ? 'bg-white text-primary-dark' : 'bg-primary text-white' }}">{{ $plan['badge'] }}</div>
                @endif
                <div class="mb-6">
                    <h3 class="text-lg font-semibold {{ $isFeatured ? 'text-white' : 'text-slate-900' }}">{{ $plan['name'] ?? '' }}</h3>
                    <p class="mt-1 text-sm {{ $isFeatured ? 'text-white/80' : 'text-slate-500' }}">{{ $plan['tagline'] ?? '' }}</p>
                </div>
                <div class="mb-6">
                    <span class="text-4xl font-extrabold {{ $isPrimary ? 'text-primary' : ($isFeatured ? 'text-white' : 'text-slate-900') }}">{{ $plan['price'] ?? '' }}</span>
                    <span class="text-sm {{ $isFeatured ? 'text-white/70' : 'text-slate-500' }}">{{ $plan['period'] ?? '' }}</span>
                </div>
                <ul class="mb-8 flex-1 space-y-3 text-sm {{ $isFeatured ? 'text-white/90' : 'text-slate-600' }}">
                    @foreach ($plan['features'] ?? [] as $feature)
                        <li class="flex items-center gap-2"><span class="{{ $isFeatured ? 'text-white' : 'text-primary' }}">✓</span> {{ $feature }}</li>
                    @endforeach
                    @foreach ($plan['excludes'] ?? [] as $feature)
                        <li class="flex items-center gap-2 {{ $isFeatured ? 'text-white/60' : 'text-slate-400' }}"><span>✗</span> {{ $feature }}</li>
                    @endforeach
                </ul>
                @if (!empty($plan['note']))
                    <p class="mb-4 text-xs font-medium {{ $isFeatured ? 'text-white/70' : 'text-slate-400' }}">{{ $plan['note'] }}</p>
                @endif
                <a href="{{ route('auth.register') }}"
                    class="block w-full rounded-xl py-3 text-center text-sm font-semibold shadow-sm transition
                        {{ $isFeatured ? 'bg-white text-primary-dark hover:bg-primary/10' : ($isPrimary ? 'bg-primary-dark text-white hover:bg-primary-dark' : 'border border-slate-300 bg-white text-slate-700 hover:border-primary hover:text-primary') }}">
                    {{ $plan['cta'] ?? 'Mulai Gratis 14 Hari' }}
                </a>
            </div>
            @endforeach
        </div>

        <p class="mt-6 text-center text-sm text-slate-500">Semua paket termasuk: SSL gratis, backup otomatis, update fitur, dan masa percobaan 14 hari.</p>
    </section>
    @endif

    {{-- SEKOLAH & TESTIMONI --}}
    @if (\App\Models\PlatformSetting::showSection('sekolah'))
    <section id="sekolah" class="bg-slate-50 py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-3xl font-bold tracking-tight text-slate-900">{{ \App\Models\PlatformSetting::hero('sekolah_heading', 'Sekolah yang Sudah Menggunakan') }}</h2>
                <p class="mt-4 text-slate-600">{{ \App\Models\PlatformSetting::hero('sekolah_subtitle', 'Bergabung bersama sekolah-sekolah Islam terbaik di Indonesia.') }}</p>
            </div>

            <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($schools as $school)
                <div class="rounded-2xl border border-slate-200 bg-white p-6 transition hover:border-primary/30 hover:shadow-md">
                    <div class="flex items-center gap-4">
                        @if ($school->logo)
                            <img src="{{ asset('storage/' . $school->logo) }}" alt="{{ $school->name }}" class="h-14 w-14 rounded-xl object-cover shadow-sm">
                        @else
                            <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-gradient-to-br from-primary to-secondary text-lg font-bold text-white shadow-lg shadow-primary/25">
                                {{ strtoupper(substr($school->name, 0, 2)) }}
                            </div>
                        @endif
                        <div>
                            <h3 class="font-semibold text-slate-900">{{ $school->name }}</h3>
                            <p class="text-xs text-slate-500">{{ ucfirst($school->plan) }} Plan</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            @php $testimonials = \App\Models\PlatformSetting::testimonials(); @endphp
            @if ($testimonials !== [])
            <div class="mx-auto max-w-2xl text-center mt-20">
                <h3 class="text-2xl font-bold tracking-tight text-slate-900">{{ \App\Models\PlatformSetting::hero('testi_heading', 'Apa Kata Mereka?') }}</h3>
                <p class="mt-4 text-slate-600">{{ \App\Models\PlatformSetting::hero('testi_subtitle', 'Testimoni dari admin sekolah yang sudah menggunakan aplikasi ini.') }}</p>
            </div>

            @php
                $testiAvatars = [
                    'bg-primary/15 text-primary',
                    'bg-blue-100 text-blue-700',
                    'bg-violet-100 text-violet-700',
                ];
            @endphp

            <div class="mt-14 grid gap-8 md:grid-cols-3">
                @foreach ($testimonials as $i => $testimonial)
                <div class="rounded-2xl border border-slate-200 bg-white p-6">
                    <div class="mb-4 flex gap-1 text-amber-400">
                        <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                    </div>
                    <p class="text-sm leading-relaxed text-slate-600">"{{ $testimonial['quote'] }}"</p>
                    <div class="mt-4 flex items-center gap-3 border-t border-slate-100 pt-4">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full {{ $testiAvatars[$i % count($testiAvatars)] }} text-sm font-bold">{{ $testimonial['initials'] }}</div>
                        <div>
                            <p class="text-sm font-semibold text-slate-900">{{ $testimonial['author'] }}</p>
                            <p class="text-xs text-slate-500">{{ $testimonial['role'] }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </section>
    @endif

    {{-- BLOG --}}
    @if ($posts->count() > 0)
    <section id="blog" class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
            <h2 class="text-3xl font-bold tracking-tight text-slate-900">Blog & Artikel</h2>
            <p class="mt-4 text-slate-600">Tips, berita, dan panduan seputar manajemen sekolah Islam terpadu.</p>
        </div>

        <div class="mt-14 grid gap-8 md:grid-cols-3">
            @foreach ($posts as $post)
                <a href="{{ route('blog.show', $post->slug) }}" class="group rounded-2xl border border-slate-200 bg-white p-6 transition hover:border-primary/30 hover:shadow-md">
                    @if ($post->featured_image)
                        <div class="mb-4 overflow-hidden rounded-xl">
                            <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" class="h-48 w-full object-cover transition group-hover:scale-105">
                        </div>
                    @else
                        <div class="mb-4 flex h-48 items-center justify-center rounded-xl bg-primary/10">
                            <svg class="h-12 w-12 text-primary" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/>
                            </svg>
                        </div>
                    @endif
                    <div class="flex items-center gap-2 mb-3">
                        <span class="rounded-full bg-primary/15 px-2.5 py-0.5 text-xs font-semibold text-primary-dark">{{ ucfirst($post->category) }}</span>
                        <span class="text-xs text-slate-400">{{ $post->published_at->diffForHumans() }}</span>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 group-hover:text-primary">{{ $post->title }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600 line-clamp-2">{{ $post->excerpt ?: Str::limit(strip_tags($post->body), 120) }}</p>
                    <p class="mt-3 text-xs text-slate-400">{{ number_format($post->visit_count) }} kunjungan</p>
                </a>
            @endforeach
        </div>

        <div class="mt-10 text-center">
            <a href="{{ route('blog.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-6 py-3 text-sm font-semibold text-slate-700 transition hover:border-primary hover:text-primary">
                Lihat Semua Artikel
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
    </section>
    @endif

    {{-- FAQ --}}
    <section id="faq" class="bg-slate-50 py-20">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-3xl font-bold tracking-tight text-slate-900">Pertanyaan Umum</h2>
                <p class="mt-4 text-slate-600">Jawaban atas pertanyaan yang sering ditanyakan.</p>
            </div>

            <div class="mt-14 space-y-4">
                <div class="rounded-xl border border-slate-200 bg-white p-5">
                    <h3 class="text-sm font-semibold text-slate-900">Apakah data sekolah aman?</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">
                        Ya. Kami menggunakan enkripsi SSL, backup otomatis harian, dan server berlokasi di Indonesia. Data Anda hanya bisa diakses oleh pengguna yang memiliki otorisasi.
                    </p>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-5">
                    <h3 class="text-sm font-semibold text-slate-900">Berapa lama proses setup?</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">
                        Setup awal hanya butuh 5-30 menit tergantung ukuran sekolah. Tim kami akan membantu proses import data siswa dan guru via Excel.
                    </p>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-5">
                    <h3 class="text-sm font-semibold text-slate-900">Bisa custom sesuai kebutuhan sekolah?</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">
                        Ya. Kami bisa menyesuaikan fitur tertentu sesuai kebutuhan sekolah Anda. Hubungi tim kami untuk diskusi lebih lanjut.
                    </p>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-5">
                    <h3 class="text-sm font-semibold text-slate-900">Apakah ada masa percobaan gratis?</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">
                        Ya! Semua sekolah mendapatkan masa percobaan gratis 14 hari tanpa kartu kredit. Anda bisa mencoba semua fitur sebelum memutuskan berlangganan.
                    </p>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-5">
                    <h3 class="text-sm font-semibold text-slate-900">Bagaimana jika butuh bantuan?</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">
                        Tim support kami tersedia via WhatsApp. Untuk paket Pro, support diberikan prioritas dengan respon lebih cepat.
                    </p>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-5">
                    <h3 class="text-sm font-semibold text-slate-900">Bisa diakses dari mana saja?</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">
                        Ya. Aplikasi berbasis web yang bisa diakses dari komputer, tablet, atau ponsel asalkan terhubung ke internet. Tidak perlu install aplikasi tambahan.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section id="tentang" class="bg-gradient-to-br from-primary to-primary-dark py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl text-center">
                <h2 class="text-3xl font-bold tracking-tight text-white">Siap Memodernisasi Sekolah Anda?</h2>
                <p class="mt-4 text-white/80">
                    Gabung bersama {{ \App\Models\PlatformSetting::appName() }} dan kelola seluruh administrasi sekolah secara digital, cepat, dan aman.
                </p>
                <div class="mt-8 flex flex-wrap justify-center gap-4">
                    @auth
                        <a href="{{ route(auth()->user()->role === 'superadmin' ? 'platform.dashboard' : auth()->user()->role . '.dashboard') }}"
                            class="rounded-lg bg-white px-6 py-3 text-sm font-semibold text-primary-dark transition hover:bg-primary/10">
                            Buka Dashboard
                        </a>
                    @else
                        <a href="{{ route('auth.register') }}"
                            class="rounded-lg bg-white px-6 py-3 text-sm font-semibold text-primary-dark transition hover:bg-primary/10">
                            Daftar Gratis Sekarang
                        </a>
                        <a href="{{ route('auth.login') }}"
                            class="rounded-lg border border-white px-6 py-3 text-sm font-semibold text-white transition hover:bg-white/10">
                            Masuk ke Akun
                        </a>
                    @endauth
                </div>
                <p class="mt-4 text-xs text-white/70">Tanpa kartu kredit · Gratis 14 hari · Batal kapan saja</p>
            </div>
        </div>
    </section>

    {{-- FOOTER --}}
    @include('partials.site-footer')

</body>
</html>

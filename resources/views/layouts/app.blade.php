<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    @php
        $school = auth()->user()->school;

        if (!function_exists('darkenHex')) {
            function darkenHex($hex, $factor) {
                $hex = ltrim((string) $hex, '#');
                $dark = function ($c) use ($hex, $factor) {
                    return (int) round(hexdec(substr($hex, $c, 2)) * $factor);
                };
                return sprintf('#%02x%02x%02x', min(255, $dark(0)), min(255, $dark(2)), min(255, $dark(4)));
            }
        }

        $themePrimary = $school?->primary_color ?? \App\Models\PlatformSetting::primaryColor();
        $themeSecondary = $school?->secondary_color ?? \App\Models\PlatformSetting::secondaryColor();
    @endphp
    @php $favicon = $school?->logo ?? \App\Models\PlatformSetting::logo(); @endphp
    @if ($favicon)
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $favicon) }}">
    @endif
    @include('partials._vite')
    <style>
        :root {
            --color-primary: {{ $themePrimary }};
            --color-secondary: {{ $themeSecondary }};
            --color-primary-dark: {{ darkenHex($themePrimary, 0.88) }};
            --color-secondary-dark: {{ darkenHex($themeSecondary, 0.88) }};
        }
    </style>
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-900 dark:bg-[#0a0a0a] dark:text-slate-100 transition-colors duration-200">
    @php
        $user = auth()->user();
        $brand = $user->school?->name ?? \App\Models\PlatformSetting::appName();
        $brandSub = $user->school_id ? 'Sistem Manajemen Sekolah' : 'Platform Manajemen Sekolah';
        $role = $user->role;
        $primary = $school?->primary_color ?? \App\Models\PlatformSetting::primaryColor();
        $secondary = $school?->secondary_color ?? \App\Models\PlatformSetting::secondaryColor();
        $logoPath = $school?->logo ?? \App\Models\PlatformSetting::logo();
        $logo = $logoPath ? asset('storage/' . $logoPath) : null;

        $links = match ($role) {
            'superadmin' => [
                ['route' => 'platform.dashboard', 'label' => 'Dashboard Platform', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                ['route' => 'platform.schools.index', 'label' => 'Kelola Sekolah', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                ['route' => 'platform.kaldik-templates.index', 'label' => 'Template KALDIK', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                ['route' => 'platform.recurring-holidays.index', 'label' => 'Libur Rutin', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                [
                    'label' => 'Pengaturan Web',
                    'icon' => 'M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9',
                    'children' => [
                        ['route' => 'platform.content.hero', 'label' => 'Hero', 'icon' => 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z'],
                        ['route' => 'platform.content.features', 'label' => 'Fitur Utama', 'icon' => 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z'],
                        ['route' => 'platform.content.tampilan', 'label' => 'Tampilan Aplikasi', 'icon' => 'M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3'],
                        ['route' => 'platform.content.peran', 'label' => 'Peran', 'icon' => 'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z'],
                        ['route' => 'platform.content.pricing', 'label' => 'Paket Harga', 'icon' => 'M9.568 3h5.932a2.25 2.25 0 0 1 1.275.412l5.603 3.914a.75.75 0 0 1 .364.744l-.748 11.219a2.25 2.25 0 0 1-2.243 2.111H4.247a2.25 2.25 0 0 1-2.243-2.111L1.256 8.07a.75.75 0 0 1 .364-.744L7.223 3.412A2.25 2.25 0 0 1 8.498 3zm2.5 6a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5z'],
                        ['route' => 'platform.content.sekolah', 'label' => 'Sekolah & Testimoni', 'icon' => 'M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 1.5h.75m-.75 3h.75m-.75 3h.75'],
                        ['route' => 'platform.content.footer', 'label' => 'Footer', 'icon' => 'M4.5 6.75h15M4.5 12h15M4.5 17.25h15'],
                        ['route' => 'platform.blog.index', 'label' => 'Blog', 'icon' => 'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z'],
                        ['route' => 'platform.settings.index', 'label' => 'Pengaturan Platform', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
                    ],
                ],
            ],
            'admin' => [
                ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                ['route' => 'admin.kelas.index', 'label' => 'Kelas', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                ['route' => 'admin.subject.index', 'label' => 'Mata Pelajaran', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
                ['route' => 'admin.academic-years.index', 'label' => 'Tahun Ajaran', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                ['route' => 'admin.quran.index', 'label' => 'Master Quran', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
                ['route' => 'admin.reading-levels.index', 'label' => 'Jenjang Baca Alquran', 'icon' => 'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z'],
                ['route' => 'admin.grade-types.index', 'label' => 'Tipe Nilai', 'icon' => 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z'],
                ['route' => 'admin.plotting.index', 'label' => 'Plotting Mapel', 'icon' => 'M4 6h16M4 10h16M4 14h16M4 18h16'],
                ['route' => 'admin.pengguna.index', 'label' => 'Pengguna', 'icon' => 'M12 4.354a4 4 0 110 7.292 4 4 0 010-7.292zM15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
                ['route' => 'admin.tahfidz.index', 'label' => 'Overview Tahfidz', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
                ['route' => 'admin.rekap.index', 'label' => 'Rekap Laporan', 'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                ['route' => 'admin.setting.profile', 'label' => 'Pengaturan', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
                ['route' => 'admin.setting.billing', 'label' => 'Paket & Billing', 'icon' => 'M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z'],
            ],
            'guru' => array_merge(
                [['route' => 'guru.dashboard', 'label' => 'Dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6']],
                $user->is_wali_kelas ? [['route' => 'guru.absensi-kelas.index', 'label' => 'Absensi Kelas', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4']] : [],
                $user->hasSubjectMapping() && !$user->hasQuranAssignment() ? [
                    ['route' => 'guru.absensi-mapel.index', 'label' => 'Absensi Mapel', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253m-4.5-13V5a2 2 0 012-2h2a2 2 0 012 2v1M6 7h12M6 11h12M6 15h8'],
                    ['route' => 'guru.nilai.index', 'label' => 'Input Nilai', 'icon' => 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z'],
                    ['route' => 'guru.nilai.rekap', 'label' => 'Rekap Nilai', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                ] : [],
                $user->is_wali_kelas ? [['route' => 'guru.absensi-kelas.rekap', 'label' => 'Rekap Absensi Kelas', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z']] : [],
                $user->isWaliKelasActive() ? $user->waliClasses()->get()->map(fn ($c) => ['route' => 'guru.tahfidz-kelas.index', 'params' => ['class_id' => $c->id], 'label' => 'Rekap Tahfiz ' . $c->class_name, 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'])->all() : [],
                $user->isWaliKelasActive() ? [['route' => 'guru.rapor.index', 'label' => 'Rapor', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253']] : [],
                $user->hasSubjectMapping() && !$user->hasQuranAssignment() ? [['route' => 'guru.absensi-mapel.rekap', 'label' => 'Rekap Absensi Mapel', 'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z']] : [],
                $user->hasTeacherRole('PJ Tahfidz') ? [['route' => 'guru.tahfidz.index', 'label' => 'Hafalan Tahfidz', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253']] : [],
                $user->hasTeacherRole('PJ Tahfidz') ? [['route' => 'guru.tahfidz.rekap', 'label' => 'Rekap Hafalan', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z']] : [],
                $user->hasTeacherRole('PJ Tahfidz') ? [['route' => 'guru.quran-assignment.index', 'label' => 'Assignment Al-Quran', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z']] : [],
                $user->hasQuranAssignment() ? [
                    ['route' => 'guru.quran-hafalan.students', 'label' => 'Input Hafalan', 'icon' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z'],
                    ['route' => 'guru.quran-tilawah.students', 'label' => 'Input Tilawah', 'icon' => 'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z'],
                    ['route' => 'guru.quran-absensi.rekap', 'label' => 'Rekap Kehadiran', 'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                ] : [],
                [['route' => 'guru.kaldik.index', 'label' => 'KALDIK', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z']],
                [['route' => 'guru.profile.index', 'label' => 'Profil', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z']],
            ),
            'wakasek' => [
                ['route' => 'wakasek.dashboard', 'label' => 'Dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                ['route' => 'wakasek.kaldik.index', 'label' => 'KALDIK', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                ['route' => 'wakasek.base.classes', 'label' => 'Kelas & Siswa', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                ['route' => 'wakasek.base.teachers', 'label' => 'Guru & Tugas', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                ['route' => 'wakasek.base.roster', 'label' => 'Roster', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['route' => 'wakasek.base.prota', 'label' => 'Prota', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                ['route' => 'wakasek.base.prosem', 'label' => 'Prosem', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                ['route' => 'wakasek.base.capaian-pembelajaran', 'label' => 'CP', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                ['route' => 'wakasek.base.atp', 'label' => 'ATP', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                ['route' => 'wakasek.base.modul-ajar', 'label' => 'Modul Ajar', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
                ['route' => 'wakasek.rapor-design.index', 'label' => 'Desain Rapor', 'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                ['route' => 'wakasek.quran-assignment.index', 'label' => 'Assignment Al-Quran', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
            ],
            'kepsek' => [
                ['route' => 'kepsek.dashboard', 'label' => 'Dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                ['route' => 'kepsek.kaldik', 'label' => 'Approval KALDIK', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['route' => 'kepsek.guru.teachers', 'label' => 'Guru & Tugas', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                ['route' => 'kepsek.roster', 'label' => 'Roster', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['route' => 'kepsek.tahfidz.index', 'label' => 'Tahfidz', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
                ['route' => 'kepsek.classes', 'label' => 'Kelas', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                ['route' => 'kepsek.students', 'label' => 'Siswa', 'icon' => 'M12 4.354a4 4 0 110 7.292 4 4 0 010-7.292zM15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z'],
                ['route' => 'kepsek.quran-assignment.index', 'label' => 'Assignment Al-Quran', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
            ],
            'wakamur' => [
                ['route' => 'wakasek.dashboard', 'label' => 'Dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                ['route' => 'wakasek.base.students', 'label' => 'Siswa', 'icon' => 'M12 4.354a4 4 0 110 7.292 4 4 0 010-7.292zM15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z'],
                ['route' => 'wakasek.base.classes', 'label' => 'Kelas', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
            ],
            'murid' => [
                ['route' => 'murid.dashboard', 'label' => 'Dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                ['route' => 'murid.hafalan', 'label' => 'Hafalan Quran', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
            ],
            'keuangan' => [['route' => 'keuangan.dashboard', 'label' => 'Dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6']],
            'staff' => [['route' => 'staff.dashboard', 'label' => 'Dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6']],
            default => [
                ['route' => 'ortu.dashboard', 'label' => 'Dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                ['route' => 'ortu.absensi', 'label' => 'Absensi', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['route' => 'ortu.nilai', 'label' => 'Nilai', 'icon' => 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z'],
                ['route' => 'ortu.hafalan', 'label' => 'Hafalan Quran', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
                ['route' => 'ortu.kaldik.index', 'label' => 'KALDIK', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
            ],
        };

        $links[] = ['route' => 'passkey.index', 'label' => 'Keamanan Akun', 'icon' => 'M4.5 12.75l6 6 9-13.5'];

        $ortuBottomNav = [
            ['route' => 'ortu.dashboard', 'label' => 'Beranda', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
            ['route' => 'ortu.absensi', 'label' => 'Absensi', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['route' => 'ortu.nilai', 'label' => 'Nilai', 'icon' => 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z'],
            ['route' => 'ortu.hafalan', 'label' => 'Hafalan', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
            ['route' => 'ortu.kaldik.index', 'label' => 'KALDIK', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
        ];

        if ($user->school instanceof \App\Models\School && ! in_array($role, ['superadmin', 'staff'], true)) {
            $links = \App\Support\PlanMenu::filter($links, $user->school);
            $ortuBottomNav = \App\Support\PlanMenu::filter($ortuBottomNav, $user->school);
        }

        if (!function_exists('hexToRgb')) {
            function hexToRgb($hex) {
                $hex = ltrim($hex, '#');
                return [
                    'r' => hexdec(substr($hex, 0, 2)),
                    'g' => hexdec(substr($hex, 2, 2)),
                    'b' => hexdec(substr($hex, 4, 2)),
                ];
            }
        }

        $p = hexToRgb($primary);
        $s = hexToRgb($secondary);
    @endphp

    <div class="min-h-screen flex">
        <!-- Mobile backdrop -->
        <div id="sidebar-backdrop" class="hidden fixed inset-0 bg-black/50 z-40 backdrop-blur-sm lg:hidden" onclick="closeMobileSidebarFn()"></div>

        <!-- Mobile sidebar -->
        <aside id="sidebar"
            class="fixed inset-y-0 left-0 z-50 w-64 text-white -translate-x-full lg:hidden transition-transform duration-200 ease-out flex flex-col"
            style="background-color: {{ $primary }}">
            <div class="px-4 py-5 flex items-center justify-between" style="border-bottom: 1px solid rgba(255,255,255,0.1)">
                <div class="min-w-0 flex items-center gap-3">
                    @if ($logo)
                        <img src="{{ $logo }}" alt="Logo" class="w-9 h-9 rounded-lg object-cover shrink-0">
                    @else
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center text-sm font-bold shrink-0" style="background: rgba(255,255,255,0.15)">
                            {{ strtoupper(substr($brand, 0, 2)) }}
                        </div>
                    @endif
                    <div class="min-w-0">
                        <h1 class="text-sm font-bold text-white truncate">{{ $brand }}</h1>
                        <p class="text-[11px] text-white/50 truncate">{{ $brandSub }}</p>
                    </div>
                </div>
                <button onclick="closeMobileSidebarFn()" class="p-1.5 rounded-lg text-white/50 hover:text-white hover:bg-white/10 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
                @foreach ($links as $link)
                    @if (isset($link['children']))
                        @php
                            $parentActive = collect($link['children'])->contains(fn ($c) => request()->routeIs($c['route']));
                        @endphp
                        <div class="sidebar-group">
                            <button type="button" onclick="toggleSidebarGroup(this)"
                                class="w-full flex items-center justify-between gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-150
                                    {{ $parentActive ? 'text-white' : 'text-white/60 hover:text-white hover:bg-white/5' }}"
                                @if ($parentActive) style="background: rgba(255,255,255,0.15)" @endif>
                                <span class="flex items-center gap-3 min-w-0">
                                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $link['icon'] }}"/></svg>
                                    <span class="truncate">{{ $link['label'] }}</span>
                                </span>
                                <svg data-group-chevron class="w-4 h-4 shrink-0 transition-transform {{ $parentActive ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div data-group-menu class="mt-1 space-y-0.5 {{ $parentActive ? '' : 'hidden' }}">
                                @foreach ($link['children'] as $child)
                                    <a href="{{ route($child['route']) }}"
                                        class="flex items-center gap-3 rounded-lg px-3 py-2.5 pl-6 text-sm font-medium transition-all duration-150
                                            {{ request()->routeIs($child['route']) ? 'text-white' : 'text-white/60 hover:text-white hover:bg-white/5' }}"
                                        @if (request()->routeIs($child['route'])) style="background: rgba(255,255,255,0.15)" @endif>
                                        <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $child['icon'] }}"/></svg>
                                        {{ $child['label'] }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <a href="{{ route($link['route'], $link['params'] ?? []) }}"
                            class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-150
                                {{ request()->routeIs($link['route']) ? 'text-white' : 'text-white/60 hover:text-white hover:bg-white/5' }}"
                            @if (request()->routeIs($link['route'])) style="background: rgba(255,255,255,0.15)" @endif>
                            <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $link['icon'] }}"/></svg>
                            {{ $link['label'] }}
                        </a>
                    @endif
                @endforeach
            </nav>
            <div class="px-3 py-3" style="border-top: 1px solid rgba(255,255,255,0.1)">
                <div class="flex items-center gap-3 px-2 mb-3">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold text-white shrink-0" style="background: rgba(255,255,255,0.15)">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-white truncate">{{ $user->name }}</p>
                        <p class="text-[11px] text-white/40 uppercase tracking-wider">{{ $user->role }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('auth.logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-white/60 hover:text-white hover:bg-white/10 transition">
                        <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Keluar
                    </button>
                </form>
            </div>
        </aside>

        <!-- Desktop sidebar -->
        <aside id="sidebar-desktop"
            class="hidden lg:flex w-64 flex-col text-white transition-sidebar fixed inset-y-0 left-0 z-30"
            style="background-color: {{ $primary }}">
            <div class="px-4 py-5 flex items-center justify-between" style="border-bottom: 1px solid rgba(255,255,255,0.1)">
                <div class="min-w-0 flex items-center gap-3">
                    @if ($logo)
                        <img src="{{ $logo }}" alt="Logo" class="w-9 h-9 rounded-lg object-cover shrink-0">
                    @else
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center text-sm font-bold shrink-0" style="background: rgba(255,255,255,0.15)">
                            {{ strtoupper(substr($brand, 0, 2)) }}
                        </div>
                    @endif
                    <div class="min-w-0 sidebar-text">
                        <h1 class="text-sm font-bold text-white truncate">{{ $brand }}</h1>
                        <p class="text-[11px] text-white/50 truncate">{{ $brandSub }}</p>
                    </div>
                </div>
            </div>
            <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
                @foreach ($links as $link)
                    @if (isset($link['children']))
                        @php
                            $parentActive = collect($link['children'])->contains(fn ($c) => request()->routeIs($c['route']));
                        @endphp
                        <div class="sidebar-group">
                            <button type="button" onclick="toggleSidebarGroup(this)" title="{{ $link['label'] }}"
                                class="w-full flex items-center justify-between gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-150 group
                                    {{ $parentActive ? 'text-white' : 'text-white/60 hover:text-white hover:bg-white/5' }}"
                                @if ($parentActive) style="background: rgba(255,255,255,0.15)" @endif>
                                <span class="flex items-center gap-3 min-w-0">
                                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $link['icon'] }}"/></svg>
                                    <span class="sidebar-text truncate">{{ $link['label'] }}</span>
                                </span>
                                <svg data-group-chevron class="w-4 h-4 shrink-0 transition-transform {{ $parentActive ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div data-group-menu class="mt-1 space-y-0.5 {{ $parentActive ? '' : 'hidden' }}">
                                @foreach ($link['children'] as $child)
                                    <a href="{{ route($child['route']) }}" title="{{ $child['label'] }}"
                                        class="flex items-center gap-3 rounded-lg px-3 py-2.5 pl-6 text-sm font-medium transition-all duration-150 group
                                            {{ request()->routeIs($child['route']) ? 'text-white' : 'text-white/60 hover:text-white hover:bg-white/5' }}"
                                        @if (request()->routeIs($child['route'])) style="background: rgba(255,255,255,0.15)" @endif>
                                        <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $child['icon'] }}"/></svg>
                                        <span class="sidebar-text">{{ $child['label'] }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <a href="{{ route($link['route'], $link['params'] ?? []) }}"
                            class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-150 group
                                {{ request()->routeIs($link['route']) ? 'text-white' : 'text-white/60 hover:text-white hover:bg-white/5' }}"
                            @if (request()->routeIs($link['route'])) style="background: rgba(255,255,255,0.15)" @endif
                            title="{{ $link['label'] }}">
                            <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $link['icon'] }}"/></svg>
                            <span class="sidebar-text">{{ $link['label'] }}</span>
                        </a>
                    @endif
                @endforeach
            </nav>
            <div class="px-3 py-3" style="border-top: 1px solid rgba(255,255,255,0.1)">
                <div class="flex items-center gap-3 px-2 mb-3">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold text-white shrink-0" style="background: rgba(255,255,255,0.15)">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1 sidebar-text">
                        <p class="text-sm font-medium text-white truncate">{{ $user->name }}</p>
                        <p class="text-[11px] text-white/40 uppercase tracking-wider">{{ $user->role }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('auth.logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-white/60 hover:text-white hover:bg-white/10 transition">
                        <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span class="sidebar-text">Keluar</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main content -->
        <div id="main-content" class="flex-1 flex flex-col min-w-0 transition-sidebar md:ml-64">
            <!-- Top bar -->
            <header class="sticky top-0 z-20 bg-white/80 dark:bg-[#0a0a0a]/80 backdrop-blur-xl border-b border-slate-200 dark:border-white/10">
                <div class="flex items-center justify-between h-14 px-4 sm:px-6">
                    <div class="flex items-center gap-3">
                        <button id="mobile-menu-btn" onclick="openMobileSidebar()" class="p-2 -ml-2 rounded-lg text-slate-500 dark:text-white/50 hover:bg-slate-100 dark:hover:bg-white/10 lg:hidden transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </button>
                        <button id="sidebar-toggle" onclick="toggleSidebar()" class="hidden lg:flex p-2 -ml-2 rounded-lg text-slate-400 dark:text-white/40 hover:bg-slate-100 dark:hover:bg-white/10 transition" title="Toggle sidebar">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </button>
                    </div>
                    <div class="flex items-center gap-2">
                        @php $ay = app(App\Services\AcademicYearContext::class)->get(); @endphp
                        @if ($ay)
                            <span class="inline-flex items-center gap-1.5 rounded-lg bg-primary/10 dark:bg-primary/100/10 border border-primary/20 dark:border-primary/20 px-2.5 py-1 text-xs font-medium text-primary dark:text-primary">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                {{ $ay->name }}
                            </span>
                        @endif
                        <button onclick="toggleDarkMode()" class="p-2 rounded-lg text-slate-500 dark:text-white/50 hover:bg-slate-100 dark:hover:bg-white/10 transition" title="Toggle dark mode">
                            <svg class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            <svg class="w-5 h-5 block dark:hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                        </button>
                    </div>
                </div>
            </header>

            <!-- Page content -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8 animate-fade-in {{ $role === 'ortu' ? 'pb-24 lg:pb-8' : '' }}">
                @if (session('status'))
                    <div class="mb-5 rounded-xl bg-gradient-to-r from-primary/10 to-secondary/10 dark:from-primary/10 dark:to-secondary/10 border border-primary/20 dark:border-primary/20 px-4 py-3 text-sm text-primary dark:text-primary flex items-center gap-2.5">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        {{ session('status') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="mb-5 rounded-xl bg-gradient-to-r from-red-50 to-rose-50 dark:from-red-500/10 dark:to-rose-500/10 border border-red-200 dark:border-red-500/20 px-4 py-3 text-sm text-red-700 dark:text-red-400 flex items-center gap-2.5">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        {{ session('error') }}
                    </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
    @if ($role === 'ortu')
    <nav class="fixed bottom-0 inset-x-0 z-40 lg:hidden border-t border-slate-200 dark:border-white/10 bg-white/95 dark:bg-[#0a0a0a]/95 backdrop-blur-xl">
        <div class="grid grid-cols-5">
            @foreach ($ortuBottomNav as $item)
                <a href="{{ route($item['route']) }}"
                    class="flex flex-col items-center gap-0.5 py-2.5 text-[11px] font-medium transition
                        {{ request()->routeIs($item['route']) ? 'text-primary dark:text-primary' : 'text-slate-400 dark:text-white/40 hover:text-slate-600 dark:hover:text-white/70' }}"
                    title="{{ $item['label'] }}">
                    <svg class="w-[22px] h-[22px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/></svg>
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>
    </nav>
    @endif
    <script>
    (function() {
        var spinner = '<svg class="animate-spin h-4 w-4 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>';
        document.querySelectorAll('form').forEach(function(form) {
            form.addEventListener('submit', function() {
                var btn = form.querySelector('button[type="submit"]');
                if (!btn || btn.disabled) return;
                btn.disabled = true;
                btn.dataset.originalHtml = btn.innerHTML;
                var text = btn.getAttribute('data-loading-text')
                    || pickLoadingText(btn.textContent.trim()) ;
                btn.innerHTML = spinner + ' <span>' + text + '</span>';
            });
        });
        function pickLoadingText(label) {
            if (/hapus|delete/i.test(label)) return 'Menghapus...';
            if (/impor|import/i.test(label)) return 'Mengimpor...';
            if (/unduh|download|ekspor|export/i.test(label)) return 'Mendownload...';
            return 'Memproses...';
        }
        window.addEventListener('pageshow', function() {
            document.querySelectorAll('button[disabled]').forEach(function(btn) {
                if (btn.dataset.originalHtml) {
                    btn.disabled = false;
                    btn.innerHTML = btn.dataset.originalHtml;
                    delete btn.dataset.originalHtml;
                }
            });
        });
    })();
    </script>
        @stack('scripts')
    </body>
</html>

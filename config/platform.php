<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Ikon Fitur Utama
    |--------------------------------------------------------------------------
    |
    | Kumpulan ikon (Heroicons outline) yang dapat dipilih superadmin untuk
    | setiap kartu pada section Fitur Utama di halaman beranda.
    |
    */

    'feature_icons' => [
        'data-master' => 'M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5',
        'rfid' => 'M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z',
        'absensi' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
        'hafalan' => 'M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25',
        'nilai' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z',
        'notif' => 'M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0',
        'spp' => 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z',
        'users' => 'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Fitur Utama
    |--------------------------------------------------------------------------
    | Kartu yang tampil di landing jika belum disesuaikan superadmin.
    */

    /*
    |--------------------------------------------------------------------------
    | Default Kartu Tampilan Aplikasi
    |--------------------------------------------------------------------------
    | Kartu yang tampil di landing pada section Tampilan Aplikasi jika belum
    | disesuaikan superadmin. Setiap kartu terdiri dari ikon, label (judul bar),
    | caption, dan gambar screenshoot (image) yang di-upload via storage public.
    */

    'tampilan_defaults' => [
        ['icon' => 'data-master', 'label' => 'Admin Dashboard', 'caption' => 'Dashboard Admin Sekolah', 'image' => null],
        ['icon' => 'nilai', 'label' => 'Guru Dashboard', 'caption' => 'Dashboard Guru', 'image' => null],
        ['icon' => 'absensi', 'label' => 'Absensi Mapel', 'caption' => 'Absensi Per Mata Pelajaran', 'image' => null],
        ['icon' => 'hafalan', 'label' => 'Hafalan Al-Qur\'an', 'caption' => 'Pencatatan Hafalan', 'image' => null],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Section "Dibuat untuk Setiap Peran"
    |--------------------------------------------------------------------------
    | Kartu yang tampil di landing pada section peran jika belum disesuaikan
    | superadmin. Setiap kartu terdiri dari ikon, judul peran, dan deskripsi.
    */

    'peran_defaults' => [
        [
            'icon' => 'data-master',
            'title' => 'Admin Sekolah',
            'desc' => 'Data master, plotting mapel & halqah, import siswa, dan reset password.',
        ],
        [
            'icon' => 'nilai',
            'title' => 'Guru',
            'desc' => 'Absensi mapel, input nilai, e-Rapor kelas, kelola hafalan, dan rekap absensi.',
        ],
        [
            'icon' => 'hafalan',
            'title' => 'Wakil Kepala Kurikulum',
            'desc' => 'Kalender akademik, KALDIK, dan pengawasan kurikulum sekolah.',
        ],
        [
            'icon' => 'users',
            'title' => 'Orang Tua',
            'desc' => 'Pantau kehadiran, log hafalan, nilai, dan status SPP anak.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Section "Sekolah & Testimoni"
    |--------------------------------------------------------------------------
    | Testimoni yang tampil di landing pada section gabungan "Sekolah yang
    | Sudah Menggunakan" jika belum disesuaikan superadmin. Placeholder
    | {app_name} akan diganti dengan nama aplikasi saat dirender.
    */

    'testimonial_defaults' => [
        [
            'quote' => 'Sebelum pakai {app_name}, kami masih absensi manual di buku. Sekarang guru tinggal buka ponsel, langsung selesai. Orang tua juga senang karena bisa pantau anak lewat notifikasi.',
            'author' => 'Ust. Abdullah',
            'role' => 'Admin · SIT Al-Hikmah',
            'initials' => 'AH',
        ],
        [
            'quote' => 'Fitur hafalan Al-Qur\'an sangat membantu. Guru Tahfidz bisa catat ziadah dan murajaah langsung dari kelas. Rekap otomatis, tidak perlu kertas lagi.',
            'author' => 'Ust. Supriyadi',
            'role' => 'Guru Tahfidz · SIT Al-Izzah',
            'initials' => 'SZ',
        ],
        [
            'quote' => 'Import siswa dari Excel sangat mudah. Setup awal hanya butuh 30 menit untuk 200 siswa. Interface-nya sederhana, staf kami yang tidak terlalu paham teknologi pun langsung bisa.',
            'author' => 'Ust. Fadli',
            'role' => 'Admin · SMPIT Darul Fikri',
            'initials' => 'SF',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Section Konten Landing
    |--------------------------------------------------------------------------
    | Daftar section konten yang dapat diaktifkan/dinonaktifkan dari superadmin.
    | Nilai tampilan disimpan di kolom `show_<key>` pada platform_settings.
    */

    'content_sections' => ['hero', 'fitur', 'tampilan', 'peran', 'sekolah', 'footer', 'pricing'],

    /*
    |--------------------------------------------------------------------------
    | Ikon Media Sosial
    |--------------------------------------------------------------------------
    | Kumpulan ikon merek media sosial yang dapat dipilih superadmin untuk
    | setiap tautan pada bagian footer. Nilai adalah atribut `d` pada SVG
    | fill 24x24 (simple-icons).
    */

    'medsos_icons' => [
        'facebook' => 'M9 8H6v4h3v12h5V12h3.642l.383-4H14V6.947C14 6.03 14.237 5.5 15.485 5.5H18V0h-3.6C11.5 0 9 2.486 9 6.22V8z',
        'instagram' => 'M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z',
        'x' => 'M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z',
        'youtube' => 'M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505a3.017 3.017 0 00-2.122 2.136C0 8.059 0 12 0 12s0 3.94.501 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.94 24 12 24 12s0-3.94-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z',
        'tiktok' => 'M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z',
        'linkedin' => 'M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z',
        'whatsapp' => 'M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z',
        'telegram' => 'M11.944 0A12 12 0 000 12a12 12 0 0012 12 12 12 0 0012-12A12 12 0 0012 0a12 12 0 00-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 01.171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Footer
    |--------------------------------------------------------------------------
    | Kontak, tagline, dan tautan media sosial yang tampil pada bagian footer
    | jika belum disesuaikan superadmin.
    */

    'footer_tagline' => 'Sistem manajemen sekolah Islam terpadu dengan absensi, hafalan Al-Qur\'an, nilai & e-Rapor, serta pembayaran SPP dalam satu platform yang mudah digunakan.',
    'footer_medsos_defaults' => [
        ['icon' => 'instagram', 'label' => 'Instagram', 'url' => 'https://instagram.com/'],
        ['icon' => 'facebook', 'label' => 'Facebook', 'url' => 'https://facebook.com/'],
        ['icon' => 'youtube', 'label' => 'YouTube', 'url' => 'https://youtube.com/'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Section "Paket Harga"
    |--------------------------------------------------------------------------
    | Paket yang tampil di landing pada section harga jika belum disesuaikan
    | superadmin. Field `accent` menentukan gaya kartu: `none` (biasa),
    | `primary` (menonjol), atau `red` (gradasi merah premium).
    */

    'pricing_heading' => 'Paket Harga',
    'pricing_subtitle' => 'Pilih paket yang sesuai dengan kebutuhan sekolah Anda.',
    'pricing_defaults' => [
        [
            'name' => 'Gratis',
            'tagline' => 'Untuk mencoba aplikasi dan absensi dasar.',
            'price' => 'Gratis',
            'period' => '/selamanya',
            'badge' => null,
            'note' => 'Maks. 50 siswa & 10 guru.',
            'cta' => 'Mulai Gratis',
            'accent' => 'none',
            'features' => ['Absensi Kelas (via ponsel)', 'Data Siswa & Guru dasar', 'Laporan Dasar'],
            'excludes' => ['Input Nilai & e-Rapor', 'Hafalan Al-Qur\'an', 'KALDIK & Kalender', 'Gate RFID', 'SPP Online & Notifikasi'],
        ],
        [
            'name' => 'Basic',
            'tagline' => 'Untuk sekolah kecil yang mulai digitalisasi.',
            'price' => 'Rp 150.000',
            'period' => '/bulan',
            'badge' => null,
            'note' => null,
            'cta' => 'Mulai Gratis 14 Hari',
            'accent' => 'none',
            'features' => ['Semua fitur Gratis', 'Absensi Kelas & Mapel', 'Input Nilai & e-Rapor', 'Hafalan Al-Qur\'an', 'KALDIK & Kalender Akademik'],
            'excludes' => ['Gate RFID', 'Pembayaran SPP', 'Notifikasi Orang Tua'],
        ],
        [
            'name' => 'Pro',
            'tagline' => 'Solusi lengkap untuk sekolah Islam modern.',
            'price' => 'Rp 400.000',
            'period' => '/bulan',
            'badge' => 'POPULER',
            'note' => null,
            'cta' => 'Mulai Gratis 14 Hari',
            'accent' => 'primary',
            'features' => ['Semua fitur Basic', 'Gate Attendance RFID', 'Pembayaran SPP Online', 'Notifikasi Real-time ke Orang Tua', 'Alert "Cabut" otomatis', 'Laporan Lengkap & Export', 'Support WhatsApp Prioritas'],
            'excludes' => [],
        ],
        [
            'name' => 'Pro Max',
            'tagline' => 'Untuk sekolah besar & yayasan multi-unit.',
            'price' => 'Rp 900.000',
            'period' => '/bulan',
            'badge' => 'TERLENGKAP',
            'note' => null,
            'cta' => 'Mulai Gratis 14 Hari',
            'accent' => 'red',
            'features' => ['Semua fitur Pro', 'Multi-unit / Kampus sekaligus', 'Kuota siswa & guru tak terbatas', 'Website sekolah & API', 'Onboarding & konsultasi khusus', 'Support prioritas & SLA tinggi'],
            'excludes' => [],
        ],
    ],

    'feature_defaults' => [
        [
            'icon' => 'data-master',
            'title' => 'Data Master Lengkap',
            'desc' => 'Kelola data siswa, guru, kelas, dan plotting mata pelajaran dengan pencatatan identitas yang rapi dan aman.',
        ],
        [
            'icon' => 'rfid',
            'title' => 'Gate Attendance RFID',
            'desc' => 'Pencatatan kehadiran gerbang secara otomatis berbasis RFID dengan log check-in dan check-out per siswa.',
        ],
        [
            'icon' => 'absensi',
            'title' => 'Absensi Mapel Cepat',
            'desc' => 'Absensi berbasis pengecualian: semua siswa default hadir, guru hanya menandai izin, sakit, atau alpa.',
        ],
        [
            'icon' => 'hafalan',
            'title' => 'Hafalan Al-Qur\'an',
            'desc' => 'Mode Tahfidz/Tahsin dengan aktivitas Ziadah & Murajaah, pencatatan surah, rentang ayat, dan nilai kualitas.',
        ],
        [
            'icon' => 'nilai',
            'title' => 'Nilai & e-Rapor',
            'desc' => 'Input nilai harian, tugas, dan ujian hingga perhitungan otomatis e-Rapor Kurikulum Merdeka dan Rapor SIT/Diniyah.',
        ],
        [
            'icon' => 'notif',
            'title' => 'Notifikasi Real-time',
            'desc' => 'Orang tua menerima notifikasi kehadiran dan log hafalan, plus alert "cabut" jika siswa terdeteksi bolos.',
        ],
    ],
];
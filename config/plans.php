<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Paket & Kuota
    |--------------------------------------------------------------------------
    |
    | Sumber kebenaran untuk paket berlangganan. DB hanya menyimpan nama paket
    | yang dipilih sekolah (kolom schools.plan); fitur, kuota, dan harga
    | didefinisikan di sini agar mudah disesuaikan.
    |
    */

    'currency' => 'Rp',

    // Masa tenggang (hari) setelah trial/berlangganan berakhir sebelum
    // sekolah otomatis dinonaktifkan statusnya.
    'grace_days' => 7,

    // Ambang (hari) sebelum trial/tagihan berakhir untuk memunculkan
    // peringatan (banner) kepada admin sekolah.
    'notice_days' => 7,

    'plans' => [
        'free' => [
            'label' => 'Gratis',
            'price' => 0,
            'period' => '/selamanya',
            'tagline' => 'Mulai dari absensi kelas.',
            'quota' => [
                'siswa' => 50,
                'guru' => 10,
                'unit' => 1,
            ],
        ],
        'basic' => [
            'label' => 'Basic',
            'price' => 150000,
            'period' => '/bulan',
            'tagline' => 'Untuk sekolah kecil yang mulai digitalisasi.',
            'quota' => [
                'siswa' => 250,
                'guru' => 30,
                'unit' => 1,
            ],
        ],
        'pro' => [
            'label' => 'Pro',
            'price' => 400000,
            'period' => '/bulan',
            'tagline' => 'Untuk sekolah menengah yang butuh otomasi.',
            'quota' => [
                'siswa' => 1000,
                'guru' => 100,
                'unit' => 1,
            ],
        ],
        'pro-max' => [
            'label' => 'Pro Max',
            'price' => 900000,
            'period' => '/bulan',
            'tagline' => 'Untuk yayasan besar & multi-unit.',
            'quota' => [
                'siswa' => null,
                'guru' => null,
                'unit' => null,
            ],
        ],
    ],

    // Urutan tier dari yang paling rendah. Fitur bersifat kumulatif.
    'order' => ['free', 'basic', 'pro', 'pro-max'],

    'features' => [
        'absensi' => [
            'label' => 'Absensi Kelas (via ponsel)',
            'description' => 'Rekap kehadiran siswa harian, termasuk absensi per kelas dan per mapel.',
            'from' => 'free',
        ],
        'data' => [
            'label' => 'Data Siswa & Guru',
            'description' => 'Master data siswa, guru, dan kelas.',
            'from' => 'free',
        ],
        'laporan' => [
            'label' => 'Laporan Dasar',
            'description' => 'Ringkasan kehadiran dan data dasar.',
            'from' => 'free',
        ],
        'nilai' => [
            'label' => 'Input Nilai & e-Rapor',
            'description' => 'Input, rekap, dan laporan nilai siswa (e-Rapor).',
            'from' => 'basic',
        ],
        'hafalan' => [
            'label' => 'Manajemen Hafalan Al-Qur\'an',
            'description' => 'Target setoran, tilawah, dan progress hafalan siswa.',
            'from' => 'basic',
        ],
        'kaldik' => [
            'label' => 'KALDIK & Kalender Pendidikan',
            'description' => 'Kalender akademik, roster, dan perangkat ajar.',
            'from' => 'basic',
        ],
        'rfid' => [
            'label' => 'Gate Attendance RFID',
            'description' => 'Absensi otomatis dengan kartu RFID.',
            'from' => 'pro',
        ],
        'spp' => [
            'label' => 'SPP Online & Notifikasi',
            'description' => 'Pembayaran SPP dan notifikasi real-time ke orang tua.',
            'from' => 'pro',
        ],
        'laporan_lengkap' => [
            'label' => 'Laporan Lengkap & Dashboard Lanjutan',
            'description' => 'Analitik, export, dan laporan untuk manajemen sekolah.',
            'from' => 'pro',
        ],
        'multi_unit' => [
            'label' => 'Multi Unit / Kampus',
            'description' => 'Kelola beberapa unit sekolah dalam satu akun.',
            'from' => 'pro-max',
        ],
        'website_api' => [
            'label' => 'Website Sekolah & API',
            'description' => 'Website sekolah dan akses data via API.',
            'from' => 'pro-max',
        ],
        'support' => [
            'label' => 'Support Prioritas',
            'description' => 'Dukungan teknis prioritas (SLA tinggi).',
            'from' => 'pro-max',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pemetaan Rute -> Fitur
    |--------------------------------------------------------------------------
    |
    | Dipakai untuk gating otomatis (backend keras) dan filter menu sidebar.
    | Setiap prefix nama rute yang cocok akan dikunci sesuai fitur paket.
    |
    */
    'feature_routes' => [
        'nilai' => ['admin.grade-types', 'admin.rekap', 'guru.nilai', 'guru.rapor', 'ortu.nilai', 'wakasek.rapor-design'],
        'hafalan' => [
            'admin.quran',
            'admin.reading-levels',
            'admin.tahfidz',
            'guru.tahfidz',
            'guru.quran',
            'murid.hafalan',
            'ortu.hafalan',
            'kepsek.tahfidz',
            'kepsek.quran-assignment',
            'wakasek.quran-assignment',
        ],
        'kaldik' => [
            'admin.academic-years',
            'guru.kaldik',
            'wakasek.kaldik',
            'wakasek.base.prota',
            'wakasek.base.prosem',
            'wakasek.base.capaian-pembelajaran',
            'wakasek.base.atp',
            'wakasek.base.modul-ajar',
            'wakasek.base.roster',
            'ortu.kaldik',
            'kepsek.kaldik',
        ],
        'rfid' => [],
        'spp' => [],
        'multi_unit' => [],
        'website_api' => [],
    ],
];
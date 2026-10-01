<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Database\Seeder;

class BlogPostSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@sit.sch.id')->first();
        if (! $admin) return;

        $posts = [
            [
                'title' => 'Cara Efektif Mengelola Absensi Siswa di Sekolah Islam',
                'slug' => 'cara-efektif-mengelola-absensi-siswa',
                'excerpt' => 'Pencatatan kehadiran siswa yang akurat merupakan fondasi penting dalam manajemen sekolah. Berikut panduan praktis untuk guru wali kelas.',
                'body' => "Absensi adalah salah satu aspek terpenting dalam operasional sekolah. Dengan sistem digital seperti SIT School Management, guru wali kelas dapat mencatadat kehadiran siswa secara cepat dan akurat.\n\nBerikut adalah langkah-langkah efektif dalam mengelola absensi:\n\n1. **Gunakan Fitur Absensi Kelas** — Guru wali kelas bisa melakukan presensi langsung dari dashboard. Pilih kelas, pilih tanggal, lalu tandai status kehadiran setiap siswa.\n\n2. **Manfaatkan Rekap Otomatis** — Sistem secara otomatis menghitung persentase kehadiran per siswa, per kelas, dan per bulan. Tidak perlu lagi menghitung manual.\n\n3. **Kirim Laporan ke Orang Tua** — Fitur portal orang tua memungkinkan ortu melihat data kehadiran anak mereka secara real-time.\n\n4. **Identifikasi Siswa Bermasalah** — Dengan grafik kehadiran, guru dapat dengan mudah melihat siswa yang memiliki tingkat kehadiran rendah dan perlu ditindaklanjuti.\n\nDengan pendekatan digital, administrasi kehadiran menjadi lebih ringkas, transparan, dan akurat.",
                'category' => 'tips',
                'is_published' => true,
                'published_at' => now()->subDays(3),
                'views' => 142,
            ],
            [
                'title' => 'Panduan Lengkap Sistem Tahfidz Al-Qur\'an di Sekolah',
                'slug' => 'panduan-lengkap-sistem-tahfidz',
                'excerpt' => 'Bagaimana mengelola program hafalan Al-Qur\'an secara digital — dari penugasan guru, pencatatan ziadah, hingga rekap otomatis.',
                'body' => "Program Tahfidz Al-Qur\'an merupakan bagian integral dari kurikulum Sekolah Islam Terpadu. Dengan SIT School Management, pengelolaan program ini menjadi jauh lebih mudah.\n\n**Fitur Utama Sistem Tahfidz:**\n\n1. **Penugasan Guru Tahfidz** — Admin dapat menugaskan guru ke kelas tertentu dengan role \"PJ Tahfidz\". Guru yang ditugaskan akan melihat siswa-siswa yang menjadi tanggung jawabnya.\n\n2. **Input Hafalan** — Guru dapat mencatat ziadah (hafalan baru) dan murajaah (mengulang) langsung dari kelas. Setiap input mencakup surat, ayat, dan skor.\n\n3. **Auto-Murajaah** — Sistem otomatis mendeteksi ayat yang perlu dimurajaahkan berdasarkan jarak hafalan terakhir.\n\n4. **Rekap & Monitoring** — Admin dan guru dapat melihat grafik progres hafalan per siswa, per kelas, atau keseluruhan.\n\n5. **Portal Orang Tua** — Orang tua dapat melihat progres hafalan anak mereka melalui portal khusus.\n\nDengan sistem digital ini, program Tahfidz menjadi lebih terukur, transparan, dan akuntabel.",
                'category' => 'tutorial',
                'is_published' => true,
                'published_at' => now()->subDays(7),
                'views' => 238,
            ],
            [
                'title' => 'Mengenal SIT School Management: Solusi Digital untuk Sekolah Islam',
                'slug' => 'mengenal-sit-school-management',
                'excerpt' => 'SIT School Management adalah platform manajemen sekolah all-in-one yang dirancang khusus untuk kebutuhan Sekolah Islam Terpadu di Indonesia.',
                'body' => "SIT School Management hadir sebagai jawaban atas kebutuhan digitalisasi administrasi sekolah Islam di Indonesia.\n\n**Apa itu SIT School Management?**\n\nSIT School Management adalah platform berbasis web yang mengintegrasikan seluruh operasional sekolah dalam satu aplikasi — mulai dari data master siswa dan guru, absensi pembelajaran, program Tahfidz Al-Qur\'an, sistem penilaian, hingga pembayaran SPP.\n\n**Mengapa Sekolah Islam Membutuhkan Platform Ini?**\n\n- **Multi-tenant & Aman** — Setiap sekolah memiliki data terisolasi. Enkripsi SSL dan backup otomatis menjamin keamanan data.\n\n- **Role-based Access** — Sistem mendukung 9 role pengguna: Superadmin, Admin, Wakil Kurikulum, Kepala Sekolah, Guru, Manajemen, Keuangan, Orang Tua, dan Staff.\n\n- **Fokus pada Al-Qur\'an** — Fitur khusus Tahfidz dengan skor, ziadah, murajaah, dan monitoring progres.\n\n- **Harga Terjangkau** — Mulai dari Rp 150.000/bulan untuk paket Basic hingga Rp 400.000/bulan untuk paket Pro.\n\n- **Setup Cepat** — Import data siswa dan guru dari Excel, selesai dalam 5-30 menit.\n\nJika sekolah Anda sedang mencari solusi digital yang terintegrasi dan mudah digunakan, SIT School Management bisa menjadi pilihan yang tepat.",
                'category' => 'berita',
                'is_published' => true,
                'published_at' => now()->subDays(14),
                'views' => 387,
            ],
        ];

        foreach ($posts as $post) {
            BlogPost::create(array_merge($post, [
                'author_id' => $admin->id,
                'school_id' => $admin->school_id,
            ]));
        }
    }
}

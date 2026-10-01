<?php

namespace Tests\Feature;

use App\Models\PlatformSetting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PlatformContentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'mysql']);
        config(['database.connections.mysql.host' => '127.0.0.1']);
        config(['database.connections.mysql.port' => '3306']);
        config(['database.connections.mysql.database' => 'sit_school']);
        config(['database.connections.mysql.username' => 'root']);
        config(['database.connections.mysql.password' => '']);
        PlatformSetting::forget();
    }

    protected function tearDown(): void
    {
        PlatformSetting::query()->where('id', 1)->update([
            'hero_badge' => 'Solusi Digital untuk Sekolah Islam',
            'hero_title' => 'Manajemen Sekolah Islam Terpadu',
            'hero_title_highlight' => 'dalam Satu Platform',
            'hero_desc' => 'Kelola kehadiran, absensi pembelajaran, hafalan Al-Qur\'an, nilai & e-Rapor, serta pembayaran SPP secara terpadu untuk guru, siswa, orang tua, dan manajemen sekolah.',
            'hero_microcopy' => 'Tanpa kartu kredit · Setup 5 menit · Support WhatsApp',
            'hero_cta_guest' => 'Mulai Gratis 14 Hari',
            'hero_cta_features' => 'Lihat Fitur',
            'hero_cta_auth' => 'Buka Dashboard',
            'fitur_heading' => 'Fitur Utama',
            'fitur_subtitle' => 'Semua kebutuhan administrasi sekolah modern dalam satu aplikasi yang mudah digunakan.',
            'features' => json_encode(config('platform.feature_defaults')),
            'tampilan_heading' => 'Tampilan Aplikasi',
            'tampilan_subtitle' => 'Antarmuka yang intuitif untuk setiap peran pengguna.',
            'tampilan_shots' => json_encode(config('platform.tampilan_defaults')),
            'peran_heading' => 'Dibuat untuk Setiap Peran',
            'peran_subtitle' => 'Setiap peran memiliki dashboard dan akses yang disesuaikan dengan tugasnya.',
            'peran_roles' => json_encode(config('platform.peran_defaults')),
            'show_hero' => 1,
            'show_fitur' => 1,
            'show_tampilan' => 1,
            'show_peran' => 1,
            'show_sekolah' => 1,
            'sekolah_heading' => 'Sekolah yang Sudah Menggunakan',
            'sekolah_subtitle' => 'Bergabung bersama sekolah-sekolah Islam terbaik di Indonesia.',
            'testi_heading' => 'Apa Kata Mereka?',
            'testi_subtitle' => 'Testimoni dari admin sekolah yang sudah menggunakan aplikasi ini.',
            'testimonials' => json_encode(config('platform.testimonial_defaults')),
            'show_footer' => 1,
            'footer_tagline' => config('platform.footer_tagline'),
            'footer_address' => null,
            'footer_phone' => null,
            'footer_email' => null,
            'footer_copyright' => null,
            'footer_medsos' => json_encode(config('platform.footer_medsos_defaults')),
            'show_pricing' => 1,
            'pricing_heading' => config('platform.pricing_heading'),
            'pricing_subtitle' => config('platform.pricing_subtitle'),
            'pricing_plans' => json_encode(config('platform.pricing_defaults')),
        ]);
        PlatformSetting::forget();
        parent::tearDown();
    }

    private function superadmin(): User
    {
        return User::where('email', 'superadmin@platform.id')->firstOrFail();
    }

    public function test_superadmin_can_view_hero_page(): void
    {
        $this->actingAs($this->superadmin());

        $resp = $this->get(route('platform.content.hero'));
        $resp->assertOk();
        $resp->assertSee('Hero', false);
        $resp->assertSee('Simpan Hero', false);
    }

    public function test_superadmin_can_view_features_page(): void
    {
        $this->actingAs($this->superadmin());

        $resp = $this->get(route('platform.content.features'));
        $resp->assertOk();
        $resp->assertSee('Fitur Utama', false);
        $resp->assertSee('Tambah Fitur', false);
        $resp->assertSee('Simpan Fitur', false);
    }

    public function test_superadmin_can_view_tampilan_page(): void
    {
        $this->actingAs($this->superadmin());

        $resp = $this->get(route('platform.content.tampilan'));
        $resp->assertOk();
        $resp->assertSee('Tampilan Aplikasi', false);
        $resp->assertSee('Label (judul bar)', false);
        $resp->assertSee('Tambah Kartu', false);
        $resp->assertSee('Hapus kartu', false);
        $resp->assertSee('Gambar (screenshot)', false);
        $resp->assertSee('Klik untuk upload', false);
        $resp->assertSee('Simpan Tampilan', false);
    }

    public function test_superadmin_can_update_hero(): void
    {
        $this->actingAs($this->superadmin());

        $resp = $this->put(route('platform.content.hero.update'), [
            'hero_badge' => 'Digitalisasi Madrasah & Sekolah',
            'hero_title' => 'Kelola Sekolah Anda',
            'hero_title_highlight' => 'dengan Mudah',
            'hero_desc' => 'Deskripsi baru untuk uji coba.',
            'hero_microcopy' => 'Gratis · Cepat · Mudah',
            'hero_cta_guest' => 'Coba Sekarang',
            'hero_cta_features' => 'Jelajahi Fitur',
            'hero_cta_auth' => 'Masuk Panel',
        ]);
        $resp->assertRedirect(route('platform.content.hero'));
        $resp->assertSessionHas('status');

        $row = PlatformSetting::query()->find(1);
        $this->assertSame('Digitalisasi Madrasah & Sekolah', $row->hero_badge);
        $this->assertSame('Kelola Sekolah Anda', $row->hero_title);
        $this->assertSame('dengan Mudah', $row->hero_title_highlight);
        $this->assertSame('Coba Sekarang', $row->hero_cta_guest);
    }

    public function test_superadmin_can_update_features(): void
    {
        $this->actingAs($this->superadmin());

        $resp = $this->put(route('platform.content.features.update'), [
            'fitur_heading' => 'Keunggulan Platform',
            'fitur_subtitle' => 'Bekerja untuk semua peran sekolah.',
            'features' => [
                ['icon' => 'absensi', 'title' => 'Absensi Cepat', 'desc' => 'Pencatatan hadir otomatis.'],
                ['icon' => 'spp', 'title' => 'Pembayaran SPP', 'desc' => 'Pantau tagihan & pembayaran.'],
            ],
        ]);
        $resp->assertRedirect(route('platform.content.features'));
        $resp->assertSessionHas('status');

        $row = PlatformSetting::query()->find(1);
        $this->assertSame('Keunggulan Platform', $row->fitur_heading);
        $this->assertSame('Pembayaran SPP', json_decode($row->features, true)[1]['title']);
    }

    public function test_features_update_rejects_invalid_icon(): void
    {
        $this->actingAs($this->superadmin());

        $resp = $this->put(route('platform.content.features.update'), [
            'features' => [
                ['icon' => 'bogus', 'title' => 'Absensi Cepat', 'desc' => 'Pencatatan hadir otomatis.'],
            ],
        ]);
        $resp->assertSessionHasErrors('features.0.icon');

        $this->assertNotSame('Absensi Cepat', json_decode(PlatformSetting::query()->find(1)->features, true)[0]['title'] ?? '');
    }

    public function test_custom_hero_and_features_render_on_landing(): void
    {
        PlatformSetting::query()->where('id', 1)->update([
            'hero_title' => 'Kelola Sekolah Anda',
            'hero_badge' => 'Digitalisasi Madrasah & Sekolah',
            'fitur_heading' => 'Keunggulan Platform',
            'features' => json_encode([
                ['icon' => 'absensi', 'title' => 'Absensi Cepat', 'desc' => 'Pencatatan hadir otomatis.'],
            ]),
        ]);
        PlatformSetting::forget();

        $this->get('/')->assertOk()
            ->assertSee('Kelola Sekolah Anda')
            ->assertSee('Digitalisasi Madrasah & Sekolah')
            ->assertSee('Keunggulan Platform')
            ->assertSee('Absensi Cepat');
    }

    public function test_superadmin_can_update_tampilan_section(): void
    {
        Storage::fake('public');
        $this->actingAs($this->superadmin());

        $resp = $this->put(route('platform.content.tampilan.update'), [
            'tampilan_heading' => 'Galeri Aplikasi',
            'tampilan_subtitle' => 'Lihat bagaimana aplikasi terlihat pada setiap peran.',
            'tampilan_shots' => [
                ['icon' => 'users', 'label' => 'Panel Admin', 'caption' => 'Dashboard Pimpinan', 'image' => UploadedFile::fake()->image('admin.png', 800, 400)],
                ['icon' => 'nilai', 'label' => 'Panel Guru', 'caption' => 'Dashboard Pengajar', 'image' => UploadedFile::fake()->image('guru.png', 800, 400)],
                ['icon' => 'absensi', 'label' => 'Absensi Kelas', 'caption' => 'Absensi Per Pertemuan'],
                ['icon' => 'hafalan', 'label' => 'Hafalan Siswa', 'caption' => 'Log Ziadah & Murajaah'],
            ],
        ]);
        $resp->assertRedirect(route('platform.content.tampilan'));
        $resp->assertSessionHas('status');

        $row = PlatformSetting::query()->find(1);
        $shots = json_decode($row->tampilan_shots, true);
        $this->assertSame('Galeri Aplikasi', $row->tampilan_heading);
        $this->assertSame('Dashboard Pimpinan', $shots[0]['caption']);
        $this->assertSame('users', $shots[0]['icon']);
        $this->assertNotNull($shots[0]['image']);
        $this->assertStringStartsWith('platform/shots/', $shots[0]['image']);
        Storage::disk('public')->assertExists($shots[0]['image']);
        $this->assertSame('Dashboard Pengajar', $shots[1]['caption']);
        $this->assertStringStartsWith('platform/shots/', $shots[1]['image']);
        $this->assertNull($shots[2]['image']);
    }

    public function test_superadmin_can_remove_tampilan_image(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('platform/shots/old-admin.png', 'x');
        PlatformSetting::query()->where('id', 1)->update([
            'tampilan_shots' => json_encode([
                ['icon' => 'data-master', 'label' => 'Panel Admin', 'caption' => 'Dashboard Pimpinan', 'image' => 'platform/shots/old-admin.png'],
            ]),
        ]);
        PlatformSetting::forget();
        $this->actingAs($this->superadmin());

        $resp = $this->put(route('platform.content.tampilan.update'), [
            'tampilan_shots' => [
                ['icon' => 'data-master', 'label' => 'Panel Admin', 'caption' => 'Dashboard Pimpinan', 'remove_image' => '1'],
            ],
        ]);
        $resp->assertRedirect(route('platform.content.tampilan'));

        $shots = json_decode(PlatformSetting::query()->find(1)->tampilan_shots, true);
        $this->assertNull($shots[0]['image']);
        Storage::disk('public')->assertMissing('platform/shots/old-admin.png');
    }

    public function test_tampilan_update_rejects_invalid_icon(): void
    {
        $this->actingAs($this->superadmin());

        $resp = $this->put(route('platform.content.tampilan.update'), [
            'tampilan_shots' => [
                ['icon' => 'bogus', 'label' => 'Panel Admin', 'caption' => 'Dashboard Pimpinan'],
            ],
        ]);
        $resp->assertSessionHasErrors('tampilan_shots.0.icon');
    }

    public function test_tampilan_update_rejects_non_image_file(): void
    {
        $this->actingAs($this->superadmin());

        $resp = $this->put(route('platform.content.tampilan.update'), [
            'tampilan_shots' => [
                ['icon' => 'data-master', 'label' => 'Panel Admin', 'caption' => 'Dashboard Pimpinan', 'image' => UploadedFile::fake()->create('doc.pdf', 100)],
            ],
        ]);
        $resp->assertSessionHasErrors('tampilan_shots.0.image');
    }

    public function test_custom_tampilan_renders_on_landing(): void
    {
        PlatformSetting::query()->where('id', 1)->update([
            'tampilan_heading' => 'Galeri Aplikasi',
            'tampilan_subtitle' => 'Lihat bagaimana aplikasi terlihat pada setiap peran.',
            'tampilan_shots' => json_encode([
                ['icon' => 'users', 'label' => 'Panel Admin', 'caption' => 'Dashboard Pimpinan', 'image' => 'platform/shots/admin.png'],
                ['icon' => 'nilai', 'label' => 'Panel Guru', 'caption' => 'Dashboard Pengajar'],
            ]),
        ]);
        PlatformSetting::forget();

        $this->get('/')->assertOk()
            ->assertSee('Galeri Aplikasi')
            ->assertSee('Lihat bagaimana aplikasi terlihat pada setiap peran.')
            ->assertSee('Panel Admin')
            ->assertSee('Dashboard Pimpinan')
            ->assertSee('storage/platform/shots/admin.png')
            ->assertSee('Panel Guru')
            ->assertSee('Dashboard Pengajar')
            ->assertSee('Belum ada screenshot');
    }

    public function test_superadmin_can_view_peran_page(): void
    {
        $this->actingAs($this->superadmin());

        $resp = $this->get(route('platform.content.peran'));
        $resp->assertOk();
        $resp->assertSee('Dibuat untuk Setiap Peran', false);
        $resp->assertSee('Simpan Peran', false);
        $resp->assertSee('Admin Sekolah', false);
        $resp->assertSee('Wakil Kepala Kurikulum', false);
    }

    public function test_superadmin_can_update_peran_section(): void
    {
        $this->actingAs($this->superadmin());

        $resp = $this->put(route('platform.content.peran.update'), [
            'peran_heading' => 'Dibuat untuk Setiap Peran',
            'peran_subtitle' => 'Setiap peran memiliki dashboard dan akses yang disesuaikan dengan tugasnya.',
            'peran_roles' => [
                ['icon' => 'data-master', 'title' => 'Super Admin', 'desc' => 'Kelola seluruh data sekolah.'],
                ['icon' => 'users', 'title' => 'Orang Tua', 'desc' => 'Pantau perkembangan anak.'],
            ],
        ]);
        $resp->assertRedirect(route('platform.content.peran'));
        $resp->assertSessionHas('status');

        $row = PlatformSetting::query()->find(1);
        $roles = json_decode($row->peran_roles, true);
        $this->assertSame('Dibuat untuk Setiap Peran', $row->peran_heading);
        $this->assertCount(2, $roles);
        $this->assertSame('Super Admin', $roles[0]['title']);
        $this->assertSame('data-master', $roles[0]['icon']);
        $this->assertSame('Orang Tua', $roles[1]['title']);
    }

    public function test_peran_update_rejects_invalid_icon(): void
    {
        $this->actingAs($this->superadmin());

        $resp = $this->put(route('platform.content.peran.update'), [
            'peran_roles' => [
                ['icon' => 'bogus', 'title' => 'Admin', 'desc' => 'Deskripsi admin.'],
            ],
        ]);
        $resp->assertSessionHasErrors('peran_roles.0.icon');
    }

    public function test_custom_peran_renders_on_landing(): void
    {
        PlatformSetting::query()->where('id', 1)->update([
            'peran_heading' => 'Dibuat untuk Setiap Peran',
            'peran_subtitle' => 'Setiap peran memiliki dashboard dan akses yang disesuaikan dengan tugasnya.',
            'peran_roles' => json_encode([
                ['icon' => 'users', 'title' => 'Orang Tua', 'desc' => 'Pantau kehadiran, log hafalan, nilai, dan status SPP anak.'],
            ]),
        ]);
        PlatformSetting::forget();

        $this->get('/')->assertOk()
            ->assertSee('Dibuat untuk Setiap Peran')
            ->assertSee('Setiap peran memiliki dashboard dan akses yang disesuaikan dengan tugasnya.')
            ->assertSee('Orang Tua')
            ->assertSee('Pantau kehadiran, log hafalan, nilai, dan status SPP anak.');
    }

    public function test_superadmin_can_view_sekolah_page(): void
    {
        $this->actingAs($this->superadmin());

        $resp = $this->get(route('platform.content.sekolah'));
        $resp->assertOk();
        $resp->assertSee('Sekolah & Testimoni', false);
        $resp->assertSee('Tampilkan di landing page', false);
        $resp->assertSee('Tambah Testimoni', false);
        $resp->assertSee('Ust. Abdullah', false);
    }

    public function test_superadmin_can_update_sekolah_section(): void
    {
        $this->actingAs($this->superadmin());

        $resp = $this->put(route('platform.content.sekolah.update'), [
            'sekolah_heading' => 'Sekolah yang Sudah Menggunakan',
            'sekolah_subtitle' => 'Bergabung bersama sekolah-sekolah Islam terbaik di Indonesia.',
            'testi_heading' => 'Kata Mereka',
            'testi_subtitle' => 'Ulasan dari para admin sekolah.',
            'testimonials' => [
                ['quote' => 'Aplikasi sangat membantu pekerjaan administrasi kami.', 'author' => 'Ust. Ahmad', 'role' => 'Admin · SIT Baru', 'initials' => 'UA'],
            ],
            'show_sekolah' => '1',
        ]);
        $resp->assertRedirect(route('platform.content.sekolah'));
        $resp->assertSessionHas('status');

        $row = PlatformSetting::query()->find(1);
        $testimonials = json_decode($row->testimonials, true);
        $this->assertSame('Kata Mereka', $row->testi_heading);
        $this->assertCount(1, $testimonials);
        $this->assertSame('Ust. Ahmad', $testimonials[0]['author']);
        $this->assertSame('UA', $testimonials[0]['initials']);
        $this->assertSame(1, (int) $row->show_sekolah);
    }

    public function test_superadmin_can_hide_hero_section(): void
    {
        $this->actingAs($this->superadmin());

        $resp = $this->put(route('platform.content.hero.update'), [
            'hero_badge' => 'Hero-For-Hide-Test',
            'show_hero' => '0',
        ]);
        $resp->assertRedirect(route('platform.content.hero'));

        $this->assertSame(0, (int) PlatformSetting::query()->find(1)->show_hero);
        PlatformSetting::forget();

        $this->get('/')->assertOk()
            ->assertDontSee('Hero-For-Hide-Test');
    }

    public function test_hidden_sections_are_not_rendered_on_landing(): void
    {
        PlatformSetting::query()->where('id', 1)->update([
            'show_fitur' => 0,
            'show_peran' => 0,
            'show_sekolah' => 0,
        ]);
        PlatformSetting::forget();

        $this->get('/')->assertOk()
            ->assertDontSee('Fitur Utama')
            ->assertDontSee('Dibuat untuk Setiap Peran')
            ->assertDontSee('Sekolah yang Sudah Menggunakan')
            ->assertDontSee('Apa Kata Mereka?');
    }

    public function test_superadmin_can_view_footer_page(): void
    {
        $this->actingAs($this->superadmin());

        $resp = $this->get(route('platform.content.footer'));
        $resp->assertOk();
        $resp->assertSee('Footer', false);
        $resp->assertSee('Tampilkan di landing page', false);
        $resp->assertSee('Media Sosial', false);
        $resp->assertSee('Tambah Media Sosial', false);
        $resp->assertSee('Instagram', false);
    }

    public function test_superadmin_can_update_footer_section(): void
    {
        $this->actingAs($this->superadmin());

        $resp = $this->put(route('platform.content.footer.update'), [
            'footer_tagline' => 'Tagline baru untuk tes footer.',
            'footer_address' => 'Jl. Merdeka No. 1, Jakarta',
            'footer_phone' => '0812-3456-7890',
            'footer_email' => 'info@sit.sch.id',
            'footer_copyright' => 'Copyright 2026 SIT School',
            'footer_medsos' => [
                ['icon' => 'instagram', 'label' => 'Instagram', 'url' => 'https://instagram.com/sitschool'],
                ['icon' => 'whatsapp', 'label' => 'WhatsApp', 'url' => 'https://wa.me/6281234567890'],
            ],
            'show_footer' => '1',
        ]);
        $resp->assertRedirect(route('platform.content.footer'));
        $resp->assertSessionHas('status');

        $row = PlatformSetting::query()->find(1);
        $medsos = json_decode($row->footer_medsos, true);
        $this->assertSame(1, (int) $row->show_footer);
        $this->assertSame('Tagline baru untuk tes footer.', $row->footer_tagline);
        $this->assertSame('info@sit.sch.id', $row->footer_email);
        $this->assertSame('Copyright 2026 SIT School', $row->footer_copyright);
        $this->assertCount(2, $medsos);
        $this->assertSame('whatsapp', $medsos[1]['icon']);
        $this->assertSame('WhatsApp', $medsos[1]['label']);
        $this->assertSame('https://wa.me/6281234567890', $medsos[1]['url']);
    }

    public function test_superadmin_can_hide_footer_section(): void
    {
        $this->actingAs($this->superadmin());

        $resp = $this->put(route('platform.content.footer.update'), [
            'footer_tagline' => 'Footer-Hidden-For-Test',
            'show_footer' => '0',
        ]);
        $resp->assertRedirect(route('platform.content.footer'));

        $this->assertSame(0, (int) PlatformSetting::query()->find(1)->show_footer);
        PlatformSetting::forget();

        $this->get('/')->assertOk()
            ->assertDontSee('Footer-Hidden-For-Test');
    }

    public function test_footer_renders_social_media_on_landing(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('Navigasi', false)
            ->assertSee('Kontak', false)
            ->assertSee('Instagram', false)
            ->assertSee('Facebook', false)
            ->assertSee('YouTube', false)
            ->assertSee('Hak cipta dilindungi.');
    }

    public function test_superadmin_can_view_pricing_page(): void
    {
        $this->actingAs($this->superadmin());

        $resp = $this->get(route('platform.content.pricing'));
        $resp->assertOk();
        $resp->assertSee('Paket Harga', false);
        $resp->assertSee('Tampilkan di landing page', false);
        $resp->assertSee('Daftar Paket', false);
        $resp->assertSee('Tambah Paket', false);
        $resp->assertSee('Pro Max', false);
    }

    public function test_superadmin_can_update_pricing_section(): void
    {
        $this->actingAs($this->superadmin());

        $resp = $this->put(route('platform.content.pricing.update'), [
            'pricing_heading' => 'Pilihan Paket',
            'pricing_subtitle' => 'Temukan paket yang paling sesuai untuk sekolah Anda.',
            'plans' => [
                [
                    'name' => 'Gratis',
                    'tagline' => 'Coba absensi dasar.',
                    'price' => 'Gratis',
                    'period' => '/selamanya',
                    'badge' => '',
                    'note' => 'Maks. 20 siswa.',
                    'cta' => 'Mulai Gratis',
                    'accent' => 'none',
                    'features_lines' => "Absensi Kelas (via ponsel)\nData Siswa dasar",
                    'excludes_lines' => "Gate RFID\nPembayaran SPP",
                ],
                [
                    'name' => 'Pro Max',
                    'tagline' => 'Untuk yayasan besar.',
                    'price' => 'Rp 900.000',
                    'period' => '/bulan',
                    'badge' => 'TERLENGKAP',
                    'note' => '',
                    'cta' => 'Hubungi Kami',
                    'accent' => 'red',
                    'features_lines' => 'Multi-unit / Kampus',
                    'excludes_lines' => '',
                ],
            ],
            'show_pricing' => '1',
        ]);
        $resp->assertRedirect(route('platform.content.pricing'));
        $resp->assertSessionHas('status');

        $row = PlatformSetting::query()->find(1);
        $plans = json_decode($row->pricing_plans, true);
        $this->assertSame('Pilihan Paket', $row->pricing_heading);
        $this->assertSame(1, (int) $row->show_pricing);
        $this->assertCount(2, $plans);
        $this->assertSame('Gratis', $plans[0]['name']);
        $this->assertSame(['Absensi Kelas (via ponsel)', 'Data Siswa dasar'], $plans[0]['features']);
        $this->assertSame(['Gate RFID', 'Pembayaran SPP'], $plans[0]['excludes']);
        $this->assertSame('Maks. 20 siswa.', $plans[0]['note']);
        $this->assertSame('red', $plans[1]['accent']);
        $this->assertSame(['Multi-unit / Kampus'], $plans[1]['features']);
        $this->assertSame([], $plans[1]['excludes']);
        $this->assertSame('Hubungi Kami', $plans[1]['cta']);
    }

    public function test_pricing_update_rejects_invalid_accent(): void
    {
        $this->actingAs($this->superadmin());

        $resp = $this->put(route('platform.content.pricing.update'), [
            'plans' => [
                [
                    'name' => 'Pro',
                    'tagline' => 'test',
                    'price' => 'Rp 400.000',
                    'period' => '/bulan',
                    'badge' => '',
                    'note' => '',
                    'cta' => 'Mulai',
                    'accent' => 'bogus',
                    'features_lines' => 'Fitur A',
                ],
            ],
        ]);
        $resp->assertSessionHasErrors('plans.0.accent');
    }

    public function test_custom_pricing_renders_on_landing(): void
    {
        PlatformSetting::query()->where('id', 1)->update([
            'pricing_heading' => 'Pilihan Paket',
            'pricing_subtitle' => 'Temukan paket yang paling sesuai untuk sekolah Anda.',
            'pricing_plans' => json_encode([
                [
                    'name' => 'Pro Max',
                    'tagline' => 'Untuk yayasan besar.',
                    'price' => 'Rp 999.999',
                    'period' => '/bulan',
                    'badge' => 'TERLENGKAP',
                    'note' => 'Kuota tak terbatas.',
                    'cta' => 'Hubungi Kami',
                    'accent' => 'red',
                    'features' => ['Multi-unit / Kampus'],
                    'excludes' => ['Tidak ada limit fitur'],
                ],
            ]),
        ]);
        PlatformSetting::forget();

        $this->get('/')->assertOk()
            ->assertSee('Pilihan Paket')
            ->assertSee('Temukan paket yang paling sesuai untuk sekolah Anda.')
            ->assertSee('Pro Max')
            ->assertSee('Rp 999.999')
            ->assertSee('TERLENGKAP')
            ->assertSee('Multi-unit / Kampus')
            ->assertSee('Tidak ada limit fitur')
            ->assertSee('bg-gradient-to-br from-primary to-primary-dark', false)
            ->assertSee('Hubungi Kami');
    }

    public function test_superadmin_can_hide_pricing_section(): void
    {
        $this->actingAs($this->superadmin());

        $resp = $this->put(route('platform.content.pricing.update'), [
            'pricing_heading' => 'Pricing-Hidden-For-Test',
            'show_pricing' => '0',
        ]);
        $resp->assertRedirect(route('platform.content.pricing'));

        $this->assertSame(0, (int) PlatformSetting::query()->find(1)->show_pricing);
        PlatformSetting::forget();

        $this->get('/')->assertOk()
            ->assertDontSee('Pricing-Hidden-For-Test');
    }
}
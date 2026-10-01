<?php

namespace Tests\Feature;

use App\Models\PlatformSetting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PlatformSettingsTest extends TestCase
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
            'app_name' => 'SIT School',
            'tagline' => 'Sekolah Islam Terpadu',
            'logo' => null,
            'primary_color' => '#059669',
            'secondary_color' => '#0d9488',
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

    public function test_superadmin_can_view_platform_settings(): void
    {
        $this->actingAs($this->superadmin());

        $resp = $this->get(route('platform.settings.index'));
        $resp->assertOk();
        $resp->assertSee('Pengaturan Platform', false);
        $resp->assertSee('Nama Aplikasi', false);
        $resp->assertSee('Warna Primer', false);
    }

    public function test_superadmin_can_update_name_and_colors(): void
    {
        $this->actingAs($this->superadmin());

        $resp = $this->put(route('platform.settings.update'), [
            'app_name' => 'SDIT Harapan Umat',
            'tagline' => 'Sekolah Islam Terpadu Harapan',
            'primary_color' => '#2563eb',
            'secondary_color' => '#7c3aed',
        ]);
        $resp->assertRedirect(route('platform.settings.index'));
        $resp->assertSessionHas('status');

        $row = PlatformSetting::query()->find(1);
        $this->assertSame('SDIT Harapan Umat', $row->app_name);
        $this->assertSame('Sekolah Islam Terpadu Harapan', $row->tagline);
        $this->assertSame('#2563eb', $row->primary_color);
        $this->assertSame('#7c3aed', $row->secondary_color);
    }

    public function test_update_rejects_invalid_color(): void
    {
        $this->actingAs($this->superadmin());

        $resp = $this->put(route('platform.settings.update'), [
            'app_name' => 'Nama Baru',
            'primary_color' => 'red',
            'secondary_color' => '#7c3aed',
        ]);
        $resp->assertSessionHasErrors('primary_color');

        $this->assertNotSame('Nama Baru', PlatformSetting::query()->find(1)->app_name);
    }

    public function test_superadmin_can_upload_logo(): void
    {
        $this->actingAs($this->superadmin());

        $resp = $this->put(route('platform.settings.update'), [
            'app_name' => 'SIT School',
            'primary_color' => '#059669',
            'secondary_color' => '#0d9488',
            'logo' => UploadedFile::fake()->image('logo.png', 100, 100),
        ]);
        $resp->assertRedirect(route('platform.settings.index'));

        $path = PlatformSetting::query()->find(1)->logo;
        $this->assertNotNull($path);
        $this->assertStringStartsWith('logos/', $path);
    }

    public function test_landing_and_blog_header_use_platform_app_name(): void
    {
        PlatformSetting::query()->where('id', 1)->update([
            'app_name' => 'SDIT Harapan Umat',
            'tagline' => 'Sekolah Islam Terpadu Harapan',
        ]);
        PlatformSetting::forget();

        $this->get('/')->assertOk()
            ->assertSee('SDIT Harapan Umat')
            ->assertSee('Sekolah Islam Terpadu Harapan');
        $this->get('/blog')->assertOk()->assertSee('SDIT Harapan Umat')->assertSee('Sekolah Islam Terpadu Harapan');
        $this->get(route('auth.login'))->assertOk()->assertSee('SDIT Harapan Umat')->assertSee('Sekolah Islam Terpadu Harapan');
    }
}
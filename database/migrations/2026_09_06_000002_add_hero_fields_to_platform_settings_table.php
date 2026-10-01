<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('platform_settings', function (Blueprint $table) {
            $table->string('hero_badge', 100)->nullable()->after('tagline');
            $table->string('hero_title', 255)->nullable()->after('hero_badge');
            $table->string('hero_title_highlight', 100)->nullable()->after('hero_title');
            $table->text('hero_desc')->nullable()->after('hero_title_highlight');
            $table->string('hero_microcopy', 255)->nullable()->after('hero_desc');
            $table->string('hero_cta_guest', 100)->nullable()->after('hero_microcopy');
            $table->string('hero_cta_features', 100)->nullable()->after('hero_cta_guest');
            $table->string('hero_cta_auth', 100)->nullable()->after('hero_cta_features');
        });

        DB::table('platform_settings')->where('id', 1)->update([
            'hero_badge' => 'Solusi Digital untuk Sekolah Islam',
            'hero_title' => 'Manajemen Sekolah Islam Terpadu',
            'hero_title_highlight' => 'dalam Satu Platform',
            'hero_desc' => 'Kelola kehadiran, absensi pembelajaran, hafalan Al-Qur\'an, nilai & e-Rapor, serta pembayaran SPP secara terpadu untuk guru, siswa, orang tua, dan manajemen sekolah.',
            'hero_microcopy' => 'Tanpa kartu kredit · Setup 5 menit · Support WhatsApp',
            'hero_cta_guest' => 'Mulai Gratis 14 Hari',
            'hero_cta_features' => 'Lihat Fitur',
            'hero_cta_auth' => 'Buka Dashboard',
        ]);
    }

    public function down(): void
    {
        Schema::table('platform_settings', function (Blueprint $table) {
            $table->dropColumn([
                'hero_badge',
                'hero_title',
                'hero_title_highlight',
                'hero_desc',
                'hero_microcopy',
                'hero_cta_guest',
                'hero_cta_features',
                'hero_cta_auth',
            ]);
        });
    }
};
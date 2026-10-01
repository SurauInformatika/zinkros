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
            $table->string('fitur_heading', 100)->nullable()->after('hero_cta_auth');
            $table->string('fitur_subtitle', 255)->nullable()->after('fitur_heading');
            $table->json('features')->nullable()->after('fitur_subtitle');
        });

        DB::table('platform_settings')->where('id', 1)->update([
            'fitur_heading' => 'Fitur Utama',
            'fitur_subtitle' => 'Semua kebutuhan administrasi sekolah modern dalam satu aplikasi yang mudah digunakan.',
            'features' => json_encode(config('platform.feature_defaults')),
        ]);
    }

    public function down(): void
    {
        Schema::table('platform_settings', function (Blueprint $table) {
            $table->dropColumn(['fitur_heading', 'fitur_subtitle', 'features']);
        });
    }
};
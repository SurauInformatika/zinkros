<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('platform_settings', function (Blueprint $table) {
            $table->boolean('show_hero')->default(true)->after('peran_roles');
            $table->boolean('show_fitur')->default(true)->after('show_hero');
            $table->boolean('show_tampilan')->default(true)->after('show_fitur');
            $table->boolean('show_peran')->default(true)->after('show_tampilan');
            $table->boolean('show_sekolah')->default(true)->after('show_peran');
            $table->string('sekolah_heading', 150)->default('Sekolah yang Sudah Menggunakan')->after('show_sekolah');
            $table->string('sekolah_subtitle', 300)->default('Bergabung bersama sekolah-sekolah Islam terbaik di Indonesia.')->after('sekolah_heading');
            $table->string('testi_heading', 150)->default('Apa Kata Mereka?')->after('sekolah_subtitle');
            $table->string('testi_subtitle', 300)->default('Testimoni dari admin sekolah yang sudah menggunakan aplikasi ini.')->after('testi_heading');
            $table->longText('testimonials')->nullable()->after('testi_subtitle');
        });

        $defaults = config('platform.testimonial_defaults', []);
        if ($defaults !== []) {
            \Illuminate\Support\Facades\DB::table('platform_settings')
                ->whereNull('testimonials')
                ->update(['testimonials' => json_encode($defaults)]);
        }
    }

    public function down(): void
    {
        Schema::table('platform_settings', function (Blueprint $table) {
            $table->dropColumn([
                'show_hero',
                'show_fitur',
                'show_tampilan',
                'show_peran',
                'show_sekolah',
                'sekolah_heading',
                'sekolah_subtitle',
                'testi_heading',
                'testi_subtitle',
                'testimonials',
            ]);
        });
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('platform_settings', function (Blueprint $table) {
            $table->string('tampilan_heading', 150)->default('Tampilan Aplikasi')->after('fitur_subtitle');
            $table->string('tampilan_subtitle', 300)->default('Antarmuka yang intuitif untuk setiap peran pengguna.')->after('tampilan_heading');
            $table->longText('tampilan_shots')->nullable()->after('tampilan_subtitle');
        });

        $defaults = config('platform.tampilan_defaults', []);
        if ($defaults !== []) {
            \Illuminate\Support\Facades\DB::table('platform_settings')
                ->whereNull('tampilan_shots')
                ->update(['tampilan_shots' => json_encode($defaults)]);
        }
    }

    public function down(): void
    {
        Schema::table('platform_settings', function (Blueprint $table) {
            $table->dropColumn(['tampilan_heading', 'tampilan_subtitle', 'tampilan_shots']);
        });
    }
};
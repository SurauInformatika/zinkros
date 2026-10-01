<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('platform_settings', function (Blueprint $table) {
            $table->string('peran_heading', 150)->default('Dibuat untuk Setiap Peran')->after('tampilan_subtitle');
            $table->string('peran_subtitle', 300)->default('Setiap peran memiliki dashboard dan akses yang disesuaikan dengan tugasnya.')->after('peran_heading');
            $table->longText('peran_roles')->nullable()->after('peran_subtitle');
        });

        $defaults = config('platform.peran_defaults', []);
        if ($defaults !== []) {
            \Illuminate\Support\Facades\DB::table('platform_settings')
                ->whereNull('peran_roles')
                ->update(['peran_roles' => json_encode($defaults)]);
        }
    }

    public function down(): void
    {
        Schema::table('platform_settings', function (Blueprint $table) {
            $table->dropColumn(['peran_heading', 'peran_subtitle', 'peran_roles']);
        });
    }
};
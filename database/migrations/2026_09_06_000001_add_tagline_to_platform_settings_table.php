<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('platform_settings', 'tagline')) {
            Schema::table('platform_settings', function (Blueprint $table) {
                $table->string('tagline', 100)->nullable()->after('app_name');
            });
        }

        DB::table('platform_settings')->whereNull('tagline')->update(['tagline' => 'Sekolah Islam Terpadu']);
    }

    public function down(): void
    {
        Schema::table('platform_settings', function (Blueprint $table) {
            $table->dropColumn('tagline');
        });
    }
};
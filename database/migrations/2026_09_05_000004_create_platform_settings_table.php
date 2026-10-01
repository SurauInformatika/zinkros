<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_settings', function (Blueprint $table) {
            $table->id();
            $table->string('app_name')->default('SIT School');
            $table->string('logo')->nullable();
            $table->string('primary_color', 7)->default('#059669');
            $table->string('secondary_color', 7)->default('#0d9488');
            $table->timestamps();
        });

        DB::table('platform_settings')->insert([
            'app_name' => 'SIT School',
            'logo' => null,
            'primary_color' => '#059669',
            'secondary_color' => '#0d9488',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_settings');
    }
};
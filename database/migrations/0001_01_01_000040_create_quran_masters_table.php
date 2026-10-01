<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quran_masters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedInteger('surah_number');
            $table->string('surah_name');
            $table->unsignedInteger('total_ayats');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quran_masters');
    }
};

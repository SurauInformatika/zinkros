<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasmi_record_items', function (Blueprint $table) {
            $table->unsignedTinyInteger('fluency_score')->nullable()->after('score');
            $table->unsignedTinyInteger('tajwid_score')->nullable()->after('fluency_score');
            $table->unsignedTinyInteger('fasohah_score')->nullable()->after('tajwid_score');
        });
    }

    public function down(): void
    {
        Schema::table('tasmi_record_items', function (Blueprint $table) {
            $table->dropColumn(['fluency_score', 'tajwid_score', 'fasohah_score']);
        });
    }
};

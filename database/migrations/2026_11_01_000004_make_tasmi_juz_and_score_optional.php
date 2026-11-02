<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasmi_records', function (Blueprint $table) {
            $table->unsignedTinyInteger('juz')->nullable()->change();
        });

        Schema::table('tasmi_record_items', function (Blueprint $table) {
            $table->unsignedTinyInteger('score')->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('tasmi_records', function (Blueprint $table) {
            $table->unsignedTinyInteger('juz')->nullable(false)->change();
        });

        Schema::table('tasmi_record_items', function (Blueprint $table) {
            $table->unsignedTinyInteger('score')->nullable(false)->default(0)->change();
        });
    }
};

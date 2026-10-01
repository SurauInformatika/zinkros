<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tahfidz_records', function (Blueprint $table) {
            $table->unsignedTinyInteger('score')->default(0)->after('ayat_end');
            $table->dropColumn('grade');
        });
    }

    public function down(): void
    {
        Schema::table('tahfidz_records', function (Blueprint $table) {
            $table->char('grade', 1)->default('A')->after('ayat_end');
            $table->dropColumn('score');
        });
    }
};

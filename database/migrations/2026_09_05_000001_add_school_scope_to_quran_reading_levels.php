<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quran_reading_levels', function (Blueprint $table) {
            $table->foreignUuid('school_id')->nullable()->after('id')->constrained('schools')->nullOnDelete();
            $table->unsignedInteger('sort_order')->default(0)->after('pages');
        });

        DB::statement("ALTER TABLE `quran_reading_levels` MODIFY `kind` VARCHAR(20) NOT NULL DEFAULT 'JILID'");

        Schema::table('quran_reading_levels', function (Blueprint $table) {
            $table->dropUnique('quran_reading_levels_kind_number_unique');
            $table->index(['school_id', 'kind', 'number']);
        });
    }

    public function down(): void
    {
        Schema::table('quran_reading_levels', function (Blueprint $table) {
            $table->dropIndex('quran_reading_levels_school_id_kind_number_index');
            $table->unique(['kind', 'number']);
        });

        DB::statement("ALTER TABLE `quran_reading_levels` MODIFY `kind` ENUM('JILID','JUZ') NOT NULL DEFAULT 'JILID'");

        Schema::table('quran_reading_levels', function (Blueprint $table) {
            $table->dropConstrainedForeignId('school_id');
            $table->dropColumn('sort_order');
        });
    }
};
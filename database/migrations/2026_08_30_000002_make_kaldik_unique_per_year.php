<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academic_calendars', function (Blueprint $table) {
            $table->dropForeign('academic_calendars_school_id_foreign');
            $table->dropUnique('cal_uniq');
            $table->unique(['school_id', 'academic_year_id', 'version'], 'cal_uniq');
            $table->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('academic_calendars', function (Blueprint $table) {
            $table->dropForeign('academic_calendars_school_id_foreign');
            $table->dropUnique('cal_uniq');
            $table->unique(['school_id', 'academic_year_id', 'semester', 'version'], 'cal_uniq');
            $table->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();
        });
    }
};
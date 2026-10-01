<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            if (!Schema::hasColumn('grades', 'academic_year_id')) {
                $table->foreignUuid('academic_year_id')->nullable()->after('school_id')->constrained('academic_years')->nullOnDelete();
            }
        });

        Schema::table('attendance_classes', function (Blueprint $table) {
            if (!Schema::hasColumn('attendance_classes', 'academic_year_id')) {
                $table->foreignUuid('academic_year_id')->nullable()->after('school_id')->constrained('academic_years')->nullOnDelete();
            }
        });

        Schema::table('attendance_subjects', function (Blueprint $table) {
            if (!Schema::hasColumn('attendance_subjects', 'academic_year_id')) {
                $table->foreignUuid('academic_year_id')->nullable()->after('school_id')->constrained('academic_years')->nullOnDelete();
            }
        });

        Schema::table('class_subject_teacher', function (Blueprint $table) {
            if (!Schema::hasColumn('class_subject_teacher', 'academic_year_id')) {
                $table->foreignUuid('academic_year_id')->nullable()->after('school_id')->constrained('academic_years')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->dropForeign(['academic_year_id']);
            $table->dropColumn('academic_year_id');
        });
        Schema::table('attendance_classes', function (Blueprint $table) {
            $table->dropForeign(['academic_year_id']);
            $table->dropColumn('academic_year_id');
        });
        Schema::table('attendance_subjects', function (Blueprint $table) {
            $table->dropForeign(['academic_year_id']);
            $table->dropColumn('academic_year_id');
        });
        Schema::table('class_subject_teacher', function (Blueprint $table) {
            $table->dropForeign(['academic_year_id']);
            $table->dropColumn('academic_year_id');
        });
    }
};

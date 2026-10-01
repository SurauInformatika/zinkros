<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'classes',
            'subjects',
            'students',
            'attendance_gates',
            'attendance_subjects',
            'grades',
            'quran_progress_logs',
            'teacher_subject',
            'class_subject_teacher',
        ];

        DB::table('subjects')->delete();

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->foreignUuid('school_id')->after('id')->constrained('schools')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'classes',
            'subjects',
            'students',
            'attendance_gates',
            'attendance_subjects',
            'grades',
            'quran_progress_logs',
            'teacher_subject',
            'class_subject_teacher',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropForeign(['school_id']);
                $blueprint->dropColumn('school_id');
            });
        }
    }
};

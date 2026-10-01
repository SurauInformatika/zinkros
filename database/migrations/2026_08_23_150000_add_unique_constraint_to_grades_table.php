<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->unique(
                ['teacher_id', 'subject_id', 'student_id', 'date', 'grade_type_id', 'academic_year_id'],
                'grades_unique_per_teacher'
            );
        });
    }

    public function down(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->dropUnique('grades_unique_per_teacher');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quran_teaching_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('school_id');
            $table->uuid('academic_year_id')->nullable();
            $table->uuid('student_id');
            $table->uuid('teacher_id');
            $table->timestamps();

            $table->unique(['student_id', 'academic_year_id'], 'qta_student_year_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quran_teaching_assignments');
    }
};

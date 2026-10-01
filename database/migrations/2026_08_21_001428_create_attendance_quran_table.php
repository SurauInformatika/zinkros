<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_quran', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('school_id');
            $table->uuid('academic_year_id')->nullable();
            $table->uuid('student_id');
            $table->uuid('teacher_id');
            $table->date('date');
            $table->enum('status', ['HADIR', 'SAKIT', 'IZIN', 'ALPA']);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'date', 'academic_year_id'], 'aq_student_date_year_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_quran');
    }
};

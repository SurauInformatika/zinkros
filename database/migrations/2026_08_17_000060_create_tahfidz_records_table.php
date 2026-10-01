<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tahfidz_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignUuid('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignUuid('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('quran_master_id')->constrained('quran_masters')->restrictOnDelete();
            $table->unsignedInteger('ayat_start');
            $table->unsignedInteger('ayat_end');
            $table->enum('activity_type', ['ZIADAH', 'MURAJAAH']);
            $table->enum('grade', ['A', 'B', 'C']);
            $table->date('recorded_date');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'academic_year_id', 'student_id']);
            $table->index(['teacher_id', 'recorded_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tahfidz_records');
    }
};

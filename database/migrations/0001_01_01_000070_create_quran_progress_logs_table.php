<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quran_progress_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('attendance_subject_id')->constrained('attendance_subjects')->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->enum('activity_type', ['ZIADAH', 'MURAJAAH']);
            $table->foreignUuid('quran_master_id')->constrained('quran_masters')->restrictOnDelete();
            $table->unsignedInteger('ayat_start');
            $table->unsignedInteger('ayat_end');
            $table->enum('grade', ['A', 'B', 'C']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quran_progress_logs');
    }
};

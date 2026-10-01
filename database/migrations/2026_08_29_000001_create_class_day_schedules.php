<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_day_schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignUuid('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->foreignUuid('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignUuid('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignUuid('teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('day_name', ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu']);
            $table->tinyInteger('start_jp')->unsigned();
            $table->tinyInteger('end_jp')->unsigned();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->index(['class_id', 'academic_year_id'], 'cds_class_ay_idx');
            $table->index(['teacher_id', 'academic_year_id'], 'cds_teacher_ay_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_day_schedules');
    }
};
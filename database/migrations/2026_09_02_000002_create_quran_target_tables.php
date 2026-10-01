<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_quran_targets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignUuid('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignUuid('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->date('target_date');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'academic_year_id']);
            $table->index(['school_id', 'teacher_id']);
        });

        Schema::create('student_quran_target_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('target_id')->constrained('student_quran_targets')->cascadeOnDelete();
            $table->foreignUuid('quran_master_id')->constrained('quran_masters')->restrictOnDelete();
            $table->unsignedInteger('ayat_start');
            $table->unsignedInteger('ayat_end');
            $table->timestamps();

            $table->index(['target_id']);
        });

        Schema::create('quran_target_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedInteger('grade_level')->nullable();
            $table->string('title');
            $table->date('target_date');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['school_id', 'grade_level']);
        });

        Schema::create('quran_target_template_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('template_id')->constrained('quran_target_templates')->cascadeOnDelete();
            $table->foreignUuid('quran_master_id')->constrained('quran_masters')->restrictOnDelete();
            $table->unsignedInteger('ayat_start');
            $table->unsignedInteger('ayat_end');
            $table->timestamps();

            $table->index(['template_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quran_target_template_items');
        Schema::dropIfExists('quran_target_templates');
        Schema::dropIfExists('student_quran_target_items');
        Schema::dropIfExists('student_quran_targets');
    }
};

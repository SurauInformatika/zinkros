<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasmi_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignUuid('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignUuid('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('juz');
            $table->date('recorded_date');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'academic_year_id', 'student_id']);
            $table->index(['teacher_id', 'recorded_date']);
        });

        Schema::create('tasmi_record_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tasmi_record_id')->constrained('tasmi_records')->cascadeOnDelete();
            $table->foreignUuid('quran_master_id')->constrained('quran_masters')->restrictOnDelete();
            $table->unsignedInteger('ayat_start');
            $table->unsignedInteger('ayat_end');
            $table->timestamps();

            $table->unique(['tasmi_record_id', 'quran_master_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasmi_record_items');
        Schema::dropIfExists('tasmi_records');
    }
};

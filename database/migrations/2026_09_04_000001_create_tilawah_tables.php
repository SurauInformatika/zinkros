<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quran_reading_levels', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('kind', ['JILID', 'JUZ']);
            $table->unsignedInteger('number');
            $table->string('label');
            $table->unsignedInteger('pages')->default(0);
            $table->timestamps();

            $table->unique(['kind', 'number']);
        });

        Schema::create('tilawah_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignUuid('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignUuid('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('reading_level_id')->nullable()->constrained('quran_reading_levels')->restrictOnDelete();
            $table->unsignedInteger('page_start')->default(0);
            $table->unsignedInteger('page_end')->default(0);
            $table->unsignedTinyInteger('score')->default(0);
            $table->string('status', 10)->nullable();
            $table->date('recorded_date');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'academic_year_id', 'student_id']);
            $table->index(['teacher_id', 'recorded_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tilawah_records');
        Schema::dropIfExists('quran_reading_levels');
    }
};

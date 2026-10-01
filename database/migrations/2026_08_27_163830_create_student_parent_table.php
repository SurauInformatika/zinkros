<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_parent', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('relation', ['AYAH', 'IBU', 'WALI'])->default('WALI');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->unique(['student_id', 'user_id']);
            $table->index(['user_id', 'school_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_parent');
    }
};
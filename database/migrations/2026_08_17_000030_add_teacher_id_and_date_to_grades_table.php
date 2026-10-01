<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            if (!Schema::hasColumn('grades', 'teacher_id')) {
                $table->foreignUuid('teacher_id')->constrained('users')->cascadeOnDelete()->after('subject_id');
            }
            if (!Schema::hasColumn('grades', 'date')) {
                $table->date('date')->nullable()->after('type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
            $table->dropColumn(['teacher_id', 'date']);
        });
    }
};

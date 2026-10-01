<?php

namespace Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_subjects', function (Blueprint $table) {
            if (!Schema::hasColumn('attendance_subjects', 'school_id')) {
                $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete()->after('id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('attendance_subjects', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
            $table->dropColumn('school_id');
        });
    }
};

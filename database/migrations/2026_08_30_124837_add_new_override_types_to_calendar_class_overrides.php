<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE calendar_class_overrides MODIFY override_type ENUM('orientation', 'exam', 'graduation', 'digital_class', 'assessment', 'teacher_training', 'field_trip', 'outing_class', 'pekan_olahraga', 'validasi', 'other') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE calendar_class_overrides MODIFY override_type ENUM('orientation', 'exam', 'graduation', 'digital_class', 'assessment', 'teacher_training', 'other') NOT NULL");
    }
};
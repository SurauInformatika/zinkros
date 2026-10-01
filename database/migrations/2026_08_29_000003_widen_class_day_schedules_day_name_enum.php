<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE class_day_schedules MODIFY day_name ENUM('senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'ahad') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE class_day_schedules MODIFY day_name ENUM('senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu') NOT NULL");
    }
};
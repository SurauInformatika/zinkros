<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE tahfidz_records MODIFY quran_master_id CHAR(36) NULL');
        DB::statement("ALTER TABLE tahfidz_records MODIFY activity_type ENUM('ZIADAH','MURAJAAH') NULL");
    }

    public function down(): void
    {
        DB::statement('DELETE FROM tahfidz_records WHERE quran_master_id IS NULL OR activity_type IS NULL');
        DB::statement('ALTER TABLE tahfidz_records MODIFY quran_master_id CHAR(36) NOT NULL');
        DB::statement("ALTER TABLE tahfidz_records MODIFY activity_type ENUM('ZIADAH','MURAJAAH') NOT NULL");
    }
};

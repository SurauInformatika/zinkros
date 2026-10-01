<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','guru','management','keuangan','ortu','staff','superadmin','wakakur','kepsek','wakamur','murid') NOT NULL DEFAULT 'ortu'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','guru','management','keuangan','ortu','staff','superadmin','wakakur','kepsek','wakamur') NOT NULL DEFAULT 'ortu'");
    }
};
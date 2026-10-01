<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: allow the new unified role value in the ENUM (still alongside
        // the legacy values so existing rows can be migrated in place).
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','guru','keuangan','ortu','staff','superadmin','wakakur','kepsek','wakamur','murid','wakasek') NOT NULL DEFAULT 'ortu'");

        // Step 2: migrate legacy wakasek rows to the unified role, recording their
        // jabatan (position) based on the old role so per-position features can
        // still differentiate them.
        DB::table('users')
            ->where('role', 'wakakur')
            ->update([
                'role' => 'wakasek',
                'position' => DB::raw("COALESCE(NULLIF(position, ''), 'kurikulum')"),
            ]);

        DB::table('users')
            ->where('role', 'wakamur')
            ->update([
                'role' => 'wakasek',
                'position' => DB::raw("COALESCE(NULLIF(position, ''), 'kesiswaan')"),
            ]);

        // Step 3: drop the legacy role values from the ENUM.
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','guru','keuangan','ortu','staff','superadmin','kepsek','murid','wakasek') NOT NULL DEFAULT 'ortu'");
    }

    public function down(): void
    {
        // Re-add legacy values (cannot restore each row's original role reliably,
        // but keeps the schema capable of storing them again).
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','guru','keuangan','ortu','staff','superadmin','wakakur','kepsek','wakamur','murid','wakasek') NOT NULL DEFAULT 'ortu'");
    }
};

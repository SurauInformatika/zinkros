<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','guru','management','keuangan','ortu','staff','superadmin','wakakur','kepsek','wakamur') NOT NULL DEFAULT 'ortu'");

        Schema::table('users', function (Blueprint $table) {
            $table->uuid('created_by')->nullable()->after('role');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn('created_by');
        });

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','guru','management','keuangan','ortu','staff','superadmin','wakakur','kepsek') NOT NULL DEFAULT 'ortu'");
    }
};
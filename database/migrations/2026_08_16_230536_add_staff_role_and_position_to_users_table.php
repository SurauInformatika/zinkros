<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'guru', 'management', 'keuangan', 'ortu', 'staff'])->default('ortu')->change();
            $table->string('position')->nullable()->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('position');
            $table->enum('role', ['admin', 'guru', 'management', 'keuangan', 'ortu'])->default('ortu')->change();
        });
    }
};

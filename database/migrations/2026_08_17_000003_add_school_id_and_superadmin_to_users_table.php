<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignUuid('school_id')->nullable()->after('id')->constrained('schools')->cascadeOnDelete();
            $table->enum('role', ['admin', 'guru', 'management', 'keuangan', 'ortu', 'staff', 'superadmin'])->default('ortu')->change();
        });

        $schoolId = (string) Str::uuid();
        DB::table('schools')->insert([
            'id' => $schoolId,
            'name' => 'Sekolah SIT (Default)',
            'slug' => 'sit-default',
            'plan' => 'pro',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->whereNull('school_id')->update(['school_id' => $schoolId]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'guru', 'management', 'keuangan', 'ortu', 'staff'])->default('ortu')->change();
            $table->dropForeign(['school_id']);
            $table->dropColumn('school_id');
        });
    }
};

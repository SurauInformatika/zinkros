<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $users = DB::table('users')
            ->where('role', 'guru')
            ->where('is_pj_tahfidz', true)
            ->get();

        foreach ($users as $user) {
            DB::table('teacher_roles')->insert([
                'id' => (string) Str::uuid(),
                'school_id' => $user->school_id,
                'teacher_id' => $user->id,
                'role_name' => 'PJ Tahfidz',
                'is_student_related' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('teacher_roles')
            ->where('role_name', 'PJ Tahfidz')
            ->delete();
    }
};

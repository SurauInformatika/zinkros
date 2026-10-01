<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_homerooms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignUuid('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('label', 191)->nullable();
            $table->unsignedTinyInteger('sort')->default(1);
            $table->timestamps();

            $table->unique(['class_id', 'user_id']);
        });

        DB::table('classes')->whereNotNull('wali_kelas_id')->orderBy('grade_level')->orderBy('class_name')->get()
            ->each(function ($class) {
                $user = DB::table('users')->where('id', $class->wali_kelas_id)->first();
                if (! $user) {
                    return;
                }

                DB::table('class_homerooms')->insert([
                    'id' => (string) Illuminate\Support\Str::uuid(),
                    'school_id' => $class->school_id,
                    'class_id' => $class->id,
                    'user_id' => $user->id,
                    'label' => null,
                    'sort' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('users')->where('id', $user->id)->update(['is_wali_kelas' => true]);
            });

        Schema::table('classes', function (Blueprint $table) {
            $table->dropForeign(['wali_kelas_id']);
            $table->dropColumn('wali_kelas_id');
        });
    }

    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->foreignUuid('wali_kelas_id')->nullable()->after('grade_level')->constrained('users')->nullOnDelete();
        });

        DB::table('class_homerooms')->orderBy('sort')->get()->each(function ($row) {
            $class = DB::table('classes')->where('id', $row->class_id)->first();
            if ($class && ! $class->wali_kelas_id) {
                DB::table('classes')->where('id', $row->class_id)->update(['wali_kelas_id' => $row->user_id]);
            }
        });

        Schema::dropIfExists('class_homerooms');
    }
};

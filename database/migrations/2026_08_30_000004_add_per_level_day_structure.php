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
        Schema::table('calendar_level_structures', function (Blueprint $table) {
            $table->json('jp_per_day')->after('jp_duration_minutes')->nullable();
        });

        $calendars = DB::table('academic_calendars')->get();
        foreach ($calendars as $cal) {
            $dayMap = [];
            foreach (DB::table('calendar_day_structures')->where('academic_calendar_id', $cal->id)->get() as $row) {
                $dayMap[$row->day_name] = (int) $row->jp_count;
            }

            $levels = DB::table('calendar_level_structures')->where('academic_calendar_id', $cal->id)->get();
            if ($levels->isEmpty()) {
                $maxGrade = (int) (DB::table('classes')->where('school_id', $cal->school_id)->max('grade_level') ?: 12);
                $avgDur = DB::table('calendar_day_structures')->where('academic_calendar_id', $cal->id)->avg('jp_duration_minutes');
                DB::table('calendar_level_structures')->insert([
                    'id' => (string) Str::uuid(),
                    'academic_calendar_id' => $cal->id,
                    'grade_level_start' => 1,
                    'grade_level_end' => $maxGrade,
                    'jp_duration_minutes' => $avgDur ? (int) round($avgDur) : 35,
                    'jp_per_day' => json_encode($dayMap),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $payload = json_encode($dayMap);
                foreach ($levels as $level) {
                    DB::table('calendar_level_structures')->where('id', $level->id)->update(['jp_per_day' => $payload]);
                }
            }
        }

        Schema::dropIfExists('calendar_day_structures');
    }

    public function down(): void
    {
        Schema::create('calendar_day_structures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('academic_calendar_id')->constrained('academic_calendars')->cascadeOnDelete();
            $table->enum('day_name', ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu']);
            $table->integer('jp_count')->default(0);
            $table->integer('jp_duration_minutes')->default(35);
            $table->timestamps();

            $table->unique(['academic_calendar_id', 'day_name'], 'cds_uniq');
        });

        Schema::table('calendar_level_structures', function (Blueprint $table) {
            $table->dropColumn('jp_per_day');
        });
    }
};
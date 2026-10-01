<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->foreignUuid('grade_type_id')->nullable()->after('subject_id')->constrained('grade_types')->nullOnDelete();
        });

        $typeMap = [
            'DAILY' => 'HARIAN',
            'TASK' => 'TUGAS',
            'EXAM' => 'UAS',
        ];

        foreach ($typeMap as $old => $code) {
            $gradeType = DB::table('grade_types')->where('code', $code)->first();
            if ($gradeType) {
                DB::table('grades')
                    ->where('type', $old)
                    ->update(['grade_type_id' => $gradeType->id]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->dropForeign(['grade_type_id']);
            $table->dropColumn('grade_type_id');
        });
    }
};

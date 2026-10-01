<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('gender')->nullable()->after('name');
        });

        $femaleClasses = DB::table('classes')
            ->whereIn('class_name', ['7A', '7B', '8A', '8B', '9A'])
            ->pluck('id');

        DB::table('students')
            ->whereIn('class_id', $femaleClasses)
            ->update(['gender' => 'P']);

        DB::table('students')
            ->whereNull('gender')
            ->update(['gender' => 'L']);
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('gender');
        });
    }
};

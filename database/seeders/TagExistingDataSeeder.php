<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\AcademicYear;

class TagExistingDataSeeder extends Seeder
{
    public function run(): void
    {
        $schools = DB::table('schools')->pluck('id');

        foreach ($schools as $schoolId) {
            $ay = AcademicYear::where('school_id', $schoolId)->first();
            if (!$ay) continue;

            $g = DB::table('grades')->where('school_id', $schoolId)->whereNull('academic_year_id')->update(['academic_year_id' => $ay->id]);
            $ac = DB::table('attendance_classes')->where('school_id', $schoolId)->whereNull('academic_year_id')->update(['academic_year_id' => $ay->id]);
            $as = DB::table('attendance_subjects')->where('school_id', $schoolId)->whereNull('academic_year_id')->update(['academic_year_id' => $ay->id]);
            $cst = DB::table('class_subject_teacher')->where('school_id', $schoolId)->whereNull('academic_year_id')->update(['academic_year_id' => $ay->id]);

            $this->command->info("School {$schoolId}: grades={$g}, ac={$ac}, as={$as}, cst={$cst}");
        }
    }
}

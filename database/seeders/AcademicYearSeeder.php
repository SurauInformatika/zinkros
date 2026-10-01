<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AcademicYearSeeder extends Seeder
{
    public function run(): void
    {
        $schools = DB::table('schools')->pluck('id');

        $years = [
            [
                'name' => '2025/2026',
                'start_date' => '2025-07-01',
                'end_date' => '2026-06-30',
                'is_active' => false,
            ],
            [
                'name' => '2026/2027',
                'start_date' => '2026-07-01',
                'end_date' => '2027-06-30',
                'is_active' => true,
            ],
        ];

        foreach ($schools as $schoolId) {
            foreach ($years as $year) {
                $exists = DB::table('academic_years')
                    ->where('school_id', $schoolId)
                    ->where('name', $year['name'])
                    ->first();

                if (!$exists) {
                    DB::table('academic_years')->insert(array_merge($year, [
                        'id' => Str::uuid()->toString(),
                        'school_id' => $schoolId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]));
                }
            }
        }
    }
}

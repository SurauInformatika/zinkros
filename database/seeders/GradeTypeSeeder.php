<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GradeTypeSeeder extends Seeder
{
    public function run(): void
    {
        $schools = DB::table('schools')->pluck('id');

        $defaults = [
            ['code' => 'HARIAN',   'name' => 'Harian',                'weight' => 15, 'sort_order' => 1],
            ['code' => 'TUGAS',    'name' => 'Tugas',                 'weight' => 15, 'sort_order' => 2],
            ['code' => 'SUMATIF',  'name' => 'Ujian Sumatif',         'weight' => 20, 'sort_order' => 3],
            ['code' => 'UTS',      'name' => 'Ujian Tengah Semester', 'weight' => 20, 'sort_order' => 4],
            ['code' => 'PRAKTIK',  'name' => 'Praktik',               'weight' => 10, 'sort_order' => 5],
            ['code' => 'UAS',      'name' => 'Ujian Akhir Semester',  'weight' => 20, 'sort_order' => 6],
        ];

        foreach ($schools as $schoolId) {
            foreach ($defaults as $type) {
                $exists = DB::table('grade_types')
                    ->where('school_id', $schoolId)
                    ->where('code', $type['code'])
                    ->first();

                if (!$exists) {
                    DB::table('grade_types')->insert(array_merge($type, [
                        'id' => Str::uuid()->toString(),
                        'school_id' => $schoolId,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]));
                }
            }
        }
    }
}

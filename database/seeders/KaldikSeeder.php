<?php

namespace Database\Seeders;

use App\Models\KaldikTemplate;
use App\Models\RecurringHoliday;
use Illuminate\Database\Seeder;

class KaldikSeeder extends Seeder
{
    public function run(): void
    {
        // ── Template: Kemenag ──
        KaldikTemplate::updateOrCreate(
            ['name' => 'Template Kemenag 2026/2027'],
            [
                'source' => 'kemenag',
                'is_active' => true,
                'default_day_structure' => [
                    'senin'    => 7,
                    'selasa'   => 7,
                    'rabu'     => 7,
                    'kamis'    => 6,
                    'jumat'    => 3,
                    'sabtu'    => 0,
                ],
                'default_level_structure' => [
                    ['start' => 1, 'end' => 3, 'menit' => 35],
                    ['start' => 4, 'end' => 6, 'menit' => 40],
                ],
                'default_holidays' => [
                    ['name' => 'Tahun Baru Masehi',      'month' => 1,  'day' => 1,  'type' => 'masehi'],
                    ['name' => 'Isra Mi\'raj',            'month' => 2,  'day' => null, 'type' => 'hijriah'],
                    ['name' => 'Tahun Baru Hijriah',      'month' => 6,  'day' => null, 'type' => 'hijriah'],
                    ['name' => 'Maulid Nabi',             'month' => 9,  'day' => null, 'type' => 'hijriah'],
                    ['name' => 'HUT RI',                  'month' => 8,  'day' => 17, 'type' => 'masehi'],
                ],
            ]
        );

        // ── Template: Dindik ──
        KaldikTemplate::updateOrCreate(
            ['name' => 'Template Dindik 2026/2027'],
            [
                'source' => 'dindik',
                'is_active' => true,
                'default_day_structure' => [
                    'senin'    => 8,
                    'selasa'   => 8,
                    'rabu'     => 8,
                    'kamis'    => 7,
                    'jumat'    => 4,
                    'sabtu'    => 0,
                ],
                'default_level_structure' => [
                    ['start' => 1, 'end' => 6, 'menit' => 40],
                ],
                'default_holidays' => [
                    ['name' => 'Tahun Baru Masehi',      'month' => 1,  'day' => 1,  'type' => 'masehi'],
                    ['name' => 'Isra Mi\'raj',            'month' => 2,  'day' => null, 'type' => 'hijriah'],
                    ['name' => 'Tahun Baru Hijriah',      'month' => 6,  'day' => null, 'type' => 'hijriah'],
                    ['name' => 'Maulid Nabi',             'month' => 9,  'day' => null, 'type' => 'hijriah'],
                    ['name' => 'HUT RI',                  'month' => 8,  'day' => 17, 'type' => 'masehi'],
                ],
            ]
        );

        // ── Template: Custom SIT ──
        KaldikTemplate::updateOrCreate(
            ['name' => 'Template SIT Custom 2026/2027'],
            [
                'source' => 'custom',
                'is_active' => true,
                'default_day_structure' => [
                    'senin'    => 6,
                    'selasa'   => 6,
                    'rabu'     => 6,
                    'kamis'    => 5,
                    'jumat'    => 3,
                    'sabtu'    => 0,
                ],
                'default_level_structure' => [
                    ['start' => 1, 'end' => 3, 'menit' => 35],
                    ['start' => 4, 'end' => 6, 'menit' => 40],
                ],
                'default_holidays' => [
                    ['name' => 'Tahun Baru Masehi',      'month' => 1,  'day' => 1,  'type' => 'masehi'],
                    ['name' => 'Isra Mi\'raj',            'month' => 2,  'day' => null, 'type' => 'hijriah'],
                    ['name' => 'Tahun Baru Hijriah',      'month' => 6,  'day' => null, 'type' => 'hijriah'],
                    ['name' => 'Maulid Nabi',             'month' => 9,  'day' => null, 'type' => 'hijriah'],
                    ['name' => 'HUT RI',                  'month' => 8,  'day' => 17, 'type' => 'masehi'],
                ],
            ]
        );

        // ── Libur Rutin (Recurring Holidays) ──
        $holidays = [
            ['name' => 'Tahun Baru Masehi',              'month' => 1,  'day' => 1,  'calendar_type' => 'masehi',  'is_active' => true],
            ['name' => 'Maulid Nabi',                    'month' => 1,  'day' => null, 'calendar_type' => 'hijriah', 'is_active' => true],
            ['name' => 'Tahun Baru Hijriah',             'month' => 6,  'day' => null, 'calendar_type' => 'hijriah', 'is_active' => true],
            ['name' => 'Isra Mi\'raj',                   'month' => 2,  'day' => null, 'calendar_type' => 'hijriah', 'is_active' => true],
            ['name' => 'Waisak',                         'month' => 5,  'day' => null, 'calendar_type' => 'masehi',  'is_active' => true],
            ['name' => 'Kenaikan Isa Almasih',           'month' => 5,  'day' => null, 'calendar_type' => 'masehi',  'is_active' => true],
            ['name' => 'Hari Raya Waisak',               'month' => 5,  'day' => null, 'calendar_type' => 'masehi',  'is_active' => true],
            ['name' => 'Hari Buruh',                     'month' => 5,  'day' => 1,   'calendar_type' => 'masehi',  'is_active' => true],
            ['name' => 'Hari Pendidikan Nasional',       'month' => 5,  'day' => 2,   'calendar_type' => 'masehi',  'is_active' => true],
            ['name' => 'Kenaikan Isa Almasih',           'month' => 5,  'day' => 29,  'calendar_type' => 'masehi',  'is_active' => true],
            ['name' => 'Hari Raya Idul Fitri',           'month' => 3,  'day' => null, 'calendar_type' => 'hijriah', 'is_active' => true],
            ['name' => 'Hari Raya Idul Adha',            'month' => 12, 'day' => null, 'calendar_type' => 'hijriah', 'is_active' => true],
            ['name' => 'Hari Raya Nyepi',                'month' => 3,  'day' => null, 'calendar_type' => 'masehi',  'is_active' => true],
            ['name' => 'HUT Proklamasi Kemerdekaan RI',  'month' => 8,  'day' => 17,  'calendar_type' => 'masehi',  'is_active' => true],
            ['name' => 'Hari Sumpah Pemuda',             'month' => 10, 'day' => 28,  'calendar_type' => 'masehi',  'is_active' => true],
            ['name' => 'Hari Pahlawan',                  'month' => 11, 'day' => 10,  'calendar_type' => 'masehi',  'is_active' => true],
            ['name' => 'Hari Toleransi Internasional',   'month' => 11, 'day' => 16,  'calendar_type' => 'masehi',  'is_active' => true],
            ['name' => 'Hari Guru Nasional',             'month' => 11, 'day' => 25,  'calendar_type' => 'masehi',  'is_active' => true],
            ['name' => 'Hari Anti Korupsi',              'month' => 12, 'day' => 9,   'calendar_type' => 'masehi',  'is_active' => true],
            ['name' => 'Hari Hak Asasi Manusia',         'month' => 12, 'day' => 10,  'calendar_type' => 'masehi',  'is_active' => true],
            ['name' => 'Natal',                          'month' => 12, 'day' => 25,  'calendar_type' => 'masehi',  'is_active' => true],
        ];

        foreach ($holidays as $holiday) {
            RecurringHoliday::updateOrCreate(
                ['name' => $holiday['name']],
                $holiday
            );
        }

        $this->command->info('KALDIK seeder complete: 3 templates + ' . count($holidays) . ' recurring holidays.');
    }
}

<?php

namespace Database\Seeders;

use App\Models\QuranReadingLevel;
use Illuminate\Database\Seeder;

class QuranReadingLevelSeeder extends Seeder
{
    /**
     * Katalog jenjang baca bawaan (global, school_id null) yang dipakai sekolah
     * yang belum mengatur jenjang baca sendiri: jilid (1-6) lalu mushaf (juz 1-30).
     *
     * Jumlah halaman memakai nilai default lazim; bisa disesuaikan per sekolah
     * lewat menu Admin > Jenjang Baca.
     */
    public function run(): void
    {
        $seed = function (string $kind, int $number, string $label, int $pages): void {
            QuranReadingLevel::updateOrCreate(
                ['kind' => $kind, 'number' => $number, 'school_id' => null],
                ['label' => $label, 'pages' => $pages, 'school_id' => null],
            );
        };

        for ($i = 1; $i <= 6; $i++) {
            $seed('JILID', $i, "Jilid {$i}", 30);
        }

        for ($j = 1; $j <= 30; $j++) {
            $seed('JUZ', $j, "Juz {$j}", 20);
        }
    }
}

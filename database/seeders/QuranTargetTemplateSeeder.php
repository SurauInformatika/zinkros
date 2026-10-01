<?php

namespace Database\Seeders;

use App\Models\QuranMaster;
use App\Models\QuranTargetTemplate;
use App\Models\QuranTargetTemplateItem;
use App\Models\School;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class QuranTargetTemplateSeeder extends Seeder
{
    /**
     * Seed satu template target hafalan universal (TK-SMA) per sekolah:
     * Juz 29-30 (An-Naba' s/d An-Nas), deadline akhir semester.
     */
    public function run(): void
    {
        $schools = School::all();

        foreach ($schools as $school) {
            if (QuranTargetTemplate::where('school_id', $school->id)->whereNull('grade_level')->exists()) {
                continue;
            }

            $title = 'Target Hafalan Juz 29-30';
            $targetDate = $this->semesterDeadline();

            $template = QuranTargetTemplate::create([
                'school_id' => $school->id,
                'grade_level' => null,
                'title' => $title,
                'target_date' => $targetDate,
                'is_active' => true,
            ]);

            foreach (range(78, 114) as $surahNumber) {
                $surah = QuranMaster::where('surah_number', $surahNumber)->first();

                if (!$surah) {
                    continue;
                }

                QuranTargetTemplateItem::create([
                    'template_id' => $template->id,
                    'quran_master_id' => $surah->id,
                    'ayat_start' => 1,
                    'ayat_end' => $surah->total_ayats,
                ]);
            }

            $this->command->info("Created universal Quran target template for school {$school->name}");
        }
    }

    /**
     * Tentukan deadline akhir semester (akhir Juni atau akhir Desember),
     * tergantung posisi bulan saat seeder berjalan.
     */
    protected function semesterDeadline(): string
    {
        $now = Carbon::now();

        if ($now->month >= 7) {
            return Carbon::create($now->year, 12, 31)->toDateString();
        }

        return Carbon::create($now->year, 6, 30)->toDateString();
    }
}

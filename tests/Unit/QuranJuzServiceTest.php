<?php

namespace Tests\Unit;

use App\Models\QuranMaster;
use App\Services\QuranJuzService;
use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;
use Tests\TestCase;

class QuranJuzServiceTest extends TestCase
{
    public function test_it_returns_all_surah_ranges_for_a_juz(): void
    {
        $surahs = new Collection;
        for ($number = 1; $number <= 114; $number++) {
            $surah = new QuranMaster;
            $surah->setAttribute('id', 'surah-'.$number);
            $surah->setAttribute('surah_number', $number);
            $surah->setAttribute('surah_name', 'Surah '.$number);
            $surah->setAttribute('total_ayats', 10);
            $surahs->push($surah);
        }

        $service = new QuranJuzService;
        $juz1 = $service->surahsForJuz(1, $surahs);
        $juz30 = $service->surahsForJuz(30, $surahs);

        $this->assertSame(1, $juz1[0]['start']);
        $this->assertSame(141, $juz1[1]['end']);
        $this->assertCount(37, $juz30);
        $this->assertSame(78, $juz30[0]['number']);
        $this->assertSame(1, $juz30[0]['start']);
        $this->assertSame(10, $juz30[0]['end']);
        $this->assertSame(114, $juz30[36]['number']);
        $this->assertSame(10, $juz30[36]['end']);
        $this->assertCount(30, $service->allJuz($surahs));
    }

    public function test_it_rejects_juz_outside_the_quran_range(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new QuranJuzService)->surahsForJuz(31, new Collection);
    }

    public function test_it_returns_madinah_mushaf_pages_and_handles_a_juz_boundary_mid_page(): void
    {
        $surahs = new Collection;
        for ($number = 1; $number <= 114; $number++) {
            $surah = new QuranMaster;
            $surah->setAttribute('id', 'surah-'.$number);
            $surah->setAttribute('surah_number', $number);
            $surah->setAttribute('surah_name', 'Surah '.$number);
            $surah->setAttribute('total_ayats', 300);
            $surahs->push($surah);
        }

        $service = new QuranJuzService;
        $juz30 = $service->pagesForJuz(30, $surahs);
        $juz3Boundary = collect($service->pagesForJuz(3, $surahs))->firstWhere('number', 62);
        $juz4Boundary = collect($service->pagesForJuz(4, $surahs))->firstWhere('number', 62);

        $this->assertCount(23, $juz30);
        $this->assertSame(582, $juz30[0]['number']);
        $this->assertSame(78, $juz30[0]['segments'][0]['number']);
        $this->assertSame(604, $juz30[22]['number']);
        $this->assertSame(114, $juz30[22]['segments'][array_key_last($juz30[22]['segments'])]['number']);
        $this->assertSame([92, 92], [$juz3Boundary['segments'][0]['start'], $juz3Boundary['segments'][0]['end']]);
        $this->assertSame([93, 100], [$juz4Boundary['segments'][0]['start'], $juz4Boundary['segments'][0]['end']]);

        $allPages = $service->allJuzPages($surahs);
        $this->assertSame(6236, collect($allPages)->flatten(1)->sum(
            fn ($page) => collect($page['segments'])->sum(fn ($segment) => $segment['end'] - $segment['start'] + 1)
        ));
        $this->assertSame(604, collect($allPages)->flatten(1)->pluck('number')->unique()->count());
    }

    public function test_it_returns_surah_ranges_for_juz_twenty_six_through_thirty(): void
    {
        $surahs = new Collection;
        for ($number = 1; $number <= 114; $number++) {
            $surah = new QuranMaster;
            $surah->setAttribute('id', 'surah-'.$number);
            $surah->setAttribute('surah_number', $number);
            $surah->setAttribute('surah_name', 'Surah '.$number);
            $surah->setAttribute('total_ayats', 300);
            $surahs->push($surah);
        }

        $service = new QuranJuzService;
        $juz26 = $service->surahsForJuz(26, $surahs);
        $juz27 = $service->surahsForJuz(27, $surahs);
        $juz30 = $service->surahsForJuz(30, $surahs);

        $this->assertSame([46, 1], [$juz26[0]['number'], $juz26[0]['start']]);
        $this->assertSame([51, 30], [$juz26[array_key_last($juz26)]['number'], $juz26[array_key_last($juz26)]['end']]);
        $this->assertSame([51, 31], [$juz27[0]['number'], $juz27[0]['start']]);
        $this->assertSame([78, 1], [$juz30[0]['number'], $juz30[0]['start']]);
        $this->assertSame([114, 300], [$juz30[array_key_last($juz30)]['number'], $juz30[array_key_last($juz30)]['end']]);
    }
}

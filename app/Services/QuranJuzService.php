<?php

namespace App\Services;

use App\Models\QuranMaster;
use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;
use RuntimeException;

class QuranJuzService
{
    private ?array $madinahPageData = null;

    private const STARTS = [
        1 => [1, 1], 2 => [2, 142], 3 => [2, 253], 4 => [3, 93], 5 => [4, 24],
        6 => [4, 148], 7 => [5, 82], 8 => [6, 111], 9 => [7, 88], 10 => [8, 41],
        11 => [9, 93], 12 => [11, 6], 13 => [12, 53], 14 => [15, 1], 15 => [17, 1],
        16 => [18, 75], 17 => [21, 1], 18 => [23, 1], 19 => [25, 21], 20 => [27, 56],
        21 => [29, 46], 22 => [33, 31], 23 => [36, 28], 24 => [39, 32], 25 => [41, 47],
        26 => [46, 1], 27 => [51, 31], 28 => [58, 1], 29 => [67, 1], 30 => [78, 1],
    ];

    /**
     * @return array<int, array{id: string, number: int, name: string, total: int, start: int, end: int}>
     */
    public function surahsForJuz(int $juz, Collection $surahs): array
    {
        if (! isset(self::STARTS[$juz])) {
            throw new InvalidArgumentException('Juz harus berada di antara 1 dan 30.');
        }

        [$startSurah, $startAyah] = self::STARTS[$juz];
        [$nextSurah, $nextAyah] = self::STARTS[$juz + 1] ?? [115, 1];
        $endSurah = $nextAyah === 1 ? $nextSurah - 1 : $nextSurah;
        $endAyah = $nextAyah === 1
            ? (int) $surahs->firstWhere('surah_number', $endSurah)?->total_ayats
            : $nextAyah - 1;
        $byNumber = $surahs->keyBy('surah_number');
        $items = [];

        for ($number = $startSurah; $number <= $endSurah; $number++) {
            $surah = $byNumber->get($number);
            if (! $surah instanceof QuranMaster) {
                throw new RuntimeException("Data surah nomor {$number} tidak ditemukan.");
            }

            $items[] = [
                'id' => $surah->id,
                'number' => (int) $surah->surah_number,
                'name' => $surah->surah_name,
                'total' => (int) $surah->total_ayats,
                'start' => $number === $startSurah ? $startAyah : 1,
                'end' => $number === $endSurah ? $endAyah : (int) $surah->total_ayats,
            ];
        }

        return $items;
    }

    /**
     * @return array<int, array<int, array{id: string, number: int, name: string, total: int, start: int, end: int}>>
     */
    public function allJuz(Collection $surahs): array
    {
        $juzMap = [];

        for ($juz = 1; $juz <= 30; $juz++) {
            $juzMap[$juz] = $this->surahsForJuz($juz, $surahs);
        }

        return $juzMap;
    }

    /**
     * @return array<int, array{number: int, segments: array<int, array{id: string, number: int, name: string, start: int, end: int}>}>
     */
    public function pagesForJuz(int $juz, Collection $surahs): array
    {
        if ($juz < 1 || $juz > 30) {
            throw new InvalidArgumentException('Juz harus berada di antara 1 dan 30.');
        }

        if ($this->madinahPageData === null) {
            // This bundled metadata maps Quran.com verse page numbers for the 604-page Madinah Mushaf.
            $path = resource_path('data/quran_mushaf_madinah_pages.json');
            $contents = file_get_contents($path);
            if ($contents === false) {
                throw new RuntimeException("Data halaman Mushaf Madinah tidak dapat dibaca: {$path}");
            }
            $this->madinahPageData = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        }

        $surahsByNumber = $surahs->keyBy('surah_number');
        $pages = $this->madinahPageData[(string) $juz] ?? null;

        if (! is_array($pages)) {
            throw new RuntimeException("Data halaman juz {$juz} tidak ditemukan.");
        }

        return array_map(function (array $page) use ($surahsByNumber): array {
            $segments = array_map(function (array $segment) use ($surahsByNumber): array {
                $surah = $surahsByNumber->get($segment['surah_number']);
                if (! $surah instanceof QuranMaster) {
                    throw new RuntimeException("Data surah nomor {$segment['surah_number']} tidak ditemukan.");
                }

                return [
                    'id' => $surah->id,
                    'number' => (int) $surah->surah_number,
                    'name' => $surah->surah_name,
                    'start' => (int) $segment['start'],
                    'end' => (int) $segment['end'],
                ];
            }, $page['segments']);

            return [
                'number' => (int) $page['number'],
                'segments' => $segments,
            ];
        }, $pages);
    }

    /**
     * @return array<int, array<int, array{number: int, segments: array<int, array{id: string, number: int, name: string, start: int, end: int}>}>>
     */
    public function allJuzPages(Collection $surahs): array
    {
        $juzPages = [];

        for ($juz = 1; $juz <= 30; $juz++) {
            $juzPages[$juz] = $this->pagesForJuz($juz, $surahs);
        }

        return $juzPages;
    }
}

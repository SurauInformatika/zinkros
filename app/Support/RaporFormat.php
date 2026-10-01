<?php

namespace App\Support;

use App\Models\RaporTemplate;
use Illuminate\Support\Str;

class RaporFormat
{
    /**
     * Skema blok rapor + properti bawaan.
     * 'list' => field yang isinya daftar (rows/columns/boxes) yang harus
     * diganti utuh (bukan digabung) saat normalisasi.
     */
    public const TYPES = [
        'kop' => [
            'label' => 'Kop Sekolah',
            'props' => [
                'show_logo' => true,
                'show_address' => true,
                'show_contact' => true,
                'logo' => '',
                'title_lines' => [],
                'title_sizes' => [],
                'title_font_size' => 15,
            ],
            'list' => ['title_lines', 'title_sizes'],
        ],
        'judul' => [
            'label' => 'Judul',
            'props' => [
                'lines' => [],
                'line_sizes' => [],
                'text' => 'LAPORAN HASIL BELAJAR PESERTA DIDIK',
                'font_size' => 16,
                'semester_size' => 12,
                'semester_text' => '',
                'show_semester' => true,
            ],
            'list' => ['lines', 'line_sizes'],
        ],
        'identitas' => [
            'label' => 'Identitas Siswa',
            'props' => [
                'left' => [
                    ['label' => 'Nama Lengkap', 'key' => 'name'],
                    ['label' => 'NISN / NIS', 'key' => 'nisn_nis'],
                    ['label' => 'Jenis Kelamin', 'key' => 'gender'],
                ],
                'right' => [
                    ['label' => 'Nama Sekolah', 'key' => 'school_name'],
                    ['label' => 'Kelas / Semester', 'key' => 'class_semester'],
                    ['label' => 'Tahun Pelajaran', 'key' => 'tahun'],
                ],
            ],
            'list' => ['left', 'right'],
        ],
        'section' => [
            'label' => 'Header Bagian',
            'props' => ['text' => 'Judul Bagian'],
            'list' => [],
        ],
        'tabel-nilai' => [
            'label' => 'Tabel Nilai',
            'props' => [
                'title' => 'A. Nilai Pengetahuan dan Keterampilan',
                'show_no' => true,
                'show_kkm' => true,
                'show_final' => true,
                'show_predikat' => true,
                'show_keterangan' => true,
                'grade_type_ids' => [],
                'custom_columns' => [],
            ],
            'list' => ['custom_columns', 'grade_type_ids'],
        ],
        'tabel-tahfidz' => [
            'label' => 'Tabel Tahfidz',
            'props' => [
                'title' => "B. Tahfidz Al-Qur'an",
                'show_surah' => true,
            ],
            'list' => [],
        ],
        'tabel-bebas' => [
            'label' => 'Tabel Kosong',
            'props' => [
                'title' => 'C. Ekstrakurikuler',
                'columns' => [
                    ['label' => 'No'],
                    ['label' => 'Kegiatan Ekstrakurikuler'],
                    ['label' => 'Predikat'],
                    ['label' => 'Keterangan'],
                ],
                'rows' => 2,
            ],
            'list' => ['columns'],
        ],
        'tabel-absensi' => [
            'label' => 'Tabel Ketidakhadiran',
            'props' => ['title' => 'D. Ketidakhadiran'],
            'list' => [],
        ],
        'catatan' => [
            'label' => 'Catatan Wali Kelas',
            'props' => ['title' => 'E. Catatan Wali Kelas', 'lines' => 3],
            'list' => [],
        ],
        'ttd' => [
            'label' => 'Tanda Tangan',
            'props' => [
                'boxes' => [
                    ['label' => 'Orang Tua / Wali', 'key' => 'ortu'],
                    ['label' => 'Wali Kelas', 'key' => 'wali'],
                    ['label' => 'Mengetahui, Kepala Sekolah', 'key' => 'kepsek'],
                ],
            ],
            'list' => ['boxes'],
        ],
        'teks-bebas' => [
            'label' => 'Teks Bebas',
            'props' => ['text' => '* Dokumen ini sah dicetak dari sistem SIT (Sistem Informasi Terpadu) sekolah.'],
            'list' => [],
        ],
    ];

    public static function labels(): array
    {
        return collect(self::TYPES)->map(fn ($t) => $t['label'])->all();
    }

    /**
     * Blok default = tata letak rapor standar yang lama (agar output cetak
     * saat belum ada template tetap sama persis).
     */
    public static function defaultBlocks(): array
    {
        $counter = 0;
        $id = function () use (&$counter) {
            return 'b'.++$counter;
        };

        $blocks = [
            ['id' => $id(), 'type' => 'kop', 'props' => self::defaults('kop')],
            ['id' => $id(), 'type' => 'judul', 'props' => self::defaults('judul')],
            ['id' => $id(), 'type' => 'identitas', 'props' => self::defaults('identitas')],
            ['id' => $id(), 'type' => 'tabel-nilai', 'props' => self::defaults('tabel-nilai')],
            ['id' => $id(), 'type' => 'tabel-tahfidz', 'props' => self::defaults('tabel-tahfidz')],
            ['id' => $id(), 'type' => 'tabel-bebas', 'props' => self::defaults('tabel-bebas')],
            ['id' => $id(), 'type' => 'tabel-absensi', 'props' => self::defaults('tabel-absensi')],
            ['id' => $id(), 'type' => 'catatan', 'props' => self::defaults('catatan')],
            ['id' => $id(), 'type' => 'ttd', 'props' => self::defaults('ttd')],
            ['id' => $id(), 'type' => 'teks-bebas', 'props' => self::defaults('teks-bebas')],
        ];

        foreach ($blocks as &$block) {
            if ($block['type'] === 'judul' && empty($block['props']['lines'])) {
                $block['props']['lines'] = ['LAPORAN HASIL BELAJAR PESERTA DIDIK'];
            }
        }

        return $blocks;
    }

    public static function defaults(string $type): array
    {
        return self::TYPES[$type]['props'] ?? [];
    }

    public static function listFields(string $type): array
    {
        return self::TYPES[$type]['list'] ?? [];
    }

    public static function newBlock(string $type): array
    {
        return [
            'id' => 'b'.Str::lower(Str::random(6)),
            'type' => isset(self::TYPES[$type]) ? $type : 'teks-bebas',
            'props' => self::defaults($type),
        ];
    }

    /**
     * Normalisasi blok dari input apa pun: hanya blok dengan tipe sah,
     * semua properti dijamin ada sesuai default.
     */
    public static function normalize(array $blocks): array
    {
        $out = [];

        foreach ($blocks as $block) {
            if (! is_array($block)) {
                continue;
            }

            $type = (string) ($block['type'] ?? '');
            if (! isset(self::TYPES[$type])) {
                continue;
            }

            $props = $block['props'] ?? [];
            $defaults = self::defaults($type);
            $list = self::listFields($type);

            if (! is_array($props)) {
                $props = [];
            }

            $merged = [];
            foreach ($defaults as $key => $value) {
                if (array_key_exists($key, $props)) {
                    $merged[$key] = in_array($key, $list, true) ? (array) $props[$key] : $props[$key];
                } else {
                    $merged[$key] = $value;
                }
            }
            foreach ($props as $key => $value) {
                if (! array_key_exists($key, $merged)) {
                    $merged[$key] = in_array($key, $list, true) ? (array) $value : $value;
                }
            }

            if ($type === 'identitas' && isset($merged['rows']) && is_array($merged['rows']) && $merged['rows'] !== []) {
                $legacy = array_values(array_filter($merged['rows'], 'is_array'));
                if (! isset($merged['left']) || $merged['left'] === []) {
                    $half = (int) ceil(count($legacy) / 2);
                    $merged['left'] = array_slice($legacy, 0, $half);
                    $merged['right'] = array_slice($legacy, $half);
                }
                unset($merged['rows']);
            }

            $out[] = [
                'id' => (string) ($block['id'] ?? 'b'.Str::lower(Str::random(6))),
                'type' => $type,
                'props' => $merged,
            ];
        }

        return $out;
    }

    /**
     * Template aktif sekolah (per tahun ajaran bila ditentukan), atau null.
     */
    public static function activeTemplate(string $schoolId, ?string $academicYearId = null): ?RaporTemplate
    {
        $query = RaporTemplate::where('school_id', $schoolId)->where('is_active', true);

        if ($academicYearId) {
            $query->where(function ($w) use ($academicYearId) {
                $w->where('academic_year_id', $academicYearId)->orWhereNull('academic_year_id');
            });
        }

        return $query->orderBy('academic_year_id')->orderByDesc('updated_at')->first();
    }
}
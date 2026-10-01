<?php

namespace App\Support;

use App\Models\AcademicCalendar;
use App\Models\CalendarClassOverride;
use App\Models\CalendarHoliday;
use App\Models\RecurringHoliday;
use Illuminate\Support\Str;

class KaldikDocument
{
    public AcademicCalendar $kaldik;

    public array $monthCells = [];

    public array $semesterBlocks = [];

    public array $allocation = [];

    public int $effectiveTeachingDays = 0;

    public array $holidayTypeLabels = [
        'nasional' => 'Libur Nasional',
        'daerah' => 'Libur Daerah',
        'sekolah' => 'Libur Sekolah',
        'rutin' => 'Libur Rutin',
    ];

    public array $hongTypeLabels = [
        'holiday' => 'Libur Nasional/Rutin',
        'exam' => 'Ujian / Penilaian',
        'activity' => 'Kegiatan / Agenda',
    ];

    public array $overrideLabels = [
        'orientation' => 'MOS / Orientasi',
        'exam' => 'Ujian',
        'graduation' => 'Wisuda',
        'digital_class' => 'Kelas Digital',
        'assessment' => 'Assessment',
        'teacher_training' => 'Pelatihan Guru',
        'field_trip' => 'FieldTrip',
        'outing_class' => 'Outing Class',
        'pekan_olahraga' => 'Pekan Olahraga',
        'validasi' => 'Validasi',
        'other' => 'Kegiatan Lainnya',
    ];

    protected array $weekName = [
        1 => 'senin', 2 => 'selasa', 3 => 'rabu', 4 => 'kamis', 5 => 'jumat', 6 => 'sabtu', 0 => 'ahad',
    ];

    public array $gridHeader = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Ahd'];

    public function __construct(AcademicCalendar $kaldik)
    {
        $this->kaldik = $kaldik;
        $this->buildMonths();
        $this->buildAllocation();
    }

    public function filename(): string
    {
        return 'KALDIK-' . Str::slug($this->kaldik->name) . '-' . Str::slug($this->kaldik->academicYear?->name ?? '') . '-' . Str::slug($this->kaldik->semesterLabel()) . '.pdf';
    }

    public function logoDataUri(): ?string
    {
        $school = $this->kaldik->school;
        if (!$school || !$school->logo) {
            return null;
        }
        $path = storage_path('app/public/' . $school->logo);
        if (!is_file($path)) {
            return null;
        }
        $mime = \Symfony\Component\Mime\MimeTypes::getDefault()->getMimeTypes(pathinfo($path, PATHINFO_EXTENSION))[0] ?? 'image/png';
        $data = file_get_contents($path);
        if ($data === false) {
            return null;
        }
        return 'data:' . $mime . ';base64,' . base64_encode($data);
    }

    public function statusLabel(): string
    {
        return match ($this->kaldik->status) {
            AcademicCalendar::STATUS_FINAL => 'FINAL',
            AcademicCalendar::STATUS_PENDING => 'MENUNGGU PERSETUJUAN',
            AcademicCalendar::STATUS_ARCHIVED => 'REVISI (DIARSIPKAN)',
            default => 'DRAFT',
        };
    }

    protected function learningDayNames(): array
    {
        return $this->kaldik->learningDays();
    }

    protected function buildMonths(): void
    {
        $start = $this->kaldik->start_date;
        $end = $this->kaldik->end_date;
        if (!$start || !$end || $end->lt($start)) {
            return;
        }

        $learningDays = $this->learningDayNames();

        $holidayMap = [];
        foreach ($this->kaldik->holidays as $h) {
            $holidayMap[$h->date->format('Y-m-d')] = $h;
        }

        $recurringMap = [];
        foreach (RecurringHoliday::forSchool($this->kaldik->school_id)->active()->get() as $rh) {
            $recurringMap[$rh->month . '-' . $rh->day] = $rh;
        }
        $recurringHolidayDates = [];
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $key = $d->month . '-' . $d->day;
            if (isset($recurringMap[$key])) {
                $recurringHolidayDates[$d->format('Y-m-d')] = $recurringMap[$key];
            }
        }

        $overrideWeeks = $this->overrideWeeks();

        $monthStart = $start->copy()->startOfMonth();
        $monthEnd = $end->copy()->startOfMonth();

        while ($monthStart->lte($monthEnd)) {
            $firstCell = $monthStart->copy()->startOfWeek(1);
            $lastDay = $monthStart->copy()->endOfMonth();
            $rows = [];
            $teaching = 0;
            $cursor = $firstCell->copy();
            while ($cursor->lte($lastDay)) {
                $row = [];
                for ($i = 0; $i < 7; $i++) {
                    $date = $cursor->copy()->addDays($i);
                    if ($date->month !== $monthStart->month || $date->year !== $monthStart->year) {
                        $row[] = null;
                        continue;
                    }
                    $cell = $this->cellFor($date, $learningDays, $holidayMap, $recurringHolidayDates, $overrideWeeks, $start, $end);
                    if (in_array('eff', $cell['classes'], true)) {
                        $teaching++;
                    }
                    $row[] = $cell;
                }
                $rows[] = $row;
                $cursor->addWeek();
            }

            $this->effectiveTeachingDays += $teaching;

            $recurringNotes = [];
            for ($d = $monthStart->copy(); $d->year === $monthStart->year && $d->month === $monthStart->month; $d->addDay()) {
                $dateKeyCheck = $d->format('Y-m-d');
                if (isset($recurringHolidayDates[$dateKeyCheck])) {
                    $recurringNotes[] = [\Illuminate\Support\Carbon::parse($dateKeyCheck), $recurringHolidayDates[$dateKeyCheck]];
                }
            }

            $notes = [];
            foreach ($this->kaldik->holidays as $h) {
                if ($h->date->year === $monthStart->year && $h->date->month === $monthStart->month) {
                    $notes[] = [
                        'sort' => $h->date->format('Y-m-d'),
                        'cls' => 'n-red',
                        'text' => $h->name,
                        'sub' => $h->date->format('d M') . " \u{00B7} " . ($this->holidayTypeLabels[$h->type] ?? $h->type),
                    ];
                }
            }
            foreach ($recurringNotes as [$noteDate, $rh]) {
                $notes[] = [
                    'sort' => $noteDate->format('Y-m-d'),
                    'cls' => 'n-red',
                    'text' => $rh->name,
                    'sub' => $noteDate->format('d M') . " \u{00B7} Libur Rutin",
                ];
            }
            foreach ($this->kaldik->classOverrides as $co) {
                $os = $co->start_date->copy()->startOfMonth();
                $oe = $co->end_date->copy()->startOfMonth();
                if ($monthStart->copy()->gte($os) && $monthStart->copy()->lte($oe)) {
                    $isExam = in_array($co->override_type, ['exam', 'assessment'], true);
                    $notes[] = [
                        'sort' => $co->start_date->format('Y-m-d'),
                        'cls' => $isExam ? 'n-green' : 'n-blue',
                        'text' => $co->title,
                        'sub' => $co->start_date->format('d M') . " \u{2013} " . $co->end_date->format('d M Y') . ($co->grade_level ? " \u{00B7} Kelas " . $co->grade_level : ''),
                    ];
                }
            }
            usort($notes, fn ($a, $b) => $a['sort'] <=> $b['sort']);

            $this->monthCells[] = [
                'label' => $monthStart->translatedFormat('F Y'),
                'month' => (int) $monthStart->month,
                'monthStart' => $monthStart->format('Y-m-d'),
                'teachingDays' => $teaching,
                'rows' => $rows,
                'notes' => $notes,
            ];
            $monthStart->addMonth();
        }

        $this->buildSemesterBlocks();
    }

    protected function buildSemesterBlocks(): void
    {
        $boundary = $this->kaldik->hasExplicitSemesterBoundaries()
            ? $this->kaldik->semester_2_start_date
            : null;

        $blocks = [];
        foreach ($this->monthCells as $month) {
            if ($boundary) {
                $monthStart = \Illuminate\Support\Carbon::parse($month['monthStart']);
                $semester = $monthStart->copy()->startOfMonth()->addDays(14)->gte($boundary) ? 'Genap' : 'Ganjil';
            } else {
                $semester = (($month['month'] >= 7 && $month['month'] <= 12) ? 'Ganjil' : 'Genap');
            }
            $key = 'Semester ' . $semester;
            if (empty($blocks) || $blocks[count($blocks) - 1]['label'] !== $key) {
                $blocks[] = ['label' => $key, 'teachingDays' => 0, 'months' => []];
            }
            $idx = count($blocks) - 1;
            $blocks[$idx]['teachingDays'] += $month['teachingDays'];
            $blocks[$idx]['months'][] = $month;
        }
        $this->semesterBlocks = $blocks;
    }

    protected function cellFor($date, array $learningDays, array $holidayMap, array $recurringHolidayDates, array $overrideWeeks, $start, $end): array
    {
        $weekday = $this->weekName[$date->dayOfWeek];
        $isLearning = isset($learningDays[$weekday]);
        $inPeriod = $date->between($start, $end);

        $classes = [];
        $tags = [];

        if (!$inPeriod) {
            return [
                'dom' => $date->format('j'),
                'classes' => ['dim'],
                'tags' => [],
            ];
        }

        if (!$isLearning) {
            $classes[] = 'libur';
            $tags[] = ['text' => 'Libur', 'cls' => 'tag-libur'];
        }

        $dateKey = $date->format('Y-m-d');

        if (isset($holidayMap[$dateKey])) {
            $h = $holidayMap[$dateKey];
            $classes[] = 'holiday';
            $label = $this->holidayTypeLabels[$h->type] ?? $h->type;
            $tags = [['text' => Str::limit($h->name, 18, "\u{2026}"), 'cls' => 'tag-holiday']];
        } elseif (isset($recurringHolidayDates[$dateKey])) {
            $rh = $recurringHolidayDates[$dateKey];
            $classes[] = 'holiday';
            $tags = [['text' => Str::limit($rh->name, 18, "\u{2026}"), 'cls' => 'tag-holiday']];
        }

        if ($isLearning) {
            $classes[] = 'eff';
            if (empty($tags)) {
                $tags[] = ['text' => 'Efektif', 'cls' => 'tag-eff'];
            }
        }

        foreach ($overrideWeeks as $ow) {
            if ($date->between($ow['start'], $ow['end'])) {
                $isExam = in_array($ow['type'], ['exam', 'assessment'], true);
                $classes[] = $isExam ? 'exam' : 'activity';
                $tags[] = [
                    'text' => ($ow['grade'] !== null ? 'Kls ' . $ow['grade'] . ' ' : '') . Str::limit($ow['title'], 14, "\u{2026}"),
                    'cls' => $isExam ? 'tag-exam' : 'tag-activity',
                ];
            }
        }

        return [
            'dom' => $date->format('j'),
            'classes' => array_values(array_unique($classes)),
            'tags' => array_slice($tags, 0, 2),
        ];
    }

    protected function overrideWeeks(): array
    {
        $list = [];
        foreach ($this->kaldik->classOverrides as $co) {
            $list[] = [
                'start' => $co->start_date,
                'end' => $co->end_date,
                'type' => $co->override_type,
                'title' => $co->title,
                'grade' => $co->grade_level,
            ];
        }
        return $list;
    }

    protected function buildAllocation(): void
    {
        $totalWeeks = $this->kaldik->totalWeeks();
        $effectiveWeeks = $this->kaldik->effectiveWeeks();
        $start = $this->kaldik->start_date;
        $end = $this->kaldik->end_date;

        $examWeeks = [];
        $activityWeeks = [];
        if ($start && $end && $end->gte($start)) {
            foreach ($this->overrideWeeks() as $ow) {
                $from = $ow['start']->max($start);
                $to = $ow['end']->min($end);
                if ($to->lt($from)) {
                    continue;
                }
                $isExam = in_array($ow['type'], ['exam', 'assessment'], true);
                $base = $start->copy()->startOfWeek(1);
                for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
                    $week = (int) floor($base->diffInDays($d->copy()->startOfWeek(1)) / 7);
                    if ($isExam) {
                        $examWeeks[$week] = true;
                    } else {
                        $activityWeeks[$week] = true;
                    }
                }
            }
        }

        $exam = count($examWeeks);
        $activity = count($activityWeeks);
        $intra = max(0, $effectiveWeeks - $exam - $activity);

        $this->allocation = [
            ['label' => 'Intrakurikuler & pembiasaan', 'detail' => 'Minggu pembelajaran reguler efektif', 'weeks' => $intra],
            ['label' => 'Penilaian (PTS/PAS/US & sejenisnya)', 'detail' => 'Menyesuaikan agenda kegiatan', 'weeks' => $exam],
            ['label' => 'Kegiatan khusus (MOS, wisuda, dll.)', 'detail' => 'Menyesuaikan agenda kegiatan', 'weeks' => $activity],
            ['label' => 'Minggu tidak efektif', 'detail' => 'Libur & kelonggaran', 'weeks' => max(0, $totalWeeks - $effectiveWeeks)],
        ];
    }

    public function totalAllocatedWeeks(): int
    {
        return array_sum(array_column($this->allocation, 'weeks'));
    }
}
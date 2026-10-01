<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\QuranMaster;
use App\Models\QuranTeachingAssignment;
use App\Models\TahfidzRecord;
use App\Services\AcademicYearContext;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuranAbsensiController extends Controller
{
    public function __construct(protected AcademicYearContext $academicYearContext) {}

    public function rekap(Request $request): View
    {
        $user = auth()->user();
        $ayId = $this->academicYearContext->id();

        $assignments = QuranTeachingAssignment::where('teacher_id', $user->id)
            ->whereHas('student')
            ->when($ayId, fn ($q) => $q->where(function ($q2) use ($ayId) {
                $q2->where('academic_year_id', $ayId)
                    ->orWhereNull('academic_year_id');
            }))
            ->with('student.classRoom')
            ->get();

        $studentIds = $assignments->pluck('student_id');
        $totalStudents = $studentIds->count();

        $dateFrom = $request->query('date_from', Carbon::now()->startOfMonth()->toDateString());
        $dateTo = $request->query('date_to', Carbon::today()->toDateString());

        $records = TahfidzRecord::where('teacher_id', $user->id)
            ->whereIn('student_id', $studentIds)
            ->where('recorded_date', '>=', $dateFrom)
            ->where('recorded_date', '<=', $dateTo)
            ->get();

        $recordsByDate = $records->groupBy('recorded_date');

        $sessions = collect();
        $current = Carbon::parse($dateFrom);
        $end = Carbon::parse($dateTo);

        while ($current->lte($end)) {
            $key = $current->toDateString();
            $dayRecords = $recordsByDate->get($key, collect());

            $isLibur = $dayRecords->isEmpty();

            $hadir = $dayRecords->where('status', null)->count();
            $sakit = $dayRecords->where('status', 'SAKIT')->count();
            $izin = $dayRecords->where('status', 'IZIN')->count();
            $alpa = $isLibur ? 0 : $totalStudents - $hadir - $sakit - $izin;
            if ($alpa < 0) $alpa = 0;

            $total = $totalStudents;
            $sessions->push([
                'date' => $key,
                'total' => $total,
                'total_records' => $dayRecords->count(),
                'is_empty' => $isLibur,
                'is_libur' => $isLibur,
                'hadir' => $hadir,
                'sakit' => $sakit,
                'izin' => $izin,
                'alpa' => $alpa,
                'persentase' => $total > 0 ? round($hadir / $total * 100, 1) : 0,
            ]);

            $current->addDay();
        }

        $sessions = $sessions->sortByDesc('date')->values();

        $chartSessions = $sessions->reverse()->values();
        $chartLabels = $chartSessions->pluck('date')->values();
        $chartHadir = $chartSessions->pluck('hadir')->values();
        $chartSakit = $chartSessions->pluck('sakit')->values();
        $chartIzin = $chartSessions->pluck('izin')->values();
        $chartAlpa = $chartSessions->pluck('alpa')->values();
        $chartIsEmpty = $chartSessions->pluck('is_empty')->values();
        $chartTitle = 'Grafik Kehadiran Al-Quran — ' . $totalStudents . ' Siswa';

        return view('guru.quran-absensi.rekap', compact(
            'sessions', 'dateFrom', 'dateTo', 'totalStudents',
            'chartLabels', 'chartHadir', 'chartSakit', 'chartIzin', 'chartAlpa', 'chartIsEmpty', 'chartTitle'
        ));
    }

    public function detail(string $date): View
    {
        $user = auth()->user();
        $ayId = $this->academicYearContext->id();

        $assignments = QuranTeachingAssignment::where('teacher_id', $user->id)
            ->when($ayId, fn ($q) => $q->where(function ($q2) use ($ayId) {
                $q2->where('academic_year_id', $ayId)
                    ->orWhereNull('academic_year_id');
            }))
            ->with('student.classRoom')
            ->get();

        $studentIds = $assignments->pluck('student_id');
        $totalStudents = $studentIds->count();

        $records = TahfidzRecord::where('teacher_id', $user->id)
            ->whereIn('student_id', $studentIds)
            ->where('recorded_date', $date)
            ->with('quranMaster')
            ->get()
            ->keyBy('student_id');

        $students = $assignments->map(function ($a) use ($records) {
            $record = $records->get($a->student_id);
            $status = 'ALPA';
            $hafalan = null;
            $score = null;
            $predikat = null;

            if ($record) {
                $status = TahfidzRecord::attendanceStatus($record->status);
                if ($status === 'HADIR' && $record->quran_master_id) {
                    $hafalan = $record->quranMaster->surah_name . ' ' . $record->ayat_start . '-' . $record->ayat_end;
                    $score = $record->score;
                    $predikat = TahfidzRecord::scoreToPredikat($record->score);
                }
            }

            return [
                'id' => $a->student_id,
                'name' => $a->student->name ?? '-',
                'class' => $a->student->classRoom?->class_name ?? '-',
                'status' => $status,
                'hafalan' => $hafalan,
                'score' => $score,
                'predikat' => $predikat,
            ];
        })->sortBy('name')->values();

        $hadir = $students->where('status', 'HADIR')->count();
        $sakit = $students->where('status', 'SAKIT')->count();
        $izin = $students->where('status', 'IZIN')->count();
        $alpa = $students->where('status', 'ALPA')->count();
        $persentase = $totalStudents > 0 ? round($hadir / $totalStudents * 100, 1) : 0;

        return view('guru.quran-absensi.detail', compact(
            'date', 'students', 'totalStudents',
            'hadir', 'sakit', 'izin', 'alpa', 'persentase'
        ));
    }

    public function studentDetail(string $date, string $studentId): View
    {
        $user = auth()->user();
        $ayId = $this->academicYearContext->id();

        $assignment = QuranTeachingAssignment::where('teacher_id', $user->id)
            ->where('student_id', $studentId)
            ->when($ayId, fn ($q) => $q->where(function ($q2) use ($ayId) {
                $q2->where('academic_year_id', $ayId)
                    ->orWhereNull('academic_year_id');
            }))
            ->with('student.classRoom')
            ->firstOrFail();

        $student = $assignment->student;

        $record = TahfidzRecord::where('teacher_id', $user->id)
            ->where('student_id', $studentId)
            ->where('recorded_date', $date)
            ->with('quranMaster')
            ->first();

        $status = 'ALPA';
        $hafalan = null;
        $ayatRange = null;
        $score = null;
        $predikat = null;
        $activityType = null;
        $notes = null;

        if ($record) {
            $status = TahfidzRecord::attendanceStatus($record->status);
            $notes = $record->notes;
            if ($status === 'HADIR' && $record->quran_master_id) {
                $hafalan = $record->quranMaster->surah_number . '. ' . $record->quranMaster->surah_name;
                $ayatRange = $record->ayat_start . ' — ' . $record->ayat_end;
                $score = $record->score;
                $predikat = TahfidzRecord::scoreToPredikat($record->score);
                $activityType = $record->activity_type;
            }
        }

        $allRecords = TahfidzRecord::where('teacher_id', $user->id)
            ->where('student_id', $studentId)
            ->with('quranMaster')
            ->orderByDesc('recorded_date')
            ->limit(20)
            ->get();

        $stats = TahfidzRecord::where('teacher_id', $user->id)
            ->where('student_id', $studentId)
            ->with('quranMaster')
            ->get();

        $ziadahAyat = $stats->where('activity_type', TahfidzRecord::TYPE_ZIADAH)->sum(fn ($r) => $r->ayat_end - $r->ayat_start + 1);
        $murajaahAyat = $stats->where('activity_type', TahfidzRecord::TYPE_MURAJAAH)->sum(fn ($r) => $r->ayat_end - $r->ayat_start + 1);

        $statsArr = null;
        if ($stats->isNotEmpty()) {
            $maxAyatPerSurah = $stats->groupBy('quran_master_id')
                ->map(fn ($r) => $r->max('ayat_end'));
            $totalAyatsMap = QuranMaster::whereIn('id', $maxAyatPerSurah->keys())
                ->pluck('total_ayats', 'id');

            $hafalNames = collect();
            $progressList = collect();
            foreach ($maxAyatPerSurah as $surahId => $maxAyat) {
                $total = $totalAyatsMap->get($surahId, 0);
                $surah = $stats->firstWhere('quran_master_id', $surahId)?->quranMaster;
                if (! $surah) continue;
                if ($maxAyat >= $total) {
                    $hafalNames->push($surah->surah_name);
                } else {
                    $progressList->push([
                        'name' => $surah->surah_name,
                        'current' => $maxAyat,
                        'total' => $total,
                    ]);
                }
            }

            $ziadahAyat = $stats->where('activity_type', TahfidzRecord::TYPE_ZIADAH)->sum(fn ($r) => $r->ayat_end - $r->ayat_start + 1);
            $murajaahAyat = $stats->where('activity_type', TahfidzRecord::TYPE_MURAJAAH)->sum(fn ($r) => $r->ayat_end - $r->ayat_start + 1);

            $statsArr = [
                'total' => $stats->count(),
                'ziadah_ayat' => $ziadahAyat,
                'murajaah_ayat' => $murajaahAyat,
                'surahs_hafal' => $hafalNames->count(),
                'surahs_progress' => $progressList->count(),
                'avg_score' => round($stats->avg('score') ?? 0, 1),
                'hafal_names' => $hafalNames->unique()->values(),
                'progress_list' => $progressList->values(),
            ];
        }

        return view('guru.quran-absensi.student-detail', compact(
            'date', 'student', 'status', 'hafalan', 'ayatRange', 'score',
            'predikat', 'activityType', 'notes', 'record',
            'allRecords', 'statsArr'
        ));
    }

    public function studentChartData(Request $request, string $studentId): JsonResponse
    {
        $user = auth()->user();
        $ayId = $this->academicYearContext->id();
        $filter = $request->query('filter', 'ta_init');

        $now = Carbon::now();
        $query = TahfidzRecord::where('teacher_id', $user->id)
            ->where('student_id', $studentId)
            ->with('quranMaster');

        switch ($filter) {
            case 'pekan':
                $start = $now->copy()->subWeek()->startOfDay();
                $end = $now->copy()->endOfDay();
                break;
            case 'pekan_lalu':
                $start = $now->copy()->subWeeks(2)->startOfDay();
                $end = $now->copy()->subWeek()->endOfDay();
                break;
            case 'bulan':
                $start = $now->copy()->subMonth()->startOfDay();
                $end = $now->copy()->endOfDay();
                break;
            case 'bulan_lalu':
                $start = $now->copy()->subMonths(2)->startOfDay();
                $end = $now->copy()->subMonth()->endOfDay();
                break;
            case 'semester':
                $start = $now->copy()->subMonths(6)->startOfDay();
                $end = $now->copy()->endOfDay();
                break;
            case 'semester_lalu':
                $start = $now->copy()->subMonths(12)->startOfDay();
                $end = $now->copy()->subMonths(6)->endOfDay();
                break;
            case 'ta_init':
                $start = $now->copy()->startOfYear();
                $end = $now->copy()->endOfYear();
                break;
            case 'ta_lalu':
                $start = $now->copy()->subYear()->startOfYear();
                $end = $now->copy()->subYear()->endOfYear();
                break;
            default:
                $start = null;
                $end = null;
        }

        if ($start && $end) {
            $query->whereBetween('recorded_date', [$start, $end]);
        }

        $records = $query->orderBy('recorded_date')->get();

        $dates = $records->groupBy(fn ($r) => Carbon::parse($r->recorded_date)->format('Y-m-d'));
        $chartLabels = [];
        $chartAyatZiadah = [];
        $chartAyatMurajaah = [];
        $chartZiadah = [];
        $chartMurajaah = [];
        $chartScores = [];

        if ($start && $end) {
            $current = $start->copy()->startOfDay();
            while ($current->lte($end)) {
                $key = $current->format('Y-m-d');
                $chartLabels[] = $current->format('d M');
                $dayRecords = $dates->get($key, collect());
                $chartAyatZiadah[] = $dayRecords->where('activity_type', TahfidzRecord::TYPE_ZIADAH)->sum(fn ($r) => $r->ayat_end - $r->ayat_start + 1);
                $chartAyatMurajaah[] = $dayRecords->where('activity_type', TahfidzRecord::TYPE_MURAJAAH)->sum(fn ($r) => $r->ayat_end - $r->ayat_start + 1);
                $chartZiadah[] = $dayRecords->where('activity_type', TahfidzRecord::TYPE_ZIADAH)->count();
                $chartMurajaah[] = $dayRecords->where('activity_type', TahfidzRecord::TYPE_MURAJAAH)->count();
                $dayScores = $dayRecords->pluck('score')->filter()->values();
                $chartScores[] = $dayScores->isNotEmpty() ? round($dayScores->avg(), 1) : null;
                $current->addDay();
            }
        } else {
            foreach ($dates as $date => $dayRecords) {
                $chartLabels[] = Carbon::parse($date)->format('d M');
                $chartAyatZiadah[] = $dayRecords->where('activity_type', TahfidzRecord::TYPE_ZIADAH)->sum(fn ($r) => $r->ayat_end - $r->ayat_start + 1);
                $chartAyatMurajaah[] = $dayRecords->where('activity_type', TahfidzRecord::TYPE_MURAJAAH)->sum(fn ($r) => $r->ayat_end - $r->ayat_start + 1);
                $chartZiadah[] = $dayRecords->where('activity_type', TahfidzRecord::TYPE_ZIADAH)->count();
                $chartMurajaah[] = $dayRecords->where('activity_type', TahfidzRecord::TYPE_MURAJAAH)->count();
                $dayScores = $dayRecords->pluck('score')->filter()->values();
                $chartScores[] = $dayScores->isNotEmpty() ? round($dayScores->avg(), 1) : null;
            }
        }

        $ziadahAyat = $records->where('activity_type', TahfidzRecord::TYPE_ZIADAH)->sum(fn ($r) => $r->ayat_end - $r->ayat_start + 1);
        $murajaahAyat = $records->where('activity_type', TahfidzRecord::TYPE_MURAJAAH)->sum(fn ($r) => $r->ayat_end - $r->ayat_start + 1);

        return response()->json([
            'labels' => $chartLabels,
            'ayat_ziadah' => $chartAyatZiadah,
            'ayat_murajaah' => $chartAyatMurajaah,
            'ziadah' => $chartZiadah,
            'murajaah' => $chartMurajaah,
            'scores' => $chartScores,
            'stats' => [
                'total' => $records->count(),
                'total_ayat' => $ziadahAyat + $murajaahAyat,
                'ziadah_ayat' => $ziadahAyat,
                'murajaah_ayat' => $murajaahAyat,
                'ziadah' => $records->where('activity_type', TahfidzRecord::TYPE_ZIADAH)->count(),
                'murajaah' => $records->where('activity_type', TahfidzRecord::TYPE_MURAJAAH)->count(),
                'avg_score' => round($records->avg('score') ?? 0, 1),
                'surahs' => $records->pluck('quran_master_id')->unique()->count(),
            ],
        ]);
    }

    public function chartData(Request $request): JsonResponse
    {
        $user = auth()->user();
        $ayId = $this->academicYearContext->id();
        $filter = $request->query('filter', 'bulan');

        $now = Carbon::now();
        $ranges = match ($filter) {
            'pekan' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'pekan_lalu' => [$now->copy()->subWeek()->startOfWeek(), $now->copy()->subWeek()->endOfWeek()],
            'bulan' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            'bulan_lalu' => [$now->copy()->subMonth()->startOfMonth(), $now->copy()->subMonth()->endOfMonth()],
            'semester' => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            'semester_lalu' => [$now->copy()->subYear()->startOfYear(), $now->copy()->subYear()->endOfYear()],
            'ta_init' => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            'ta_lalu' => [$now->copy()->subYear()->startOfYear(), $now->copy()->subYear()->endOfYear()],
            default => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
        };

        $dateFrom = $ranges[0]->toDateString();
        $dateTo = $ranges[1]->toDateString();

        $assignments = QuranTeachingAssignment::where('teacher_id', $user->id)
            ->when($ayId, fn ($q) => $q->where(function ($q2) use ($ayId) {
                $q2->where('academic_year_id', $ayId)
                    ->orWhereNull('academic_year_id');
            }))
            ->get();

        $studentIds = $assignments->pluck('student_id');
        $totalStudents = $studentIds->count();

        $records = TahfidzRecord::where('teacher_id', $user->id)
            ->whereIn('student_id', $studentIds)
            ->where('recorded_date', '>=', $dateFrom)
            ->where('recorded_date', '<=', $dateTo)
            ->get();

        $recordsByDate = $records->groupBy('recorded_date');

        $labels = [];
        $hadir = [];
        $sakit = [];
        $izin = [];
        $alpa = [];
        $is_empty = [];

        $current = Carbon::parse($dateFrom);
        $end = Carbon::parse($dateTo);

        while ($current->lte($end)) {
            $key = $current->toDateString();
            $dayRecords = $recordsByDate->get($key, collect());
            $isEmpty = $dayRecords->isEmpty();

            $labels[] = $current->translatedFormat('d M');
            $is_empty[] = $isEmpty;

            if ($isEmpty) {
                $hadir[] = 0;
                $sakit[] = 0;
                $izin[] = 0;
                $alpa[] = 0;
            } else {
                $h = $dayRecords->where('status', null)->count();
                $s = $dayRecords->where('status', 'SAKIT')->count();
                $i = $dayRecords->where('status', 'IZIN')->count();
                $a = $totalStudents - $h - $s - $i;
                if ($a < 0) $a = 0;

                $hadir[] = $h;
                $sakit[] = $s;
                $izin[] = $i;
                $alpa[] = $a;
            }

            $current->addDay();
        }

        return response()->json([
            'labels' => $labels,
            'hadir' => $hadir,
            'sakit' => $sakit,
            'izin' => $izin,
            'alpa' => $alpa,
            'is_empty' => $is_empty,
        ]);
    }
}

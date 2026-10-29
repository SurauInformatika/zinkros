<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\ClassRoom;
use App\Models\QuranMaster;
use App\Models\Student;
use App\Models\StudentQuranTarget;
use App\Models\StudentQuranTargetItem;
use App\Models\TahfidzRecord;
use App\Models\User;
use App\Services\AcademicYearContext;
use App\Services\QuranTargetService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TahfidzController extends Controller
{
    public function __construct(
        protected AcademicYearContext $academicYearContext,
        protected QuranTargetService $quranTargetService
    ) {
    }

    protected function routeGroup(): string
    {
        $name = (string) request()->route()?->getName();

        return str_contains($name, '.') ? substr($name, 0, strrpos($name, '.')) : $name;
    }

    public function index(Request $request): View
    {
        $user = auth()->user();
        $schoolId = $user->school_id;

        $classes = ClassRoom::where('school_id', $schoolId)
            ->withCount('students')
            ->orderBy('grade_level')
            ->orderBy('class_name')
            ->get();

        $classId = $request->query('class_id');

        $students = collect();
        $studentStats = collect();
        $stats = null;
        $selectedClass = null;
        $studentTargets = collect();

        if ($classId) {
            $selectedClass = ClassRoom::where('id', $classId)
                ->where('school_id', $schoolId)
                ->first();

            if ($selectedClass) {
                $students = Student::where('class_id', $classId)
                    ->with(['quranTargets' => fn ($q) => $q->with('items.quranMaster')])
                    ->orderBy('name')
                    ->get();

                $query = TahfidzRecord::where('school_id', $schoolId)
                    ->whereHas('student', fn ($q) => $q->where('class_id', $classId));

                if ($this->academicYearContext->exists()) {
                    $query->where('academic_year_id', $this->academicYearContext->id());
                }

                $allRecords = $query->with(['quranMaster', 'teacher'])->get();

                if ($allRecords->isNotEmpty()) {
                    $stats = [
                        'total' => $allRecords->count(),
                        'ziadah' => $allRecords->where('activity_type', TahfidzRecord::TYPE_ZIADAH)->count(),
                        'murajaah' => $allRecords->where('activity_type', TahfidzRecord::TYPE_MURAJAAH)->count(),
                    'avg_score' => round($allRecords->avg('score'), 1),
                        'unique_surahs' => $allRecords->pluck('quran_master_id')->unique()->count(),
                        'total_ayat' => $allRecords->sum(fn ($r) => $r->ayat_end - $r->ayat_start + 1),
                    ];
                }

                $studentStats = $allRecords->groupBy('student_id')->map(function ($records) {
                    $student = $records->first()->student;
                    $totalAyat = $records->sum(fn ($r) => $r->ayat_end - $r->ayat_start + 1);
                    $surahs = $records->pluck('quran_master_id')->unique()->count();
                    $latestDate = $records->max('recorded_date');
                    return [
                        'name' => $student?->name ?? '-',
                        'total' => $records->count(),
                        'ziadah' => $records->where('activity_type', TahfidzRecord::TYPE_ZIADAH)->count(),
                        'murajaah' => $records->where('activity_type', TahfidzRecord::TYPE_MURAJAAH)->count(),
                        'total_ayat' => $totalAyat,
                        'surahs' => $surahs,
                        'avg_score' => round($records->avg('score'), 1),
                        'latest_date' => $latestDate,
                    ];
                });

                $studentTargets = $students->mapWithKeys(function ($student) {
                    $activeTargets = $student->quranTargets->filter(fn ($t) => $t->is_active);
                    $decorated = $activeTargets->isNotEmpty()
                        ? $this->quranTargetService->decorateTarget($activeTargets->sortByDesc('target_date')->first())
                        : null;
                    return [$student->id => $decorated];
                });
            }
        }

        return view('admin.tahfidz.index', compact(
            'classes', 'classId', 'selectedClass', 'students', 'studentStats', 'stats', 'studentTargets'
        ))->with('routeGroup', $this->routeGroup());
    }

    public function student(Request $request, Student $student): View
    {
        $user = auth()->user();

        if ($student->school_id !== $user->school_id) {
            abort(403);
        }

        $query = TahfidzRecord::where('student_id', $student->id)
            ->with(['quranMaster', 'teacher']);

        if ($this->academicYearContext->exists()) {
            $query->where('academic_year_id', $this->academicYearContext->id());
        }

        $records = $query->orderByDesc('recorded_date')
            ->orderByDesc('created_at')
            ->get();

        $stats = null;
        if ($records->isNotEmpty()) {
            $maxAyatPerSurah = $records->groupBy('quran_master_id')
                ->map(fn ($r) => $r->max('ayat_end'));
            $totalAyatsMap = \App\Models\QuranMaster::whereIn('id', $maxAyatPerSurah->keys())
                ->pluck('total_ayats', 'id');

            $hafalNames = collect();
            $progressList = collect();
            foreach ($maxAyatPerSurah as $surahId => $maxAyat) {
                $total = $totalAyatsMap->get($surahId, 0);
                $surah = $records->firstWhere('quran_master_id', $surahId)?->quranMaster;
                if (!$surah) continue;
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

            $ziadahAyat = $records->where('activity_type', TahfidzRecord::TYPE_ZIADAH)->sum(fn ($r) => $r->ayat_end - $r->ayat_start + 1);
            $murajaahAyat = $records->where('activity_type', TahfidzRecord::TYPE_MURAJAAH)->sum(fn ($r) => $r->ayat_end - $r->ayat_start + 1);

            $stats = [
                'total' => $records->count(),
                'ziadah' => $records->where('activity_type', TahfidzRecord::TYPE_ZIADAH)->count(),
                'murajaah' => $records->where('activity_type', TahfidzRecord::TYPE_MURAJAAH)->count(),
                'total_ayat' => $ziadahAyat + $murajaahAyat,
                'ziadah_ayat' => $ziadahAyat,
                'murajaah_ayat' => $murajaahAyat,
                'surahs_hafal' => $hafalNames->count(),
                'surahs_progress' => $progressList->count(),
                'surahs' => $hafalNames->count() + $progressList->count(),
                'avg_score' => round($records->avg('score'), 1),
                'hafal_names' => $hafalNames->unique()->values(),
                'progress_list' => $progressList->values(),
            ];
        }

        $student->load('classRoom');
        $targets = $this->buildTargetsForStudent($student->id);

        return view('admin.tahfidz.student', compact('student', 'records', 'stats', 'targets'))->with('routeGroup', $this->routeGroup());
    }

    private function buildTargetsForStudent(string $studentId): array
    {
        $targets = StudentQuranTarget::where('student_id', $studentId)
            ->with(['items.quranMaster'])
            ->orderByDesc('is_active')
            ->orderBy('target_date')
            ->get();

        $result = [];

        foreach ($targets as $target) {
            $items = $target->items->sortBy(function ($item) {
                return $item->quranMaster?->surah_number ?? 0;
            })->values();

            $totalAyat = 0;
            $completedAyat = 0;

            $decoratedItems = [];

            foreach ($items as $item) {
                $from = (int) $item->ayat_start;
                $to = (int) $item->ayat_end;
                $itemTotal = max(0, $to - $from + 1);
                $totalAyat += $itemTotal;

                $maxAyat = TahfidzRecord::where('student_id', $studentId)
                    ->where('quran_master_id', $item->quran_master_id)
                    ->where('activity_type', TahfidzRecord::TYPE_ZIADAH)
                    ->max('ayat_end');

                $itemCompleted = 0;
                if ($maxAyat !== null) {
                    if ((int) $maxAyat >= $to) {
                        $itemCompleted = $itemTotal;
                    } elseif ((int) $maxAyat >= $from) {
                        $itemCompleted = max(0, (int) $maxAyat - $from + 1);
                    }
                }
                $completedAyat += $itemCompleted;

                $decoratedItems[] = [
                    'surah_name' => $item->quranMaster?->surah_name ?? '-',
                    'surah_number' => $item->quranMaster?->surah_number,
                    'from_ayat' => $from,
                    'to_ayat' => $to,
                    'total' => $itemTotal,
                    'completed' => $itemCompleted,
                    'percent' => $itemTotal > 0 ? round(($itemCompleted / $itemTotal) * 100) : 0,
                ];
            }

            $percent = $totalAyat > 0 ? round(($completedAyat / $totalAyat) * 100) : 0;
            $targetDate = $target->target_date ? Carbon::parse($target->target_date) : null;
            $daysRemaining = $targetDate ? Carbon::now()->startOfDay()->diffInDays($targetDate->startOfDay(), false) : null;

            if ($percent >= 100) {
                $status = 'Tercapai';
            } elseif ($daysRemaining !== null && $daysRemaining < 0) {
                $status = 'Terlambat';
            } elseif ($daysRemaining !== null && $daysRemaining <= 7) {
                $status = 'Hampir Berakhir';
            } else {
                $status = 'Berjalan';
            }

            $result[] = [
                'id' => $target->id,
                'title' => $target->title,
                'target_date' => $targetDate?->format('Y-m-d'),
                'target_date_label' => $targetDate?->format('d M Y'),
                'days_remaining' => $daysRemaining,
                'status' => $status,
                'percent' => $percent,
                'total_ayat' => $totalAyat,
                'completed_ayat' => $completedAyat,
                'items' => $decoratedItems,
                'notes' => $target->notes,
                'is_active' => $target->is_active,
            ];
        }

        return $result;
    }

    public function chart(Request $request, Student $student): JsonResponse
    {
        $user = auth()->user();

        if ($student->school_id !== $user->school_id) {
            abort(403);
        }

        $filter = $request->query('filter', 'ta_init');
        $now = Carbon::now();

        $query = TahfidzRecord::where('student_id', $student->id)
            ->with(['quranMaster']);

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
                $ay = $this->academicYearContext->get();
                if ($ay) {
                    $query->where('academic_year_id', $ay->id);
                }
                $start = null;
                $end = null;
                break;
            case 'ta_lalu':
                $currentAy = $this->academicYearContext->get();
                if ($currentAy) {
                    $prevAy = AcademicYear::where('school_id', $user->school_id)
                        ->where('start_date', '<', $currentAy->start_date)
                        ->orderByDesc('start_date')
                        ->first();
                    if ($prevAy) {
                        $query->where('academic_year_id', $prevAy->id);
                    } else {
                        $query->whereRaw('0=1');
                    }
                } else {
                    $query->whereRaw('0=1');
                }
                $start = null;
                $end = null;
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
                $current->addDay();
            }
        } else {
            foreach ($dates as $date => $dayRecords) {
                $chartLabels[] = Carbon::parse($date)->format('d M');
                $chartAyatZiadah[] = $dayRecords->where('activity_type', TahfidzRecord::TYPE_ZIADAH)->sum(fn ($r) => $r->ayat_end - $r->ayat_start + 1);
                $chartAyatMurajaah[] = $dayRecords->where('activity_type', TahfidzRecord::TYPE_MURAJAAH)->sum(fn ($r) => $r->ayat_end - $r->ayat_start + 1);
                $chartZiadah[] = $dayRecords->where('activity_type', TahfidzRecord::TYPE_ZIADAH)->count();
                $chartMurajaah[] = $dayRecords->where('activity_type', TahfidzRecord::TYPE_MURAJAAH)->count();
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
            'stats' => [
                'total' => $records->count(),
                'total_ayat' => $ziadahAyat + $murajaahAyat,
                'ziadah_ayat' => $ziadahAyat,
                'murajaah_ayat' => $murajaahAyat,
                'ziadah' => $records->where('activity_type', TahfidzRecord::TYPE_ZIADAH)->count(),
                'murajaah' => $records->where('activity_type', TahfidzRecord::TYPE_MURAJAAH)->count(),
                'avg_score' => round($records->avg('score'), 1),
                'surahs' => $records->pluck('quran_master_id')->unique()->count(),
            ],
        ]);
    }
}

<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\ClassRoom;
use App\Models\ClassSubjectTeacher;
use App\Models\QuranMaster;
use App\Models\Student;
use App\Models\Subject;
use App\Models\TahfidzRecord;
use App\Services\AcademicYearContext;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TahfidzController extends Controller
{
    public function __construct(protected AcademicYearContext $academicYearContext)
    {
    }

    public function index(): View
    {
        $user = auth()->user();

        $plottedClasses = ClassSubjectTeacher::where('teacher_id', $user->id)
            ->whereHas('subject', fn ($q) => $q->where('type', Subject::TYPE_QURAN))
            ->with(['classRoom', 'subject'])
            ->get()
            ->groupBy('class_id')
            ->map(function ($items) {
                $class = $items->first()->classRoom;
                return [
                    'class' => $class,
                    'subjects' => $items->pluck('subject')->keyBy('id'),
                ];
            })
            ->values();

        $today = Carbon::today()->toDateString();

        $recentRecords = TahfidzRecord::where('teacher_id', $user->id)
            ->where('recorded_date', $today)
            ->with(['student', 'quranMaster'])
            ->get()
            ->groupBy('student_id')
            ->map(function ($items) {
                $student = $items->first()->student;
                return [
                    'student_name' => $student?->name ?? '-',
                    'count' => $items->count(),
                    'ziadah' => $items->where('activity_type', TahfidzRecord::TYPE_ZIADAH)->count(),
                    'murajaah' => $items->where('activity_type', TahfidzRecord::TYPE_MURAJAAH)->count(),
                ];
            })
            ->values();

        return view('guru.tahfidz.index', compact('plottedClasses', 'recentRecords', 'today'));
    }

    public function create(Request $request): View
    {
        $user = auth()->user();
        $classId = $request->query('class_id');
        $subjectId = $request->query('subject_id');

        if (!$classId || !$subjectId) {
            return redirect()->route('guru.tahfidz.index')
                ->with('error', 'Pilih kelas dan mata pelajaran terlebih dahulu.');
        }

        $plot = ClassSubjectTeacher::where('teacher_id', $user->id)
            ->where('class_id', $classId)
            ->where('subject_id', $subjectId)
            ->first();

        if (!$plot) {
            return redirect()->route('guru.tahfidz.index')
                ->with('error', 'Anda tidak terploting untuk kelas dan mata pelajaran ini.');
        }

        $classRoom = \App\Models\ClassRoom::findOrFail($classId);
        $subject = Subject::findOrFail($subjectId);
        $students = Student::where('class_id', $classId)->orderBy('name')->get();
        $date = $request->query('date', Carbon::today()->toDateString());
        $surahs = QuranMaster::orderBy('surah_number')->get();

        $existingRecords = TahfidzRecord::where('teacher_id', $user->id)
            ->where('recorded_date', $date)
            ->whereIn('student_id', $students->pluck('id'))
            ->with('quranMaster')
            ->get()
            ->groupBy('student_id');

        $hasSubmitted = $existingRecords->isNotEmpty();

        return view('guru.tahfidz.create', compact(
            'classRoom', 'subject', 'students', 'date', 'surahs',
            'existingRecords', 'hasSubmitted'
        ));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $schoolId = $user->school_id;
        $ayId = $this->academicYearContext->id();

        $validated = $request->validate([
            'class_id' => 'required|uuid',
            'subject_id' => 'required|uuid',
            'date' => 'required|date',
            'hafalan' => 'required|array',
            'hafalan.*.quran_master_id' => 'nullable|uuid',
            'hafalan.*.ayat_start' => 'nullable|integer|min:1',
            'hafalan.*.ayat_end' => 'nullable|integer|min:1',
            'hafalan.*.activity_type' => 'nullable|in:ZIADAH,MURAJAAH',
            'hafalan.*.grade' => 'nullable|in:A,B,C',
            'hafalan.*.notes' => 'nullable|string|max:255',
        ]);

        $plot = ClassSubjectTeacher::where('teacher_id', $user->id)
            ->where('class_id', $validated['class_id'])
            ->where('subject_id', $validated['subject_id'])
            ->first();

        if (!$plot) {
            return back()->with('error', 'Anda tidak terploting untuk kelas ini.');
        }

        $studentIds = Student::where('class_id', $validated['class_id'])->pluck('id')->toArray();

        $saved = 0;
        $skipped = 0;

        foreach ($validated['hafalan'] as $studentId => $item) {
            if (!in_array($studentId, $studentIds)) {
                continue;
            }

            if (empty($item['quran_master_id']) || empty($item['ayat_start']) || empty($item['activity_type']) || empty($item['score'])) {
                $skipped++;
                continue;
            }

            $ayatEnd = $item['ayat_end'] ?? $item['ayat_start'];
            if ((int)$ayatEnd < (int)$item['ayat_start']) {
                $ayatEnd = $item['ayat_start'];
            }

            $surah = \App\Models\QuranMaster::find($item['quran_master_id']);
            if ($surah && (int)$ayatEnd > $surah->total_ayats) {
                $ayatEnd = $surah->total_ayats;
            }
            if ($surah && (int)$item['ayat_start'] > $surah->total_ayats) {
                $skipped++;
                continue;
            }

            TahfidzRecord::create([
                'school_id' => $schoolId,
                'academic_year_id' => $ayId,
                'student_id' => $studentId,
                'teacher_id' => $user->id,
                'quran_master_id' => $item['quran_master_id'],
                'ayat_start' => $item['ayat_start'],
                'ayat_end' => $ayatEnd,
                'activity_type' => $item['activity_type'],
                'score' => (int) $item['score'],
                'recorded_date' => $validated['date'],
                'notes' => $item['notes'] ?? null,
            ]);
            $saved++;
        }

        if ($saved === 0) {
            return back()->with('error', 'Tidak ada data yang disimpan. Pastikan minimal satu siswa memiliki data lengkap.');
        }

        $msg = "Berhasil menyimpan {$saved} hafalan.";
        if ($skipped > 0) {
            $msg .= " {$skipped} siswa dilewati (data kosong).";
        }

        return redirect()->route('guru.tahfidz.index')
            ->with('success', 'Hafalan berhasil disimpan untuk ' . $validated['date']);
    }

    public function rekap(Request $request): View
    {
        $user = auth()->user();

        $plottedClasses = ClassSubjectTeacher::where('teacher_id', $user->id)
            ->whereHas('subject', fn ($q) => $q->where('type', Subject::TYPE_QURAN))
            ->with(['classRoom', 'subject'])
            ->get()
            ->groupBy('class_id')
            ->map(function ($items) {
                $class = $items->first()->classRoom;
                return [
                    'class' => $class,
                    'subjects' => $items->pluck('subject'),
                ];
            })
            ->values();

        $classId = $request->query('class_id');
        $students = collect();
        $studentStats = collect();
        $stats = null;

        if ($classId) {
            $students = Student::where('class_id', $classId)->orderBy('name')->get();

            $query = TahfidzRecord::where('teacher_id', $user->id)
                ->whereHas('student', fn ($q) => $q->where('class_id', $classId));

            if ($this->academicYearContext->exists()) {
                $query->where('academic_year_id', $this->academicYearContext->id());
            }

            $allRecords = $query->with('quranMaster')->get();

            if ($allRecords->isNotEmpty()) {
                $stats = [
                    'total' => $allRecords->count(),
                    'ziadah' => $allRecords->where('activity_type', TahfidzRecord::TYPE_ZIADAH)->count(),
                    'murajaah' => $allRecords->where('activity_type', TahfidzRecord::TYPE_MURAJAAH)->count(),
                    'avg_score' => round($allRecords->avg('score'), 1),
                    'unique_surahs' => $allRecords->pluck('quran_master_id')->unique()->count(),
                ];
            }

            $studentStats = $allRecords->groupBy('student_id')->map(function ($records, $studentId) use ($students) {
                $student = $students->firstWhere('id', $studentId);
                $totalAyat = $records->sum(fn ($r) => $r->ayat_end - $r->ayat_start + 1);
                $surahs = $records->pluck('quran_master_id')->unique()->count();
                return [
                    'name' => $student?->name ?? '-',
                    'total' => $records->count(),
                    'ziadah' => $records->where('activity_type', TahfidzRecord::TYPE_ZIADAH)->count(),
                    'murajaah' => $records->where('activity_type', TahfidzRecord::TYPE_MURAJAAH)->count(),
                    'total_ayat' => $totalAyat,
                    'surahs' => $surahs,
                    'avg_score' => round($records->avg('score'), 1),
                ];
            });
        }

        return view('guru.tahfidz.rekap', compact(
            'plottedClasses', 'classId', 'students', 'studentStats', 'stats'
        ));
    }

    public function fetchByDate(Request $request): JsonResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'class_id' => 'required|uuid',
            'subject_id' => 'required|uuid',
            'date' => 'required|date',
        ]);

        $plot = ClassSubjectTeacher::where('teacher_id', $user->id)
            ->where('class_id', $validated['class_id'])
            ->where('subject_id', $validated['subject_id'])
            ->first();

        if (!$plot) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $studentIds = Student::where('class_id', $validated['class_id'])->pluck('id')->toArray();

        $existingRecords = TahfidzRecord::where('teacher_id', $user->id)
            ->where('recorded_date', $validated['date'])
            ->whereIn('student_id', $studentIds)
            ->with('quranMaster')
            ->get()
            ->groupBy('student_id');

        $lastPerStudent = TahfidzRecord::where('teacher_id', $user->id)
            ->whereIn('student_id', $studentIds)
            ->where('recorded_date', '<', $validated['date'])
            ->orderByDesc('recorded_date')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('student_id')
            ->map(function ($records) {
                return $records->first();
            });

        $result = [];
        foreach ($studentIds as $sid) {
            $existing = $existingRecords->get($sid, collect());
            $last = $lastPerStudent->get($sid);

            $result[$sid] = [
                'has_existing' => $existing->isNotEmpty(),
                'existing' => $existing->map(fn ($r) => [
                    'quran_master_id' => $r->quran_master_id,
                    'surah_name' => $r->quranMaster->surah_name ?? '',
                    'surah_number' => $r->quranMaster->surah_number ?? 0,
                    'ayat_start' => $r->ayat_start,
                    'ayat_end' => $r->ayat_end,
                    'activity_type' => $r->activity_type,
                    'score' => $r->score,
                    'notes' => $r->notes ?? '',
                ])->toArray(),
                'last_surah_id' => $last?->quran_master_id,
                'last_surah_name' => $last?->quranMaster?->surah_name,
                'last_surah_number' => $last?->quranMaster?->surah_number,
                'last_ayat_end' => $last?->ayat_end,
            ];
        }

        return response()->json($result);
    }

    public function student(Request $request, Student $student): View
    {
        $user = auth()->user();

        if ($student->school_id !== $user->school_id) {
            abort(403);
        }

        $hasPlot = ClassSubjectTeacher::where('teacher_id', $user->id)
            ->where('class_id', $student->class_id)
            ->whereHas('subject', fn ($q) => $q->where('type', Subject::TYPE_QURAN))
            ->exists();

        $hasAssignment = \App\Models\QuranTeachingAssignment::where('teacher_id', $user->id)
            ->where('student_id', $student->id)
            ->exists();

        if (!$hasPlot && !$hasAssignment) {
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

        return view('guru.tahfidz.student', compact('student', 'records', 'stats'));
    }

    public function destroy(TahfidzRecord $tahfidzRecord): \Illuminate\Http\RedirectResponse
    {
        $user = auth()->user();

        if ($tahfidzRecord->teacher_id !== $user->id) {
            abort(403);
        }

        $tahfidzRecord->delete();

        return back()->with('success', 'Riwayat hafalan berhasil dihapus.');
    }

    public function chart(Request $request, Student $student): JsonResponse
    {
        $user = auth()->user();

        if ($student->school_id !== $user->school_id) {
            abort(403);
        }

        $hasPlot = ClassSubjectTeacher::where('teacher_id', $user->id)
            ->where('class_id', $student->class_id)
            ->whereHas('subject', fn ($q) => $q->where('type', Subject::TYPE_QURAN))
            ->exists();

        $hasAssignment = \App\Models\QuranTeachingAssignment::where('teacher_id', $user->id)
            ->where('student_id', $student->id)
            ->exists();

        if (!$hasPlot && !$hasAssignment) {
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

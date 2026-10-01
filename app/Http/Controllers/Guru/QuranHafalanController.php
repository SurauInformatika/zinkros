<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\QuranMaster;
use App\Models\QuranTargetTemplate;
use App\Models\QuranTeachingAssignment;
use App\Models\StudentQuranTarget;
use App\Models\StudentQuranTargetItem;
use App\Models\TahfidzRecord;
use App\Services\AcademicYearContext;
use App\Services\QuranTargetService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuranHafalanController extends Controller
{
    public function __construct(
        protected AcademicYearContext $academicYearContext,
        protected QuranTargetService $quranTargetService,
    ) {}

    public function students(): View
    {
        $user = auth()->user();
        $ayId = $this->academicYearContext->id();

        $assignments = QuranTeachingAssignment::where('teacher_id', $user->id)
            ->whereHas('student')
            ->when($ayId, fn ($q) => $q->where(function ($q2) use ($ayId) {
                $q2->where('academic_year_id', $ayId)
                    ->orWhereNull('academic_year_id');
            }))
            ->with(['student.classRoom', 'student.quranTargets' => fn ($q) => $q->with('items.quranMaster')])
            ->get()
            ->sortBy('student.classRoom.class_name')
            ->groupBy(fn ($a) => $a->student->classRoom?->class_name ?? 'Tanpa Kelas');

        foreach ($assignments as $group) {
            foreach ($group as $a) {
                $targets = $a->student?->quranTargets ?? collect();
                $activeTargets = $targets->filter(fn ($t) => $t->is_active);
                $a->target_progress = $activeTargets->isNotEmpty()
                    ? $this->quranTargetService->decorateTarget($activeTargets->sortByDesc('target_date')->first())
                    : null;
            }
        }

        return view('guru.quran-hafalan.students', compact('assignments'));
    }

    public function input(Request $request, string $studentId): View
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

        $surahs = QuranMaster::orderBy('surah_number')->get();

        $lastRecord = TahfidzRecord::where('teacher_id', $user->id)
            ->where('student_id', $studentId)
            ->with('quranMaster')
            ->orderByDesc('recorded_date')
            ->orderByDesc('created_at')
            ->first();

        $date = $request->query('date', Carbon::today()->toDateString());

        $existingToday = TahfidzRecord::where('teacher_id', $user->id)
            ->where('student_id', $studentId)
            ->where('recorded_date', $date)
            ->with('quranMaster')
            ->get();

        $pastRecords = TahfidzRecord::where('student_id', $studentId)
            ->where('teacher_id', $user->id)
            ->select('quran_master_id', 'ayat_start', 'ayat_end')
            ->get();

        $allRecords = TahfidzRecord::where('teacher_id', $user->id)
            ->where('student_id', $studentId)
            ->with('quranMaster')
            ->orderByDesc('recorded_date')
            ->orderByDesc('created_at')
            ->get();

        $targets = StudentQuranTarget::where('student_id', $studentId)
            ->with('items.quranMaster')
            ->orderBy('target_date')
            ->get()
            ->map(fn ($t) => $this->quranTargetService->decorateTarget($t))
            ->all();

        return view('guru.quran-hafalan.input', compact(
            'student', 'surahs', 'lastRecord', 'existingToday', 'date', 'pastRecords', 'allRecords',
            'targets'
        ));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $schoolId = $user->school_id;
        $ayId = $this->academicYearContext->id();

        $status = $request->input('status');
        $isAbsent = in_array($status, ['SAKIT', 'IZIN']);

        if ($isAbsent) {
            $validated = $request->validate([
                'student_id' => 'required|uuid',
                'recorded_date' => 'required|date|before_or_equal:today',
                'status' => 'required|in:SAKIT,IZIN',
                'notes' => 'nullable|string|max:500',
            ]);
        } else {
            $validated = $request->validate([
                'student_id' => 'required|uuid',
                'recorded_date' => 'required|date|before_or_equal:today',
                'quran_master_id' => 'required|uuid',
                'ayat_start' => 'required|integer|min:1',
                'ayat_end' => 'required|integer|min:1',
                'activity_type' => 'required|in:ZIADAH,MURAJAAH',
                'score' => 'required|integer|min:0|max:100',
                'notes' => 'nullable|string|max:500',
            ]);
        }

        $assignment = QuranTeachingAssignment::where('teacher_id', $user->id)
            ->where('student_id', $validated['student_id'])
            ->when($ayId, fn ($q) => $q->where(function ($q2) use ($ayId) {
                $q2->where('academic_year_id', $ayId)
                    ->orWhereNull('academic_year_id');
            }))
            ->first();

        if (!$assignment) {
            return back()->with('error', 'Anda tidak memiliki akses untuk siswa ini.');
        }

        if ($isAbsent) {
            TahfidzRecord::create([
                'school_id' => $schoolId,
                'academic_year_id' => $ayId,
                'student_id' => $validated['student_id'],
                'teacher_id' => $user->id,
                'quran_master_id' => null,
                'ayat_start' => 0,
                'ayat_end' => 0,
                'activity_type' => null,
                'score' => 0,
                'status' => $validated['status'],
                'recorded_date' => $validated['recorded_date'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $label = $validated['status'] === 'SAKIT' ? 'Sakit' : 'Izin';
            return back()->with('success', "Siswa ditandai sebagai {$label}.");
        }

        $surah = QuranMaster::find($validated['quran_master_id']);
        if (!$surah) {
            return back()->with('error', 'Surah tidak ditemukan.');
        }

        $ayatStart = (int) $validated['ayat_start'];
        $ayatEnd = (int) $validated['ayat_end'];

        if ($ayatEnd < $ayatStart) {
            $ayatEnd = $ayatStart;
        }
        if ($ayatEnd > $surah->total_ayats) {
            $ayatEnd = $surah->total_ayats;
        }
        if ($ayatStart > $surah->total_ayats) {
            return back()->with('error', 'Ayat melebihi total ayat surah.');
        }

        $alreadyExists = TahfidzRecord::where('student_id', $validated['student_id'])
            ->where('teacher_id', $user->id)
            ->where('quran_master_id', $validated['quran_master_id'])
            ->where('ayat_start', '<=', $ayatEnd)
            ->where('ayat_end', '>=', $ayatStart)
            ->exists();

        if ($alreadyExists && $validated['activity_type'] === 'ZIADAH') {
            $validated['activity_type'] = 'MURAJAAH';
        }

        TahfidzRecord::create([
            'school_id' => $schoolId,
            'academic_year_id' => $ayId,
            'student_id' => $validated['student_id'],
            'teacher_id' => $user->id,
            'quran_master_id' => $validated['quran_master_id'],
            'ayat_start' => $ayatStart,
            'ayat_end' => $ayatEnd,
            'activity_type' => $validated['activity_type'],
            'score' => $validated['score'],
            'recorded_date' => $validated['recorded_date'],
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('success', 'Hafalan berhasil disimpan.');
    }

    public function history(Request $request, string $studentId): View
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

        $records = TahfidzRecord::where('teacher_id', $user->id)
            ->where('student_id', $studentId)
            ->when($ayId, fn ($q) => $q->where(function ($q2) use ($ayId) {
                $q2->where('academic_year_id', $ayId)
                    ->orWhereNull('academic_year_id');
            }))
            ->with('quranMaster')
            ->orderByDesc('recorded_date')
            ->orderByDesc('created_at')
            ->get();

        $targets = StudentQuranTarget::where('student_id', $studentId)
            ->with('items.quranMaster')
            ->orderBy('target_date')
            ->get()
            ->map(fn ($t) => $this->quranTargetService->decorateTarget($t))
            ->all();

        return view('guru.quran-hafalan.history', compact('student', 'records', 'targets'));
    }

    public function chart(Request $request, string $studentId): JsonResponse
    {
        $user = auth()->user();
        $ayId = $this->academicYearContext->id();

        $assignment = QuranTeachingAssignment::where('teacher_id', $user->id)
            ->where('student_id', $studentId)
            ->when($ayId, fn ($q) => $q->where(function ($q2) use ($ayId) {
                $q2->where('academic_year_id', $ayId)
                    ->orWhereNull('academic_year_id');
            }))
            ->first();

        if (!$assignment) {
            abort(403);
        }

        $filter = $request->query('filter', 'ta_init');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $now = Carbon::now();

        $query = TahfidzRecord::where('teacher_id', $user->id)
            ->where('student_id', $studentId);

        if ($dateFrom && $dateTo) {
            $start = Carbon::parse($dateFrom)->startOfDay();
            $end = Carbon::parse($dateTo)->endOfDay();
            $filter = 'custom';
        } else {
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
                    if ($ayId) {
                        $query->where('academic_year_id', $ayId);
                    }
                    $start = null;
                    $end = null;
                    break;
                case 'ta_lalu':
                    if ($ayId) {
                        $prevAy = AcademicYear::where('school_id', $user->school_id)
                            ->where('start_date', '<', Carbon::parse($this->academicYearContext->get()->start_date))
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
        }

        if ($start && $end) {
            $query->whereBetween('recorded_date', [$start, $end]);
        }

        $records = $query->orderBy('recorded_date')->get();

        $dates = $records->groupBy(fn ($r) => Carbon::parse($r->recorded_date)->format('Y-m-d'));
        $chartLabels = [];
        $chartAyatZiadah = [];
        $chartAyatMurajaah = [];
        $chartAvgScores = [];
        $chartCounts = [];
        $chartSurahs = [];

        if ($start && $end) {
            $current = $start->copy()->startOfDay();
            while ($current->lte($end)) {
                $key = $current->format('Y-m-d');
                $chartLabels[] = $current->format('d M');
                $dayRecords = $dates->get($key, collect());
                $chartAyatZiadah[] = $dayRecords->where('activity_type', TahfidzRecord::TYPE_ZIADAH)->sum(fn ($r) => $r->ayat_end - $r->ayat_start + 1);
                $chartAyatMurajaah[] = $dayRecords->where('activity_type', TahfidzRecord::TYPE_MURAJAAH)->sum(fn ($r) => $r->ayat_end - $r->ayat_start + 1);
                $chartAvgScores[] = $dayRecords->isNotEmpty() ? round($dayRecords->where('activity_type', '!=', null)->avg('score'), 1) : null;
                $chartCounts[] = $dayRecords->count();
                $chartSurahs[] = $dayRecords->pluck('quranMaster.surah_name')->filter()->unique()->values()->all();
                $current->addDay();
            }
        } else {
            foreach ($dates as $date => $dayRecords) {
                $chartLabels[] = Carbon::parse($date)->format('d M');
                $chartAyatZiadah[] = $dayRecords->where('activity_type', TahfidzRecord::TYPE_ZIADAH)->sum(fn ($r) => $r->ayat_end - $r->ayat_start + 1);
                $chartAyatMurajaah[] = $dayRecords->where('activity_type', TahfidzRecord::TYPE_MURAJAAH)->sum(fn ($r) => $r->ayat_end - $r->ayat_start + 1);
                $chartAvgScores[] = $dayRecords->isNotEmpty() ? round($dayRecords->where('activity_type', '!=', null)->avg('score'), 1) : null;
                $chartCounts[] = $dayRecords->count();
                $chartSurahs[] = $dayRecords->pluck('quranMaster.surah_name')->filter()->unique()->values()->all();
            }
        }

        return response()->json([
            'labels' => $chartLabels,
            'ayat_ziadah' => $chartAyatZiadah,
            'ayat_murajaah' => $chartAyatMurajaah,
            'avg_scores' => $chartAvgScores,
            'counts' => $chartCounts,
            'surahs' => $chartSurahs,
            'stats' => [
                'total' => $records->count(),
                'ziadah' => $records->where('activity_type', TahfidzRecord::TYPE_ZIADAH)->count(),
                'murajaah' => $records->where('activity_type', TahfidzRecord::TYPE_MURAJAAH)->count(),
                'ziadah_ayat' => $records->where('activity_type', TahfidzRecord::TYPE_ZIADAH)->sum(fn ($r) => $r->ayat_end - $r->ayat_start + 1),
                'murajaah_ayat' => $records->where('activity_type', TahfidzRecord::TYPE_MURAJAAH)->sum(fn ($r) => $r->ayat_end - $r->ayat_start + 1),
                'avg_score' => round($records->where('activity_type', '!=', null)->avg('score') ?? 0, 1),
                'surahs' => $records->pluck('quran_master_id')->filter()->unique()->count(),
            ],
        ]);
    }

    public function targetCreate(Request $request, string $studentId): View
    {
        $user = auth()->user();
        $ayId = $this->academicYearContext->id();

        $assignment = $this->findAssignedStudent($user->id, $studentId, $ayId);
        if (!$assignment) {
            abort(403, 'Anda tidak memiliki akses untuk siswa ini.');
        }

        $student = $assignment->student;

        $surahs = QuranMaster::orderBy('surah_number')->get();

        $templates = QuranTargetTemplate::where('school_id', $user->school_id)
            ->active()
            ->with('items.quranMaster')
            ->orderBy('title')
            ->get();

        return view('guru.quran-hafalan.target-form', compact('student', 'surahs', 'templates'));
    }

    public function targetStore(Request $request)
    {
        $user = auth()->user();
        $ayId = $this->academicYearContext->id();

        $validated = $request->validate([
            'student_id' => 'required|uuid',
            'title' => 'required|string|max:255',
            'target_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
            'surah_ids' => 'required|array|min:1',
            'surah_ids.*' => 'uuid',
            'ayat_starts' => 'required|array',
            'ayat_starts.*' => 'integer|min:1',
            'ayat_ends' => 'required|array',
            'ayat_ends.*' => 'integer|min:1',
        ]);

        $assignment = $this->findAssignedStudent($user->id, $validated['student_id'], $ayId);
        if (!$assignment) {
            return back()->with('error', 'Anda tidak memiliki akses untuk siswa ini.');
        }

        $target = StudentQuranTarget::create([
            'school_id' => $user->school_id,
            'academic_year_id' => $ayId,
            'student_id' => $validated['student_id'],
            'teacher_id' => $user->id,
            'title' => $validated['title'],
            'target_date' => $validated['target_date'],
            'is_active' => true,
            'notes' => $validated['notes'] ?? null,
        ]);

        foreach ($validated['surah_ids'] as $i => $surahId) {
            $surah = QuranMaster::find($surahId);
            if (!$surah) {
                continue;
            }

            $start = max(1, (int) $validated['ayat_starts'][$i]);
            $end = (int) $validated['ayat_ends'][$i];
            if ($end < $start) {
                $end = $start;
            }
            if ($end > $surah->total_ayats) {
                $end = $surah->total_ayats;
            }

            StudentQuranTargetItem::create([
                'target_id' => $target->id,
                'quran_master_id' => $surahId,
                'ayat_start' => $start,
                'ayat_end' => $end,
            ]);
        }

        return redirect()->route('guru.quran-hafalan.input', $validated['student_id'])
            ->with('success', 'Target hafalan berhasil dibuat.');
    }

    public function targetEdit(Request $request, string $studentId, string $targetId): View
    {
        $user = auth()->user();
        $ayId = $this->academicYearContext->id();

        $assignment = $this->findAssignedStudent($user->id, $studentId, $ayId);
        if (!$assignment) {
            abort(403, 'Anda tidak memiliki akses untuk siswa ini.');
        }

        $student = $assignment->student;

        $target = StudentQuranTarget::where('id', $targetId)
            ->where('student_id', $studentId)
            ->with('items.quranMaster')
            ->firstOrFail();

        $surahs = QuranMaster::orderBy('surah_number')->get();

        $templates = QuranTargetTemplate::where('school_id', $user->school_id)
            ->active()
            ->with('items.quranMaster')
            ->orderBy('title')
            ->get();

        return view('guru.quran-hafalan.target-form', compact('student', 'target', 'surahs', 'templates'));
    }

    public function targetUpdate(Request $request, string $studentId, string $targetId)
    {
        $user = auth()->user();
        $ayId = $this->academicYearContext->id();

        $assignment = $this->findAssignedStudent($user->id, $studentId, $ayId);
        if (!$assignment) {
            return back()->with('error', 'Anda tidak memiliki akses untuk siswa ini.');
        }

        $target = StudentQuranTarget::where('id', $targetId)
            ->where('student_id', $studentId)
            ->firstOrFail();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'target_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
            'surah_ids' => 'required|array|min:1',
            'surah_ids.*' => 'uuid',
            'ayat_starts' => 'required|array',
            'ayat_starts.*' => 'integer|min:1',
            'ayat_ends' => 'required|array',
            'ayat_ends.*' => 'integer|min:1',
        ]);

        $target->update([
            'title' => $validated['title'],
            'target_date' => $validated['target_date'],
            'is_active' => $request->boolean('is_active'),
            'notes' => $validated['notes'] ?? null,
        ]);

        $target->items()->delete();

        foreach ($validated['surah_ids'] as $i => $surahId) {
            $surah = QuranMaster::find($surahId);
            if (!$surah) {
                continue;
            }

            $start = max(1, (int) $validated['ayat_starts'][$i]);
            $end = (int) $validated['ayat_ends'][$i];
            if ($end < $start) {
                $end = $start;
            }
            if ($end > $surah->total_ayats) {
                $end = $surah->total_ayats;
            }

            StudentQuranTargetItem::create([
                'target_id' => $target->id,
                'quran_master_id' => $surahId,
                'ayat_start' => $start,
                'ayat_end' => $end,
            ]);
        }

        return redirect()->route('guru.quran-hafalan.input', $studentId)
            ->with('success', 'Target hafalan berhasil diperbarui.');
    }

    public function targetDestroy(Request $request, string $studentId, string $targetId)
    {
        $user = auth()->user();
        $ayId = $this->academicYearContext->id();

        $assignment = $this->findAssignedStudent($user->id, $studentId, $ayId);
        if (!$assignment) {
            return back()->with('error', 'Anda tidak memiliki akses untuk siswa ini.');
        }

        $target = StudentQuranTarget::where('id', $targetId)
            ->where('student_id', $studentId)
            ->firstOrFail();

        $target->delete();

        return redirect()->route('guru.quran-hafalan.input', $studentId)
            ->with('success', 'Target hafalan berhasil dihapus.');
    }

    private function findAssignedStudent(string $teacherId, string $studentId, ?string $ayId): ?QuranTeachingAssignment
    {
        return QuranTeachingAssignment::where('teacher_id', $teacherId)
            ->where('student_id', $studentId)
            ->when($ayId, fn ($q) => $q->where(function ($q2) use ($ayId) {
                $q2->where('academic_year_id', $ayId)
                    ->orWhereNull('academic_year_id');
            }))
            ->with('student.classRoom')
            ->first();
    }
}

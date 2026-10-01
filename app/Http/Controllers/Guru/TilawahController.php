<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\QuranReadingLevel;
use App\Models\QuranTeachingAssignment;
use App\Models\TilawahRecord;
use App\Services\AcademicYearContext;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TilawahController extends Controller
{
    public function __construct(protected AcademicYearContext $academicYearContext) {}

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
            ->with(['student.classRoom', 'student.tilawahRecords.readingLevel'])
            ->get()
            ->sortBy('student.classRoom.class_name')
            ->groupBy(fn ($a) => $a->student->classRoom?->class_name ?? 'Tanpa Kelas');

        $schoolLevels = QuranReadingLevel::forSchool($user->school_id);

        foreach ($assignments as $classGroup) {
            foreach ($classGroup as $a) {
                $records = $a->student?->tilawahRecords ?? collect();
                $a->last_record = $records
                    ->sortByDesc(fn ($r) => [$r->recorded_date, $r->created_at])
                    ->first();
                $a->progress = $this->computeProgress($records->all(), $schoolLevels);
            }
        }

        return view('guru.quran-tilawah.students', compact('assignments'));
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

        $levels = QuranReadingLevel::forSchool($user->school_id);

        $lastRecord = TilawahRecord::where('teacher_id', $user->id)
            ->where('student_id', $studentId)
            ->with('readingLevel')
            ->orderByDesc('recorded_date')
            ->orderByDesc('created_at')
            ->first();

        $date = $request->query('date', Carbon::today()->toDateString());

        $existingToday = TilawahRecord::where('teacher_id', $user->id)
            ->where('student_id', $studentId)
            ->where('recorded_date', $date)
            ->with('readingLevel')
            ->get();

        $allRecords = TilawahRecord::where('teacher_id', $user->id)
            ->where('student_id', $studentId)
            ->with('readingLevel')
            ->orderByDesc('recorded_date')
            ->orderByDesc('created_at')
            ->get();

        return view('guru.quran-tilawah.input', compact(
            'student', 'levels', 'lastRecord', 'existingToday', 'date', 'allRecords'
        ));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $schoolId = $user->school_id;
        $ayId = $this->academicYearContext->id();

        $status = $request->input('status');
        $isAbsent = in_array($status, [TilawahRecord::STATUS_SAKIT, TilawahRecord::STATUS_IZIN]);

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
                'reading_level_id' => 'required|uuid',
                'page_start' => 'required|integer|min:1',
                'page_end' => 'required|integer|min:1',
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
            TilawahRecord::create([
                'school_id' => $schoolId,
                'academic_year_id' => $ayId,
                'student_id' => $validated['student_id'],
                'teacher_id' => $user->id,
                'reading_level_id' => null,
                'page_start' => 0,
                'page_end' => 0,
                'score' => 0,
                'status' => $validated['status'],
                'recorded_date' => $validated['recorded_date'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $label = $validated['status'] === TilawahRecord::STATUS_SAKIT ? 'Sakit' : 'Izin';
            return back()->with('success', "Siswa ditandai sebagai {$label}.");
        }

        $level = QuranReadingLevel::find($validated['reading_level_id']);
        if (!$level) {
            return back()->with('error', 'Jenjang baca tidak ditemukan.');
        }

        $pageStart = (int) $validated['page_start'];
        $pageEnd = (int) $validated['page_end'];

        if ($pageEnd < $pageStart) {
            $pageEnd = $pageStart;
        }
        if ($level->pages > 0 && $pageEnd > $level->pages) {
            $pageEnd = $level->pages;
        }

        TilawahRecord::create([
            'school_id' => $schoolId,
            'academic_year_id' => $ayId,
            'student_id' => $validated['student_id'],
            'teacher_id' => $user->id,
            'reading_level_id' => $level->id,
            'page_start' => $pageStart,
            'page_end' => $pageEnd,
            'score' => $validated['score'],
            'recorded_date' => $validated['recorded_date'],
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('success', 'Tilawah berhasil disimpan.');
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

        $records = TilawahRecord::where('teacher_id', $user->id)
            ->where('student_id', $studentId)
            ->when($ayId, fn ($q) => $q->where(function ($q2) use ($ayId) {
                $q2->where('academic_year_id', $ayId)
                    ->orWhereNull('academic_year_id');
            }))
            ->with('readingLevel')
            ->orderByDesc('recorded_date')
            ->orderByDesc('created_at')
            ->get();

        return view('guru.quran-tilawah.history', compact('student', 'records'));
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

        $query = TilawahRecord::where('teacher_id', $user->id)
            ->where('student_id', $studentId)
            ->whereNotNull('reading_level_id');

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
                case 'bulan':
                    $start = $now->copy()->subMonth()->startOfDay();
                    $end = $now->copy()->endOfDay();
                    break;
                case 'semester':
                    $start = $now->copy()->subMonths(6)->startOfDay();
                    $end = $now->copy()->endOfDay();
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

        $records = $query->with('readingLevel')->orderBy('recorded_date')->get();

        $dates = $records->groupBy(fn ($r) => Carbon::parse($r->recorded_date)->format('Y-m-d'));
        $chartLabels = [];
        $chartPages = [];
        $chartAvgScores = [];
        $chartCounts = [];

        if ($start && $end) {
            $current = $start->copy()->startOfDay();
            while ($current->lte($end)) {
                $key = $current->format('Y-m-d');
                $chartLabels[] = $current->format('d M');
                $dayRecords = $dates->get($key, collect());
                $chartPages[] = $dayRecords->sum(fn ($r) => $r->pagesRead());
                $chartAvgScores[] = $dayRecords->isNotEmpty() ? round($dayRecords->avg('score'), 1) : null;
                $chartCounts[] = $dayRecords->count();
                $current->addDay();
            }
        } else {
            foreach ($dates as $date => $dayRecords) {
                $chartLabels[] = Carbon::parse($date)->format('d M');
                $chartPages[] = $dayRecords->sum(fn ($r) => $r->pagesRead());
                $chartAvgScores[] = $dayRecords->isNotEmpty() ? round($dayRecords->avg('score'), 1) : null;
                $chartCounts[] = $dayRecords->count();
            }
        }

        return response()->json([
            'labels' => $chartLabels,
            'pages' => $chartPages,
            'avg_scores' => $chartAvgScores,
            'counts' => $chartCounts,
            'stats' => [
                'total' => $records->count(),
                'pages' => $records->sum(fn ($r) => $r->pagesRead()),
                'avg_score' => round($records->avg('score') ?? 0, 1),
                'levels' => $records->pluck('reading_level_id')->filter()->unique()->count(),
            ],
        ]);
    }

    private function computeProgress(array $records, $schoolLevels): array
    {
        $present = collect($records)
            ->filter(fn ($r) => $r->isPresent() && $r->reading_level_id)
            ->sortBy(fn ($r) => [$r->recorded_date, $r->created_at]);

        if ($present->isEmpty()) {
            return ['percent' => 0, 'label' => 'Belum mulai'];
        }

        $last = $present->last();

        $levels = $schoolLevels->values();
        $totalPages = (int) $levels->sum('pages');

        $currentLevelIndex = 0;
        $beforePages = 0;
        foreach ($levels as $i => $level) {
            if ($level->id === $last->reading_level_id) {
                $currentLevelIndex = $i;
                break;
            }
            $beforePages += (int) $level->pages;
        }

        $currentDone = $last->page_end;
        $totalDone = $beforePages + $currentDone;
        $percent = $totalPages > 0 ? min(100, round($totalDone / $totalPages * 100)) : 0;

        return [
            'percent' => $percent,
            'label' => $last->readingLevel->label . ' hl. ' . $last->page_end,
        ];
    }
}

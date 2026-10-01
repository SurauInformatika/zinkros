<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\ClassSubjectTeacher;
use App\Services\AcademicYearContext;
use App\Models\Grade;
use App\Models\GradeType;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class GradeController extends Controller
{
    public function __construct(protected AcademicYearContext $academicYearContext) {}

    public function index(): View
    {
        $user = auth()->user();

        $cstQuery = ClassSubjectTeacher::where('teacher_id', $user->id);
        if ($this->academicYearContext->exists()) {
            $cstQuery->where('academic_year_id', $this->academicYearContext->id());
        }
        $plottedClasses = $cstQuery->with(['classRoom', 'subject'])
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
        $recentGradesQuery = Grade::where('teacher_id', $user->id)
            ->where('date', $today);
        if ($this->academicYearContext->exists()) {
            $recentGradesQuery->where(function ($q) {
                $q->where('academic_year_id', $this->academicYearContext->id())
                    ->orWhereNull('academic_year_id');
            });
        }
        $recentGrades = $recentGradesQuery->with(['subject', 'gradeType'])
            ->get()
            ->groupBy('subject_id')
            ->map(function ($items, $subjectId) {
                $subject = Subject::find($subjectId);
                return [
                    'subject' => $subject?->name ?? '-',
                    'count' => $items->count(),
                    'avg' => round($items->avg('score'), 1),
                    'min' => $items->min('score'),
                    'max' => $items->max('score'),
                ];
            })
            ->values();

        return view('guru.nilai.index', compact('plottedClasses', 'recentGrades', 'today'));
    }

    public function create(Request $request): View|\Illuminate\Http\RedirectResponse
    {
        $user = auth()->user();
        $classId = $request->query('class_id');
        $subjectId = $request->query('subject_id');

        if (!$classId || !$subjectId) {
            return redirect()->route('guru.nilai.index')
                ->with('error', 'Pilih kelas dan mata pelajaran terlebih dahulu.');
        }

        $cstQuery = ClassSubjectTeacher::where('teacher_id', $user->id)
            ->where('class_id', $classId)
            ->where('subject_id', $subjectId);
        if ($this->academicYearContext->exists()) {
            $cstQuery->where('academic_year_id', $this->academicYearContext->id());
        }
        $plot = $cstQuery->first();

        if (!$plot) {
            return redirect()->route('guru.nilai.index')
                ->with('error', 'Anda tidak terploting untuk kelas dan mata pelajaran ini.');
        }

        $classRoom = ClassRoom::findOrFail($classId);
        $subject = Subject::findOrFail($subjectId);
        $students = Student::where('class_id', $classId)->orderBy('name')->get();
        $date = $request->query('date', Carbon::today()->toDateString());
        $gradeTypeId = $request->query('grade_type_id');

        $gradeTypes = GradeType::where('school_id', $user->school_id)
            ->active()
            ->ordered()
            ->get();

        if (!$gradeTypeId && $gradeTypes->isNotEmpty()) {
            $gradeTypeId = $gradeTypes->first()->id;
        }

        $existingGrades = collect();
        $hasSubmitted = false;
        $gradeRows = collect();
        $kkm = (int) ($subject->kkm ?? 80);
        if ($gradeTypeId) {
            $existingGradesQuery = Grade::where('teacher_id', $user->id)
                ->where('subject_id', $subjectId)
                ->where('date', $date)
                ->where('grade_type_id', $gradeTypeId);
            if ($this->academicYearContext->exists()) {
                $existingGradesQuery->where(function ($q) {
                    $q->where('academic_year_id', $this->academicYearContext->id())
                        ->orWhereNull('academic_year_id');
                });
            }
            $existingGrades = $existingGradesQuery->pluck('score', 'student_id')
                ->toArray();
            $hasSubmitted = count($existingGrades) > 0;

            $rowsQuery = Grade::where('teacher_id', $user->id)
                ->where('subject_id', $subjectId)
                ->where('date', $date)
                ->where('grade_type_id', $gradeTypeId);
            if ($this->academicYearContext->exists()) {
                $rowsQuery->where(function ($q) {
                    $q->where('academic_year_id', $this->academicYearContext->id())
                        ->orWhereNull('academic_year_id');
                });
            }
            $gradeRows = $rowsQuery->get()->keyBy('student_id');
        }

        return view('guru.nilai.create', compact(
            'classRoom', 'subject', 'students', 'date', 'gradeTypeId', 'gradeTypes',
            'existingGrades', 'hasSubmitted', 'kkm', 'gradeRows'
        ));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $schoolId = $user->school_id;

        $validated = $request->validate([
            'class_id' => 'required|uuid',
            'subject_id' => 'required|uuid',
            'date' => 'required|date',
            'grade_type_id' => 'required|uuid',
            'nilai' => 'required|array',
            'nilai.*.student_id' => 'required|uuid',
            'nilai.*.score' => 'nullable|numeric|between:0,100',
            'nilai.*.notes' => 'nullable|string|max:255',
        ]);

        $cstQuery = ClassSubjectTeacher::where('teacher_id', $user->id)
            ->where('class_id', $validated['class_id'])
            ->where('subject_id', $validated['subject_id']);
        if ($this->academicYearContext->exists()) {
            $cstQuery->where('academic_year_id', $this->academicYearContext->id());
        }
        $plot = $cstQuery->first();

        if (!$plot) {
            return back()->with('error', 'Anda tidak terploting untuk kelas ini.');
        }

        $kkm = (int) (Subject::where('id', $validated['subject_id'])->value('kkm') ?? 80);

        $gradeType = GradeType::where('id', $validated['grade_type_id'])
            ->where('school_id', $schoolId)
            ->first();

        if (!$gradeType) {
            return back()->with('error', 'Tipe nilai tidak valid.');
        }

        foreach ($validated['nilai'] as $item) {
            $score = isset($item['score']) && $item['score'] !== '' ? (float) $item['score'] : null;

            if ($score === null) {
                $deleteQuery = Grade::where('teacher_id', $user->id)
                    ->where('subject_id', $validated['subject_id'])
                    ->where('student_id', $item['student_id'])
                    ->where('date', $validated['date'])
                    ->where('grade_type_id', $validated['grade_type_id']);
                if ($this->academicYearContext->exists()) {
                    $deleteQuery->where(function ($q) {
                        $q->where('academic_year_id', $this->academicYearContext->id())
                            ->orWhereNull('academic_year_id');
                    });
                }
                $deleteQuery->delete();
                continue;
            }

            Grade::updateOrCreate(
                array_merge(
                    [
                        'teacher_id' => $user->id,
                        'subject_id' => $validated['subject_id'],
                        'student_id' => $item['student_id'],
                        'date' => $validated['date'],
                        'grade_type_id' => $validated['grade_type_id'],
                    ],
                    $this->academicYearContext->exists()
                        ? ['academic_year_id' => $this->academicYearContext->id()]
                        : ['academic_year_id' => null]
                ),
                array_merge(
                    [
                        'school_id' => $schoolId,
                        'score' => $score,
                        'notes' => $item['notes'] ?? null,
                    ],
                    $score !== null && $score >= $kkm
                        ? [
                            'remedial_score' => null,
                            'remedial_capped' => null,
                            'remedial_notes' => null,
                            'remedial_by' => null,
                            'remedial_at' => null,
                        ]
                        : []
                )
            );
        }

        return redirect()->route('guru.nilai.index')
            ->with('success', 'Nilai berhasil disimpan untuk ' . $validated['date']);
    }

    public function updateKkm(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'subject_id' => 'required|uuid',
            'kkm' => 'required|integer|between:1,100',
        ]);

        $subject = Subject::where('id', $validated['subject_id'])
            ->where('school_id', $user->school_id)
            ->first();

        $plotExists = ClassSubjectTeacher::where('teacher_id', $user->id)
            ->where('subject_id', $validated['subject_id'])
            ->exists();

        if (!$subject || !$plotExists) {
            return back()->with('error', 'Anda tidak terploting pada mata pelajaran ini.');
        }

        $subject->update(['kkm' => $validated['kkm']]);

        return back()->with('success', 'KKM ' . $subject->name . ' diubah menjadi ' . $validated['kkm'] . '.');
    }

    public function remedial(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'class_id' => 'required|uuid',
            'subject_id' => 'required|uuid',
            'date' => 'required|date',
            'grade_type_id' => 'required|uuid',
            'remedial' => 'nullable|array',
            'remedial.*.student_id' => 'required|uuid',
            'remedial.*.score' => 'nullable|numeric|between:0,100',
        ]);

        $subject = Subject::where('id', $validated['subject_id'])
            ->where('school_id', $user->school_id)
            ->first();

        $kkm = (int) ($subject?->kkm ?? 80);

        $cstQuery = ClassSubjectTeacher::where('teacher_id', $user->id)
            ->where('class_id', $validated['class_id'])
            ->where('subject_id', $validated['subject_id']);
        if ($this->academicYearContext->exists()) {
            $cstQuery->where('academic_year_id', $this->academicYearContext->id());
        }
        $plot = $cstQuery->first();

        if (!$plot) {
            return back()->with('error', 'Anda tidak terploting untuk kelas ini.');
        }

        $gradeType = GradeType::where('id', $validated['grade_type_id'])
            ->where('school_id', $user->school_id)
            ->first();

        if (!$gradeType) {
            return back()->with('error', 'Tipe nilai tidak valid.');
        }

        $gradeRowsQuery = Grade::where('teacher_id', $user->id)
            ->where('subject_id', $validated['subject_id'])
            ->where('date', $validated['date'])
            ->where('grade_type_id', $validated['grade_type_id']);
        if ($this->academicYearContext->exists()) {
            $gradeRowsQuery->where(function ($q) {
                $q->where('academic_year_id', $this->academicYearContext->id())
                    ->orWhereNull('academic_year_id');
            });
        }
        $gradeRows = $gradeRowsQuery->get()->keyBy('student_id');

        foreach (($validated['remedial'] ?? []) as $item) {
            $grade = $gradeRows->get($item['student_id']);

            $student = Student::where('id', $item['student_id'])
                ->where('class_id', $validated['class_id'])
                ->first();

            if (!$grade || !$student) {
                continue;
            }

            $score = isset($item['score']) && $item['score'] !== '' ? (float) $item['score'] : null;

            if ($score === null) {
                $grade->update([
                    'remedial_score' => null,
                    'remedial_capped' => null,
                    'remedial_notes' => null,
                    'remedial_by' => null,
                    'remedial_at' => null,
                ]);
                continue;
            }

            $grade->update([
                'remedial_score' => $score,
                'remedial_capped' => min($score, $kkm),
                'remedial_notes' => $grade->remedial_notes,
                'remedial_by' => $user->id,
                'remedial_at' => now(),
            ]);
        }

        return back()->with('success', 'Remedial disimpan. Nilai perbaikan di-cap maksimal KKM (' . $kkm . ').');
    }

    public function history(Request $request): View
    {
        $user = auth()->user();

        $historyQuery = Grade::where('teacher_id', $user->id);
        if ($this->academicYearContext->exists()) {
            $historyQuery->where(function ($q) {
                $q->where('academic_year_id', $this->academicYearContext->id())
                    ->orWhereNull('academic_year_id');
            });
        }
        $query = $historyQuery->with(['subject', 'student.classRoom', 'gradeType']);

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->filled('grade_type_id')) {
            $query->where('grade_type_id', $request->grade_type_id);
        }

        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        $grades = $query->orderByDesc('date')
            ->orderBy('student_id')
            ->paginate(30)
            ->withQueryString();

        $subjects = Subject::whereHas('classSubjectTeachers', function ($q) use ($user) {
            $q->where('teacher_id', $user->id);
        })->get();

        $gradeTypes = GradeType::where('school_id', $user->school_id)
            ->active()
            ->ordered()
            ->get();

        return view('guru.nilai.history', compact('grades', 'subjects', 'gradeTypes'));
    }

    public function rekap(Request $request): View|\Illuminate\Http\RedirectResponse
    {
        $user = auth()->user();

        $plottedClassesQuery = ClassSubjectTeacher::where('teacher_id', $user->id);
        if ($this->academicYearContext->exists()) {
            $plottedClassesQuery->where('academic_year_id', $this->academicYearContext->id());
        }
        $plottedClasses = $plottedClassesQuery
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
        $subjectId = $request->query('subject_id');
        $gradeTypeId = $request->query('grade_type_id');

        $gradeTypes = GradeType::where('school_id', $user->school_id)
            ->active()
            ->ordered()
            ->get();

        if (!$classId || !$subjectId) {
            $classRoom = null;
            $subject = null;
            $students = collect();
            $dates = [];
            $allGrades = collect();
            $stats = null;
            $studentStats = collect();

            return view('guru.nilai.rekap', compact(
                'plottedClasses', 'classRoom', 'subject', 'students', 'dates',
                'allGrades', 'stats', 'studentStats',
                'gradeTypeId', 'gradeTypes'
            ));
        }

        $plotQuery = ClassSubjectTeacher::where('teacher_id', $user->id)
            ->where('class_id', $classId)
            ->where('subject_id', $subjectId);
        if ($this->academicYearContext->exists()) {
            $plotQuery->where('academic_year_id', $this->academicYearContext->id());
        }
        $plot = $plotQuery->first();

        if (!$plot) {
            return redirect()->route('guru.nilai.rekap')
                ->with('error', 'Anda tidak terploting untuk kelas ini.');
        }

        $classRoom = ClassRoom::findOrFail($classId);
        $subject = Subject::findOrFail($subjectId);
        $students = Student::where('class_id', $classId)->orderBy('name')->get();

        $query = Grade::where('teacher_id', $user->id)
            ->where('subject_id', $subjectId)
            ->whereHas('student', fn ($q) => $q->where('class_id', $classId));
        if ($this->academicYearContext->exists()) {
            $query->where(function ($q) {
                $q->where('academic_year_id', $this->academicYearContext->id())
                    ->orWhereNull('academic_year_id');
            });
        }

        if ($gradeTypeId) {
            $query->where('grade_type_id', $gradeTypeId);
        }

        $allGrades = $query->get()->load('gradeType');

        $dates = $allGrades->pluck('date')->unique()->sort()->values()->toArray();

        $finalScores = $allGrades->map(fn ($g) => $g->finalScore())
            ->filter(fn ($s) => $s !== null)
            ->values();

        $stats = null;
        if ($finalScores->isNotEmpty()) {
            $total = $finalScores->count();
            $above80 = $finalScores->where(fn ($s) => $s >= 80)->count();
            $below60 = $finalScores->where(fn ($s) => $s < 60)->count();
            $stats = [
                'total' => $total,
                'avg' => round($finalScores->avg(), 1),
                'min' => $finalScores->min(),
                'max' => $finalScores->max(),
                'above80' => $above80,
                'below60' => $below60,
                'pct_above80' => round($above80 / $total * 100, 1),
                'pct_below60' => round($below60 / $total * 100, 1),
                'by_type' => $allGrades->groupBy('grade_type_id')->map(function ($items) {
                    $scores = $items->map(fn ($g) => $g->finalScore())
                        ->filter(fn ($s) => $s !== null)
                        ->values();
                    $gradeType = $items->first()->gradeType;
                    return [
                        'name' => $gradeType?->name ?? '-',
                        'weight' => $gradeType?->weight ?? 0,
                        'count' => $scores->count(),
                        'avg' => $scores->isNotEmpty() ? round($scores->avg(), 1) : null,
                        'min' => $scores->isNotEmpty() ? $scores->min() : null,
                        'max' => $scores->isNotEmpty() ? $scores->max() : null,
                    ];
                }),
            ];
        }

        $studentStats = $allGrades->groupBy('student_id')->map(function ($records) {
            $scores = $records->map(fn ($g) => $g->finalScore())
                ->filter(fn ($s) => $s !== null)
                ->values();
            $byType = $records->groupBy('grade_type_id')->map(function ($items) {
                $scores = $items->map(fn ($g) => $g->finalScore())
                    ->filter(fn ($s) => $s !== null)
                    ->values();
                return $scores->isNotEmpty() ? round($scores->avg(), 1) : null;
            });
            return [
                'count' => $scores->count(),
                'avg' => $scores->isNotEmpty() ? round($scores->avg(), 1) : null,
                'min' => $scores->isNotEmpty() ? $scores->min() : null,
                'max' => $scores->isNotEmpty() ? $scores->max() : null,
                'above80' => $scores->where(fn ($s) => $s >= 80)->count(),
                'below60' => $scores->where(fn ($s) => $s < 60)->count(),
                'by_type' => $byType,
            ];
        });

        return view('guru.nilai.rekap', compact(
            'plottedClasses', 'classRoom', 'subject', 'students', 'dates',
            'allGrades', 'stats', 'studentStats',
            'gradeTypeId', 'gradeTypes'
        ));
    }

    public function ajaxGrades(Request $request): JsonResponse
    {
        $user = auth()->user();

        $request->validate([
            'class_id' => 'required|uuid',
            'subject_id' => 'required|uuid',
            'date' => 'required|date',
            'grade_type_id' => 'required|uuid',
        ]);

        $grades = Grade::where('teacher_id', $user->id)
            ->where('subject_id', $request->subject_id)
            ->where('date', $request->date)
            ->where('grade_type_id', $request->grade_type_id)
            ->pluck('score', 'student_id')
            ->toArray();

        return response()->json([
            'grades' => $grades,
            'hasData' => count($grades) > 0,
        ]);
    }

    public function studentGrades(string $studentId): View
    {
        $user = auth()->user();

        $student = Student::with('classRoom')->findOrFail($studentId);

        $query = Grade::where('teacher_id', $user->id)
            ->where('student_id', $studentId)
            ->with(['subject', 'gradeType']);
        if ($this->academicYearContext->exists()) {
            $query->where(function ($q) {
                $q->where('academic_year_id', $this->academicYearContext->id())
                    ->orWhereNull('academic_year_id');
            });
        }

        $grades = $query->orderByDesc('date')->get();

        $allScores = $grades->map(fn ($g) => $g->finalScore())
            ->filter(fn ($s) => $s !== null)
            ->values();
        $stats = null;
        if ($allScores->isNotEmpty()) {
            $stats = [
                'avg' => round($allScores->avg(), 1),
                'max' => $allScores->max(),
                'min' => $allScores->min(),
                'total' => $grades->count(),
                'above80' => $allScores->filter(fn ($s) => $s >= 80)->count(),
                'below60' => $allScores->filter(fn ($s) => $s < 60)->count(),
            ];
        }

        $bySubject = $grades->groupBy(fn ($g) => $g->subject_id)->map(function ($items) {
            $subject = $items->first()->subject;
            $byType = $items->groupBy('grade_type_id')->map(function ($typeItems) {
                $gt = $typeItems->first()->gradeType;
                return [
                    'type_name' => $gt?->name ?? '-',
                    'weight' => $gt?->weight ?? 0,
                    'records' => $typeItems->map(fn ($g) => [
                        'date' => $g->date,
                        'score' => $g->finalScore(),
                        'original_score' => $g->score,
                        'is_remedial' => $g->hasRemedial(),
                        'notes' => $g->notes,
                    ])->sortByDesc('date')->values(),
                    'avg' => $typeItems->map(fn ($g) => $g->finalScore())
                        ->filter(fn ($s) => $s !== null)
                        ->avg(),
                ];
            })->values();

            $scores = $items->map(fn ($g) => $g->finalScore())
                ->filter(fn ($s) => $s !== null)
                ->values();
            return [
                'subject_name' => $subject?->name ?? '-',
                'by_type' => $byType,
                'avg' => $scores->isNotEmpty() ? round($scores->avg(), 1) : null,
            ];
        })->values();

        return view('guru.nilai.student', compact('student', 'stats', 'bySubject'));
    }
}

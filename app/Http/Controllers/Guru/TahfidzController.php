<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\AcademicCalendar;
use App\Models\AcademicYear;
use App\Models\ClassRoom;
use App\Models\ClassSubjectTeacher;
use App\Models\QuranMaster;
use App\Models\Student;
use App\Models\StudentQuranTarget;
use App\Models\StudentQuranTargetItem;
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
            });

        $assigned = \App\Models\QuranTeachingAssignment::where('teacher_id', $user->id)
            ->with(['student.classRoom'])
            ->get()
            ->groupBy(fn ($a) => $a->student?->class_id)
            ->filter(fn ($items, $cid) => $cid);

        $subjectQuran = \App\Models\Subject::where('type', Subject::TYPE_QURAN)->first();
        if ($subjectQuran) {
            foreach ($assigned as $classId => $items) {
                if ($plottedClasses->has($classId)) {
                    $existing = $plottedClasses->get($classId);
                    $existing['subjects']->put($subjectQuran->id, $subjectQuran);
                    $plottedClasses->put($classId, $existing);
                } else {
                    $class = $items->first()->student?->classRoom;
                    if ($class) {
                        $plottedClasses->put($classId, [
                            'class' => $class,
                            'subjects' => collect([$subjectQuran->id => $subjectQuran]),
                        ]);
                    }
                }
            }
        }

        $plottedClasses = $plottedClasses->values()->sortBy(fn ($pc) => $pc['class']->class_name ?? '')->values();

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

    public function create(Request $request): View|\Illuminate\Http\RedirectResponse
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
            $hasAssignment = \App\Models\QuranTeachingAssignment::where('teacher_id', $user->id)
                ->whereHas('student', fn ($q) => $q->where('class_id', $classId))
                ->exists();

            $subject = \App\Models\Subject::find($subjectId);
            if (!$hasAssignment || !$subject || $subject->type !== Subject::TYPE_QURAN) {
                return redirect()->route('guru.tahfidz.index')
                    ->with('error', 'Anda tidak terploting untuk kelas dan mata pelajaran ini.');
            }
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
        $isPj = (bool) $user->is_pj_tahfidz;
        $mode = $request->query('mode', 'kelas');

        if ($isPj) {
            $plottedClasses = ClassSubjectTeacher::whereHas('subject', fn ($q) => $q->where('type', Subject::TYPE_QURAN))
                ->with(['classRoom', 'subject'])
                ->get()
                ->groupBy('class_id')
                ->map(function ($items) {
                    $class = $items->first()->classRoom;
                    return [
                        'class' => $class,
                        'subjects' => $items->pluck('subject')->unique('id'),
                    ];
                });

            $assigned = \App\Models\QuranTeachingAssignment::with(['student.classRoom', 'teacher'])
                ->get()
                ->groupBy(function ($item) {
                    return $item->student?->class_id;
                })
                ->filter(function ($items, $classId) {
                    return !empty($classId);
                });

            $subjectQuran = Subject::where('type', Subject::TYPE_QURAN)->first();
            foreach ($assigned as $classId => $items) {
                if ($plottedClasses->has($classId)) {
                    $existing = $plottedClasses->get($classId);
                    if ($subjectQuran && !$existing['subjects']->contains('id', $subjectQuran->id)) {
                        $existing['subjects']->push($subjectQuran);
                    }
                    $plottedClasses->put($classId, $existing);
                } else {
                    $class = $items->first()->student?->classRoom;
                    if ($class) {
                        $plottedClasses->put($classId, [
                            'class' => $class,
                            'subjects' => $subjectQuran ? collect([$subjectQuran]) : collect(),
                        ]);
                    }
                }
            }

            $plottedClasses = $plottedClasses->values()->sortBy(fn ($item) => $item['class']->class_name ?? $item['class']->name ?? '')->values();
        } else {
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
                });

            $assignedClassIds = \App\Models\QuranTeachingAssignment::where('teacher_id', $user->id)
                ->join('students', 'students.id', '=', 'quran_teaching_assignments.student_id')
                ->pluck('students.class_id')
                ->unique()
                ->filter();

            if ($assignedClassIds->isNotEmpty()) {
                $existingClassIds = $plottedClasses->keys();
                $missingClassIds = $assignedClassIds->diff($existingClassIds);

                if ($missingClassIds->isNotEmpty()) {
                    $missingClasses = \App\Models\ClassRoom::whereIn('id', $missingClassIds)->get()->keyBy('id');
                    $subjectQuran = \App\Models\Subject::where('type', Subject::TYPE_QURAN)->first();

                    foreach ($missingClassIds as $cid) {
                        if (isset($missingClasses[$cid])) {
                            $plottedClasses->put($cid, [
                                'class' => $missingClasses[$cid],
                                'subjects' => $subjectQuran ? collect([$subjectQuran]) : collect(),
                            ]);
                        }
                    }
                }
            }

            $plottedClasses = $plottedClasses->values()->sortBy(fn ($item) => $item['class']->class_name ?? $item['class']->name ?? '')->values();
        }

        $classId = $request->query('class_id');
        $filterTeacher = $request->query('guru_id');
        $teachers = collect();
        $students = collect();
        $studentStats = collect();
        $stats = null;

        if ($isPj) {
            $teachers = \App\Models\User::where('school_id', $user->school_id)
                ->where(function ($q) {
                    $q->where('is_pj_tahfidz', true)
                      ->orWhereHas('classSubjectTeachers.subject', function ($sq) {
                          $sq->where('type', \App\Models\Subject::TYPE_QURAN);
                      })
                      ->orWhereHas('quranTeachingAssignments');
                })
                ->orderBy('name')
                ->get()
                ->unique('id');

            if ($mode !== 'guru') {
                if (!$classId && $plottedClasses->isNotEmpty()) {
                    $first = $plottedClasses->first();
                    if (isset($first['class']) && $first['class']) {
                        $classId = $first['class']->id;
                    }
                }
            }
        }

        if ($mode === 'guru' && $isPj && $filterTeacher) {
            $guru = $teachers->firstWhere('id', $filterTeacher) ?? \App\Models\User::find($filterTeacher);

            $teacherClassIds = collect();
            ClassSubjectTeacher::where('teacher_id', $filterTeacher)
                ->whereHas('subject', fn ($q) => $q->where('type', Subject::TYPE_QURAN))
                ->pluck('class_id')
                ->each(fn ($id) => $teacherClassIds->push($id));

            \App\Models\QuranTeachingAssignment::where('teacher_id', $filterTeacher)
                ->join('students', 'students.id', '=', 'quran_teaching_assignments.student_id')
                ->pluck('students.class_id')
                ->each(fn ($id) => $teacherClassIds->push($id));

            $teacherClassIds = $teacherClassIds->filter()->unique();

            $assignedStudentIds = \App\Models\QuranTeachingAssignment::where('teacher_id', $filterTeacher)
                ->pluck('student_id')
                ->filter()
                ->unique();

            if ($teacherClassIds->isNotEmpty() || $assignedStudentIds->isNotEmpty()) {
                $classes = ClassRoom::whereIn('id', $teacherClassIds)->orderBy('class_name')->get()->keyBy('id');

                $students = Student::with('classRoom')
                    ->when($assignedStudentIds->isNotEmpty(), function ($q) use ($assignedStudentIds) {
                        $q->whereIn('id', $assignedStudentIds);
                    }, function ($q) use ($teacherClassIds) {
                        $q->whereIn('class_id', $teacherClassIds);
                    })
                    ->orderBy('class_id')
                    ->orderBy('name')
                    ->get();

                $query = TahfidzRecord::where('teacher_id', $filterTeacher)
                    ->when($assignedStudentIds->isNotEmpty(), function ($q) use ($assignedStudentIds) {
                        $q->whereIn('student_id', $assignedStudentIds);
                    }, function ($q) use ($teacherClassIds) {
                        $q->whereHas('student', fn ($sq) => $sq->whereIn('class_id', $teacherClassIds));
                    });

                if ($this->academicYearContext->exists()) {
                    $query->where('academic_year_id', $this->academicYearContext->id());
                }

                $allRecords = $query->with('quranMaster', 'student.classRoom')->get();

                if ($allRecords->isNotEmpty()) {
                    $stats = [
                        'total' => $allRecords->count(),
                        'ziadah' => $allRecords->where('activity_type', TahfidzRecord::TYPE_ZIADAH)->count(),
                        'murajaah' => $allRecords->where('activity_type', TahfidzRecord::TYPE_MURAJAAH)->count(),
                        'avg_score' => round($allRecords->avg('score'), 1),
                        'unique_surahs' => $allRecords->pluck('quran_master_id')->filter()->unique()->count(),
                    ];
                } else {
                    $stats = [
                        'total' => 0,
                        'ziadah' => 0,
                        'murajaah' => 0,
                        'avg_score' => 0,
                        'unique_surahs' => 0,
                    ];
                }

                $recordsByStudent = $allRecords->groupBy('student_id');

                $studentStats = $students->mapWithKeys(function ($student) use ($recordsByStudent) {
                    $records = $recordsByStudent->get($student->id, collect());
                    $totalAyat = $records->sum(fn ($r) => (($r->ayat_end ?? $r->ayat_start) - $r->ayat_start + 1) > 0 ? (($r->ayat_end ?? $r->ayat_start) - $r->ayat_start + 1) : 0);
                    $surahs = $records->pluck('quran_master_id')->filter()->unique()->count();
                    $avgScore = $records->whereNotNull('score')->avg('score');
                    $className = $student->classRoom->class_name ?? $student->classRoom->name ?? '-';
                    return [
                        $student->id => [
                            'name' => $student->name,
                            'class_id' => $student->class_id,
                            'class_name' => $className,
                            'total' => $records->count(),
                            'ziadah' => $records->where('activity_type', TahfidzRecord::TYPE_ZIADAH)->count(),
                            'murajaah' => $records->where('activity_type', TahfidzRecord::TYPE_MURAJAAH)->count(),
                            'total_ayat' => $totalAyat,
                            'surahs' => $surahs,
                            'avg_score' => $avgScore ? round($avgScore, 1) : null,
                        ],
                    ];
                });

                $studentStatsGrouped = $studentStats->groupBy('class_id')->sortKeys();
            } else {
                $students = collect();
                $studentStats = collect();
                $studentStatsGrouped = collect();
                $classes = collect();
                $stats = [
                    'total' => 0,
                    'ziadah' => 0,
                    'murajaah' => 0,
                    'avg_score' => 0,
                    'unique_surahs' => 0,
                ];
            }
        } elseif ($classId) {
            $students = Student::with('classRoom')->where('class_id', $classId)->orderBy('name')->get();

            $query = TahfidzRecord::whereHas('student', fn ($q) => $q->where('class_id', $classId));

            if ($isPj && $filterTeacher) {
                $query->where('teacher_id', $filterTeacher);
            } elseif (!$isPj) {
                $query->where('teacher_id', $user->id);
            }

            if ($this->academicYearContext->exists()) {
                $query->where('academic_year_id', $this->academicYearContext->id());
            }

            $allRecords = $query->with('quranMaster', 'student.classRoom')->get();

            if ($allRecords->isNotEmpty()) {
                $stats = [
                    'total' => $allRecords->count(),
                    'ziadah' => $allRecords->where('activity_type', TahfidzRecord::TYPE_ZIADAH)->count(),
                    'murajaah' => $allRecords->where('activity_type', TahfidzRecord::TYPE_MURAJAAH)->count(),
                    'avg_score' => round($allRecords->avg('score'), 1),
                    'unique_surahs' => $allRecords->pluck('quran_master_id')->filter()->unique()->count(),
                ];
            } else {
                $stats = [
                    'total' => 0,
                    'ziadah' => 0,
                    'murajaah' => 0,
                    'avg_score' => 0,
                    'unique_surahs' => 0,
                ];
            }

            $recordsByStudent = $allRecords->groupBy('student_id');

            $studentStats = $students->mapWithKeys(function ($student) use ($recordsByStudent) {
                $records = $recordsByStudent->get($student->id, collect());
                $totalAyat = $records->sum(fn ($r) => (($r->ayat_end ?? $r->ayat_start) - $r->ayat_start + 1) > 0 ? (($r->ayat_end ?? $r->ayat_start) - $r->ayat_start + 1) : 0);
                $surahs = $records->pluck('quran_master_id')->filter()->unique()->count();
                $avgScore = $records->whereNotNull('score')->avg('score');
                $className = $student->classRoom->class_name ?? $student->classRoom->name ?? '-';
                return [
                    $student->id => [
                        'name' => $student->name,
                        'class_name' => $className,
                        'total' => $records->count(),
                        'ziadah' => $records->where('activity_type', TahfidzRecord::TYPE_ZIADAH)->count(),
                        'murajaah' => $records->where('activity_type', TahfidzRecord::TYPE_MURAJAAH)->count(),
                        'total_ayat' => $totalAyat,
                        'surahs' => $surahs,
                        'avg_score' => $avgScore ? round($avgScore, 1) : null,
                    ],
                ];
            });
        }

        $studentStatsGrouped = $studentStatsGrouped ?? collect();
        $classes = $classes ?? collect();

        return view('guru.tahfidz.rekap', compact(
            'plottedClasses', 'classId', 'students', 'studentStats', 'stats', 'teachers', 'filterTeacher', 'mode', 'studentStatsGrouped', 'classes'
        ));
    }

    public function rekapHarian(Request $request): View
    {
        $validated = $request->validate([
            'class_id' => 'nullable|uuid',
            'filter' => 'nullable|in:hari_ini,pekan_ini,bulan_ini,semester_ini,tahun_ajaran_ini',
        ]);

        $user = auth()->user();
        if (! $user) {
            abort(403);
        }

        $classId = $validated['class_id'] ?? $request->input('class_id');

        if (! $classId) {
            return view('guru.tahfidz.rekap-harian', [
                'class' => null,
                'records' => collect(),
                'dates' => collect(),
                'summary' => ['siswa_menghafal' => 0, 'ziadah' => 0, 'murajaah' => 0, 'total_ayat' => 0],
                'canInput' => false,
            ]);
        }

        $classRoom = ClassRoom::where('school_id', $user->school_id)
            ->find($classId);

        if (! $classRoom) {
            return view('guru.tahfidz.rekap-harian', [
                'class' => null,
                'records' => collect(),
                'dates' => collect(),
                'summary' => ['siswa_menghafal' => 0, 'ziadah' => 0, 'murajaah' => 0, 'total_ayat' => 0],
                'canInput' => false,
            ]);
        }

        $hasPlot = false;
        $hasAssignment = false;

        if ($user->role === 'guru') {
            if ($user->is_pj_tahfidz) {
                $hasPlot = true;
                $hasAssignment = true;
            } else {
                $hasPlot = ClassSubjectTeacher::where('teacher_id', $user->id)
                    ->where('class_id', $classRoom->id)
                    ->whereHas('subject', fn ($query) => $query->where('type', Subject::TYPE_QURAN))
                    ->exists();
                $hasAssignment = \App\Models\QuranTeachingAssignment::where('teacher_id', $user->id)
                    ->whereHas('student', fn ($query) => $query->where('class_id', $classRoom->id))
                    ->exists();
            }
        } elseif (in_array($user->role, ['admin', 'kepsek', 'wakakur'])) {
            $hasPlot = true;
            $hasAssignment = true;
        }

        if (! $hasPlot && ! $hasAssignment) {
            return view('guru.tahfidz.rekap-harian', [
                'class' => $classRoom,
                'records' => collect(),
                'dates' => collect(),
                'summary' => ['siswa_menghafal' => 0, 'ziadah' => 0, 'murajaah' => 0, 'total_ayat' => 0],
                'canInput' => false,
            ]);
        }

        $today = Carbon::today();
        $filter = $validated['filter'] ?? 'pekan_ini';
        $academicYear = $this->academicYearContext->get()
            ?? AcademicYear::where('school_id', $user->school_id)->where('is_active', true)->first();

        $rangeStart = match ($filter) {
            'hari_ini' => $today->copy(),
            'bulan_ini' => $today->copy()->startOfMonth(),
            'semester_ini', 'tahun_ajaran_ini' => $academicYear
                ? Carbon::parse($academicYear->start_date)
                : $today->copy()->startOfYear(),
            default => $today->copy()->startOfWeek(Carbon::MONDAY),
        };
        $rangeEnd = $today->copy();

        if ($filter === 'tahun_ajaran_ini' && $academicYear) {
            $rangeEnd = min($today, Carbon::parse($academicYear->end_date));
        } elseif ($filter === 'semester_ini') {
            $calendarQuery = AcademicCalendar::where('school_id', $user->school_id)
                ->where('status', AcademicCalendar::STATUS_FINAL)
                ->when($academicYear, fn ($query) => $query->where('academic_year_id', $academicYear->id));
            $annualCalendar = (clone $calendarQuery)
                ->where('semester', AcademicCalendar::SEMESTER_ANNUAL)
                ->orderByDesc('version')
                ->first();

            if ($annualCalendar) {
                if ($annualCalendar->semester_2_start_date && $today->gte($annualCalendar->semester_2_start_date)) {
                    $rangeStart = $annualCalendar->semester_2_start_date->copy();
                    $rangeEnd = $annualCalendar->end_date->copy();
                } elseif ($annualCalendar->semester_2_start_date) {
                    $rangeStart = $annualCalendar->start_date->copy();
                    $rangeEnd = $annualCalendar->semester1EndDate();
                } else {
                    $midpoint = $annualCalendar->start_date->copy()
                        ->addDays((int) floor($annualCalendar->start_date->diffInDays($annualCalendar->end_date) / 2));
                    if ($today->gte($midpoint)) {
                        $rangeStart = $midpoint;
                        $rangeEnd = $annualCalendar->end_date->copy();
                    } else {
                        $rangeStart = $annualCalendar->start_date->copy();
                        $rangeEnd = $midpoint->copy()->subDay();
                    }
                }
            } else {
                $semesterCalendars = (clone $calendarQuery)
                    ->whereIn('semester', [1, 2])
                    ->orderBy('semester')
                    ->get();
                $currentSemester = $semesterCalendars->first(
                    fn ($calendar) => $today->betweenIncluded($calendar->start_date, $calendar->end_date)
                );
                if ($currentSemester) {
                    $rangeStart = $currentSemester->start_date->copy();
                    $rangeEnd = $currentSemester->end_date->copy();
                } elseif ($academicYear) {
                    $midpoint = Carbon::parse($academicYear->start_date)
                        ->addDays((int) floor(Carbon::parse($academicYear->start_date)->diffInDays(Carbon::parse($academicYear->end_date)) / 2));
                    $rangeStart = $today->lt($midpoint)
                        ? Carbon::parse($academicYear->start_date)
                        : $midpoint;
                    $rangeEnd = $today->lt($midpoint)
                        ? $midpoint->copy()->subDay()
                        : Carbon::parse($academicYear->end_date);
                } else {
                    $rangeStart = $today->month < 7
                        ? $today->copy()->startOfYear()
                        : $today->copy()->startOfYear()->addMonths(6);
                    $rangeEnd = $rangeStart->copy()->addMonths(6)->subDay();
                }
            }
            $rangeEnd = min($rangeEnd, $today);
        }

        $students = Student::where('class_id', $classRoom->id)
            ->orderBy('name')
            ->get();
        $baseRecordsQuery = TahfidzRecord::where('teacher_id', $user->id)
            ->whereIn('student_id', $students->pluck('id'))
            ->whereBetween('recorded_date', [$rangeStart->toDateString(), $rangeEnd->toDateString()])
            ->when(
                $academicYear,
                fn ($query) => $query->where('academic_year_id', $academicYear->id)
            );
        $recordsByDate = (clone $baseRecordsQuery)
            ->with('student', 'quranMaster')
            ->orderBy('created_at')
            ->get()
            ->groupBy(fn ($record) => Carbon::parse($record->recorded_date)->toDateString());

        $dateCards = $recordsByDate->map(function ($dayRecords, $date) use ($students) {
            $recordsByStudent = $dayRecords->groupBy('student_id');
            $present = $recordsByStudent->filter(
                fn ($studentRecords) => $studentRecords->contains(fn ($record) => $record->status === null)
            )->count();

            return [
                'date' => $date,
                'total' => $students->count(),
                'hadir' => $present,
                'tidak_hadir' => max(0, $students->count() - $present),
            ];
        })->sortByDesc('date')->values();

        return view('guru.tahfidz.rekap-harian', compact(
            'classRoom', 'filter', 'rangeStart', 'rangeEnd', 'dateCards'
        ));
    }

    public function detailRekapHarian(Request $request, string $date): View
    {
        $request->merge(['detail_date' => $date]);
        $validated = $request->validate([
            'class_id' => 'required|uuid',
            'filter' => 'nullable|in:hari_ini,pekan_ini,bulan_ini,semester_ini,tahun_ajaran_ini',
            'detail_date' => 'required|date',
        ]);

        $user = auth()->user();
        $classRoom = ClassRoom::where('school_id', $user->school_id)
            ->findOrFail($validated['class_id']);

        $hasPlot = ClassSubjectTeacher::where('teacher_id', $user->id)
            ->where('class_id', $classRoom->id)
            ->whereHas('subject', fn ($query) => $query->where('type', Subject::TYPE_QURAN))
            ->exists();
        $hasAssignment = \App\Models\QuranTeachingAssignment::where('teacher_id', $user->id)
            ->whereHas('student', fn ($query) => $query->where('class_id', $classRoom->id))
            ->exists();

        if (!$hasPlot && !$hasAssignment) {
            abort(403);
        }

        $detailDate = Carbon::parse($validated['detail_date'])->toDateString();
        $academicYear = $this->academicYearContext->get()
            ?? AcademicYear::where('school_id', $user->school_id)->where('is_active', true)->first();
        $students = Student::where('class_id', $classRoom->id)
            ->orderBy('name')
            ->get();
        $detailRecords = TahfidzRecord::where('teacher_id', $user->id)
            ->where('recorded_date', $detailDate)
            ->whereIn('student_id', $students->pluck('id'))
            ->when(
                $academicYear,
                fn ($query) => $query->where('academic_year_id', $academicYear->id)
            )
            ->with('quranMaster')
            ->orderBy('created_at')
            ->get()
            ->groupBy('student_id');

        abort_if($detailRecords->isEmpty(), 404);

        $detailStudents = $students->map(function ($student) use ($detailRecords) {
            $studentRecords = $detailRecords->get($student->id, collect());
            $attendanceRecord = $studentRecords->first(fn ($record) => $record->status === null)
                ?? $studentRecords->first(fn ($record) => in_array($record->status, [
                    TahfidzRecord::STATUS_SAKIT,
                    TahfidzRecord::STATUS_IZIN,
                    TahfidzRecord::STATUS_ALPA,
                ], true));

            return [
                'student' => $student,
                'status' => $attendanceRecord
                    ? TahfidzRecord::attendanceStatus($attendanceRecord->status)
                    : 'ALPA',
                'records' => $studentRecords->filter(
                    fn ($record) => $record->quran_master_id !== null
                ),
                'notes' => $attendanceRecord?->notes,
            ];
        });

        return view('guru.tahfidz.rekap-harian-detail', [
            'classRoom' => $classRoom,
            'filter' => $validated['filter'] ?? 'pekan_ini',
            'detailDate' => $detailDate,
            'detailStudents' => $detailStudents,
        ]);
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

        $targets = $this->buildTargetsForStudent($student->id);

        $student->load('classRoom');

        return view('guru.tahfidz.student', compact('student', 'records', 'stats', 'targets'));
    }

    private function buildTargetsForStudent(string $studentId): array
    {
        $targets = StudentQuranTarget::where('student_id', $studentId)
            ->with(['items.quranMaster'])
            ->orderByDesc('is_active')
            ->orderByDesc('created_at')
            ->get();

        $result = [];

        foreach ($targets as $target) {
            $totalAyat = 0;
            $completedAyat = 0;
            $items = [];

            foreach ($target->items as $item) {
                $quran = $item->quranMaster;
                $itemTotal = $item->totalAyat();
                $totalAyat += $itemTotal;

                $maxAyat = TahfidzRecord::where('student_id', $studentId)
                    ->where('quran_master_id', $item->quran_master_id)
                    ->where('activity_type', TahfidzRecord::TYPE_ZIADAH)
                    ->max('ayat_end');

                $done = 0;
                if ($maxAyat !== null) {
                    $start = $item->ayat_start;
                    $done = max(0, min($itemTotal, (int) $maxAyat - $start + 1));
                    if ($done < 0) {
                        $done = 0;
                    }
                    if ((int) $maxAyat >= $item->ayat_end) {
                        $done = $itemTotal;
                    }
                }

                $completedAyat += $done;

                $items[] = [
                    'surah_name' => $quran->surah_name ?? '-',
                    'surah_number' => $quran->surah_number ?? null,
                    'ayat_start' => $item->ayat_start,
                    'ayat_end' => $item->ayat_end,
                    'total' => $itemTotal,
                    'done' => $done,
                ];
            }

            $percent = $totalAyat > 0 ? (int) round(($completedAyat / $totalAyat) * 100) : 0;
            if ($percent > 100) {
                $percent = 100;
            }

            $daysRemaining = (int) now()->diffInDays($target->target_date, false);
            $status = $target->is_active ? 'active' : 'inactive';
            if ($percent >= 100 && $target->is_active) {
                $status = 'completed';
            } elseif ($daysRemaining < 0 && $percent < 100 && $target->is_active) {
                $status = 'overdue';
            }

            $result[] = [
                'id' => $target->id,
                'title' => $target->title,
                'target_date' => $target->target_date->format('Y-m-d'),
                'target_date_label' => $target->target_date->format('d M Y'),
                'days_remaining' => $daysRemaining,
                'status' => $status,
                'percent' => $percent,
                'total_ayat' => $totalAyat,
                'completed_ayat' => $completedAyat,
                'items' => $items,
                'notes' => $target->notes,
                'is_active' => $target->is_active,
            ];
        }

        return $result;
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

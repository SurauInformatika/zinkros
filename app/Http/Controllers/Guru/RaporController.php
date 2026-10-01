<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\ClassSubjectTeacher;
use App\Models\Grade;
use App\Models\GradeType;
use App\Models\Student;
use App\Models\Subject;
use App\Models\TahfidzRecord;
use App\Services\AcademicYearContext;
use App\Services\RaporDataService;
use App\Support\RaporFormat;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RaporController extends Controller
{
    public function __construct(protected AcademicYearContext $academicYearContext) {}

    public function index(Request $request): View
    {
        $user = auth()->user();
        $ayId = $this->academicYearContext->id();

        $waliClasses = ClassRoom::whereHas('homerooms', fn ($q) => $q->where('user_id', $user->id))
            ->withCount('students')
            ->orderBy('grade_level')
            ->orderBy('class_name')
            ->get();

        $classRoom = null;
        $subjectGrades = collect();
        $cards = collect();
        $subjects = collect();
        $gradeTypes = collect();
        $sort = $request->query('sort', 'name') === 'avg' ? 'avg' : 'name';
        $q = trim((string) $request->query('q'));

        if ($waliClasses->isNotEmpty()) {
            $classRoom = $waliClasses
                ->firstWhere('id', $request->query('class_id'))
                ?: $waliClasses->first();

            if (! $user->isWaliKelasFor($classRoom)) {
                abort(403);
            }

            $gradeTypes = GradeType::where('school_id', $user->school_id)
                ->active()
                ->ordered()
                ->get();

            // Mapel yang benar-benar diajarkan di kelas ini (plotting), fallback semua mapel sekolah
            $subjects = ClassSubjectTeacher::where('class_id', $classRoom->id)
                ->with('subject')
                ->get()
                ->pluck('subject')
                ->filter(fn ($s) => $s)
                ->unique('id')
                ->sortBy('name')
                ->values();

            if ($subjects->isEmpty()) {
                $subjects = Subject::where('school_id', $user->school_id)->orderBy('name')->get();
            }

            $students = Student::where('class_id', $classRoom->id)->orderBy('name')->get();
            $studentIds = $students->pluck('id')->toArray();

            $gradeQuery = Grade::whereIn('student_id', $studentIds);
            if ($ayId) {
                $gradeQuery->where(function ($w) use ($ayId) {
                    $w->where('academic_year_id', $ayId)->orWhereNull('academic_year_id');
                });
            }
            $allGrades = $gradeQuery->with('subject')->get();

            $tahQuery = TahfidzRecord::whereIn('student_id', $studentIds);
            if ($ayId) {
                $tahQuery->where('academic_year_id', $ayId);
            }
            $allTahfidz = $tahQuery->with('quranMaster')->get()->groupBy('student_id');

            $subjectGrades = $allGrades->groupBy('student_id');

            $cards = $students->map(function ($student) use ($subjectGrades, $subjects, $gradeTypes, $allTahfidz) {
                $sg = $subjectGrades->get($student->id, collect());

                $rows = $subjects->map(function ($sub) use ($sg, $gradeTypes) {
                    $items = $sg->where('subject_id', $sub->id);
                    $scores = $items->map(fn ($g) => $g->finalScore())
                        ->filter(fn ($s) => $s !== null)
                        ->values();

                    $byType = $items->groupBy('grade_type_id')->map(function ($t) {
                        $sc = $t->map(fn ($g) => $g->finalScore())
                            ->filter(fn ($s) => $s !== null)
                            ->values();

                        return $sc->isNotEmpty() ? round($sc->avg(), 1) : null;
                    });

                    $weightedSum = 0;
                    $totalWeight = 0;
                    foreach ($byType as $typeId => $avg) {
                        if ($avg === null) {
                            continue;
                        }
                        $gt = $gradeTypes->firstWhere('id', $typeId);
                        if ($gt) {
                            $weightedSum += $avg * $gt->weight;
                            $totalWeight += $gt->weight;
                        }
                    }

                    $final = null;
                    if ($scores->isNotEmpty()) {
                        $final = $totalWeight >= 100
                            ? round($weightedSum / $totalWeight, 1)
                            : round($scores->avg(), 1);
                    }

                    $kkm = (int) ($sub->kkm ?? 80);

                    return [
                        'subject' => $sub,
                        'final' => $final,
                        'kkm' => $kkm,
                        'has_data' => $final !== null,
                        'below_kkm' => $final !== null && $final < $kkm,
                    ];
                });

                $dinilai = $rows->where('has_data', true)->count();
                $finals = $rows->where('has_data', true)->pluck('final');

                $tah = $allTahfidz->get($student->id);
                $tahfidz = null;
                if ($tah) {
                    $tahfidz = [
                        'total' => $tah->count(),
                        'ayat' => $tah->sum(fn ($r) => $r->ayat_end - $r->ayat_start + 1),
                        'surahs' => $tah->pluck('quranMaster.surah_name')->filter()->unique()->count(),
                        'avg_score' => round($tah->avg('score'), 1),
                    ];
                }

                return [
                    'student' => $student,
                    'rows' => $rows,
                    'dinilai' => $dinilai,
                    'total_subjects' => $subjects->count(),
                    'avg' => $finals->isNotEmpty() ? round($finals->avg(), 1) : null,
                    'below_kkm' => $rows->where('below_kkm', true)->count(),
                    'tahfidz' => $tahfidz,
                ];
            });

            if ($sort === 'avg') {
                $cards = $cards->sortBy(fn ($c) => $c['avg'] ?? PHP_FLOAT_MAX);
            } else {
                $cards = $cards->sortBy(fn ($c) => $c['student']->name);
            }
            $cards = $cards->values();

            if ($q !== '') {
                $cards = $cards->filter(fn ($c) => str_contains(strtolower($c['student']->name), strtolower($q)));
            }
        }

        return view('guru.rapor.index', compact(
            'waliClasses', 'classRoom', 'cards', 'subjects', 'gradeTypes', 'sort', 'q'
        ));
    }

    public function student(Request $request, Student $student): View
    {
        $user = auth()->user();

        if ($student->school_id !== $user->school_id) {
            abort(403);
        }

        if (! $student->classRoom || ! $user->isWaliKelasFor($student->classRoom)) {
            abort(403);
        }

        $semester = in_array((int) $request->query('semester', 1), [1, 2], true)
            ? (int) $request->query('semester', 1)
            : 1;

        $ctx = app(RaporDataService::class)->forStudent($user, $student, $semester);

        $template = RaporFormat::activeTemplate($user->school_id, $this->academicYearContext->id());
        $blocks = $template
            ? RaporFormat::normalize($template->blocks)
            : RaporFormat::defaultBlocks();

        return view('guru.rapor.student', compact('student', 'semester', 'ctx', 'blocks'));
    }

    public static function predikat(float $score): string
    {
        return match (true) {
            $score >= 90 => 'A',
            $score >= 80 => 'B',
            $score >= 70 => 'C',
            $score >= 60 => 'D',
            default => 'E',
        };
    }
}
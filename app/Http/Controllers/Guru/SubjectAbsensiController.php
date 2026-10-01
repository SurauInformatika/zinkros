<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSubject;
use App\Models\ClassRoom;
use App\Models\ClassSubjectTeacher;
use App\Models\Student;
use App\Models\Subject;
use App\Services\AcademicYearContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class SubjectAbsensiController extends Controller
{
    public function __construct(protected AcademicYearContext $academicYearContext) {}

    public function index(): View
    {
        $user = auth()->user();

        $plottedClasses = ClassSubjectTeacher::where('teacher_id', $user->id)
            ->when($this->academicYearContext->exists(), fn ($q) => $q->where(function ($q2) {
                $q2->where('academic_year_id', $this->academicYearContext->id())
                    ->orWhereNull('academic_year_id');
            }))
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
        $recentAbsensi = AttendanceSubject::where('teacher_id', $user->id)
            ->when($this->academicYearContext->exists(), fn ($q) => $q->where(function ($q2) {
                $q2->where('academic_year_id', $this->academicYearContext->id())
                    ->orWhereNull('academic_year_id');
            }))
            ->where('date', $today)
            ->with('subject')
            ->get()
            ->groupBy('subject_id')
            ->map(function ($items, $subjectId) {
                $subject = Subject::find($subjectId);
                return [
                    'subject' => $subject?->name ?? '-',
                    'count' => $items->count(),
                    'hadir' => $items->where('status', 'HADIR')->count(),
                    'izin' => $items->where('status', 'IZIN')->count(),
                    'sakit' => $items->where('status', 'SAKIT')->count(),
                    'alpa' => $items->where('status', 'ALPA')->count(),
                ];
            })
            ->values();

        return view('guru.absensi-mapel.index', compact('plottedClasses', 'recentAbsensi', 'today'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        $user = auth()->user();
        $classId = $request->query('class_id');
        $subjectId = $request->query('subject_id');

        if (!$classId || !$subjectId) {
            return redirect()->route('guru.absensi-mapel.index')
                ->with('error', 'Pilih kelas dan mata pelajaran terlebih dahulu.');
        }

        $plot = ClassSubjectTeacher::where('teacher_id', $user->id)
            ->when($this->academicYearContext->exists(), fn ($q) => $q->where(function ($q2) {
                $q2->where('academic_year_id', $this->academicYearContext->id())
                    ->orWhereNull('academic_year_id');
            }))
            ->where('class_id', $classId)
            ->where('subject_id', $subjectId)
            ->first();

        if (!$plot) {
            return redirect()->route('guru.absensi-mapel.index')
                ->with('error', 'Anda tidak terploting untuk kelas dan mata pelajaran ini.');
        }

        $classRoom = ClassRoom::findOrFail($classId);
        $subject = Subject::findOrFail($subjectId);
        $students = Student::where('class_id', $classId)->orderBy('name')->get();
        $date = $request->query('date', Carbon::today()->toDateString());

        $existingAbsensi = AttendanceSubject::where('teacher_id', $user->id)
            ->when($this->academicYearContext->exists(), fn ($q) => $q->where(function ($q2) {
                $q2->where('academic_year_id', $this->academicYearContext->id())
                    ->orWhereNull('academic_year_id');
            }))
            ->where('subject_id', $subjectId)
            ->where('date', $date)
            ->pluck('status', 'student_id')
            ->toArray();

        $hasSubmitted = count($existingAbsensi) > 0;

        $existingTopic = AttendanceSubject::where('teacher_id', $user->id)
            ->when($this->academicYearContext->exists(), fn ($q) => $q->where(function ($q2) {
                $q2->where('academic_year_id', $this->academicYearContext->id())
                    ->orWhereNull('academic_year_id');
            }))
            ->where('subject_id', $subjectId)
            ->where('date', $date)
            ->whereNotNull('topic')
            ->value('topic');

        return view('guru.absensi-mapel.create', compact(
            'classRoom', 'subject', 'students', 'date', 'existingAbsensi', 'hasSubmitted', 'existingTopic'
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
            'topic' => 'nullable|string|max:255',
            'absensi' => 'required|array',
            'absensi.*.student_id' => 'required|uuid',
            'absensi.*.status' => 'required|in:HADIR,IZIN,SAKIT,ALPA',
            'absensi.*.notes' => 'nullable|string|max:255',
        ]);

        $plot = ClassSubjectTeacher::where('teacher_id', $user->id)
            ->when($this->academicYearContext->exists(), fn ($q) => $q->where(function ($q2) {
                $q2->where('academic_year_id', $this->academicYearContext->id())
                    ->orWhereNull('academic_year_id');
            }))
            ->where('class_id', $validated['class_id'])
            ->where('subject_id', $validated['subject_id'])
            ->first();

        if (!$plot) {
            return back()->with('error', 'Anda tidak terploting untuk kelas ini.');
        }

        $now = Carbon::now();

        foreach ($validated['absensi'] as $item) {
            AttendanceSubject::updateOrCreate(
                [
                    'teacher_id' => $user->id,
                    'subject_id' => $validated['subject_id'],
                    'student_id' => $item['student_id'],
                    'date' => $validated['date'],
                ],
                [
                    'school_id' => $schoolId,
                    'status' => $item['status'],
                    'timestamp' => $now->toTimeString(),
                    'topic' => $validated['topic'] ?? null,
                    'notes' => $item['notes'] ?? null,
                ]
            );
        }

        return redirect()->route('guru.absensi-mapel.index')
            ->with('success', 'Absensi mapel berhasil disimpan untuk ' . $validated['date']);
    }

    public function rekapMapel(Request $request): View
    {
        $user = auth()->user();

        $plottedClassIds = ClassSubjectTeacher::where('teacher_id', $user->id)
            ->when($this->academicYearContext->exists(), fn ($q) => $q->where(function ($q2) {
                $q2->where('academic_year_id', $this->academicYearContext->id())
                    ->orWhereNull('academic_year_id');
            }))
            ->pluck('class_id')
            ->unique();

        $classes = ClassRoom::whereIn('id', $plottedClassIds)
            ->orderBy('grade_level')
            ->orderBy('class_name')
            ->get();

        $subjects = Subject::whereHas('classSubjectTeachers', function ($q) use ($user) {
            $q->where('teacher_id', $user->id);
        })->orderBy('type')->orderBy('name')->get();

        $classId = $request->query('class_id');
        $subjectId = $request->query('subject_id');
        $dateFrom = $request->query('date_from', Carbon::now()->startOfMonth()->toDateString());
        $dateTo = $request->query('date_to', Carbon::today()->toDateString());

        $query = AttendanceSubject::where('teacher_id', $user->id)
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->with(['subject', 'student']);

        if ($classId) {
            $studentIds = Student::where('class_id', $classId)->pluck('id');
            $query->whereIn('student_id', $studentIds);
        }

        if ($subjectId) {
            $query->where('subject_id', $subjectId);
        }

        if ($this->academicYearContext->exists()) {
            $query->where(function ($q2) {
                $q2->where('academic_year_id', $this->academicYearContext->id())
                    ->orWhereNull('academic_year_id');
            });
        }

        $sessions = $query->get()
            ->groupBy(fn ($r) => $r->date . '|' . $r->subject_id . '|' . ($r->student->class_id ?? ''))
            ->map(function ($records) {
                $first = $records->first();
                $total = $records->count();
                return [
                    'date' => $first->date,
                    'subject' => $first->subject,
                    'class_id' => $first->student->class_id ?? null,
                    'class_name' => $first->student->classRoom?->class_name ?? '-',
                    'topic' => $first->topic,
                    'total' => $total,
                    'hadir' => $records->where('status', 'HADIR')->count(),
                    'sakit' => $records->where('status', 'SAKIT')->count(),
                    'izin' => $records->where('status', 'IZIN')->count(),
                    'alpa' => $records->where('status', 'ALPA')->count(),
                    'persentase' => $total > 0 ? round($records->where('status', 'HADIR')->count() / $total * 100, 1) : 0,
                    'students' => $records->pluck('student_id')->toArray(),
                ];
            })
            ->sortByDesc('date')
            ->values();

        $chartLabels = $sessions->reverse()->pluck('date')->values();
        $chartHadir = $sessions->reverse()->pluck('hadir')->values();
        $chartSakit = $sessions->reverse()->pluck('sakit')->values();
        $chartIzin = $sessions->reverse()->pluck('izin')->values();
        $chartAlpa = $sessions->reverse()->pluck('alpa')->values();

        $chartTitle = 'Grafik Kehadiran — ' . $sessions->count() . ' Sesi';

        return view('guru.absensi-mapel.rekap', compact(
            'classes', 'subjects', 'sessions', 'classId', 'subjectId',
            'dateFrom', 'dateTo', 'chartLabels', 'chartHadir', 'chartSakit', 'chartIzin', 'chartAlpa', 'chartTitle'
        ));
    }

    public function rekapDetail(Request $request): View
    {
        $user = auth()->user();
        $date = $request->query('date');
        $subjectId = $request->query('subject_id');
        $classId = $request->query('class_id');

        if (!$date || !$subjectId || !$classId) {
            return redirect()->route('guru.absensi-mapel.rekap')
                ->with('error', 'Parameter tidak lengkap.');
        }

        $subject = Subject::find($subjectId);
        $classRoom = ClassRoom::find($classId);

        $studentIds = Student::where('class_id', $classId)->pluck('id');

        $records = AttendanceSubject::where('teacher_id', $user->id)
            ->where('date', $date)
            ->where('subject_id', $subjectId)
            ->whereIn('student_id', $studentIds)
            ->with('student')
            ->get();

        $topic = $records->first()?->topic;

        $students = $records->map(fn ($r) => [
            'name' => $r->student?->name ?? '-',
            'nisn' => $r->student?->nisn ?? '-',
            'status' => $r->status,
            'notes' => $r->notes,
        ])->sortBy('name')->values();

        $stats = [
            'total' => $records->count(),
            'hadir' => $records->where('status', 'HADIR')->count(),
            'sakit' => $records->where('status', 'SAKIT')->count(),
            'izin' => $records->where('status', 'IZIN')->count(),
            'alpa' => $records->where('status', 'ALPA')->count(),
        ];

        return view('guru.absensi-mapel.rekap-detail', compact(
            'date', 'subject', 'classRoom', 'topic', 'students', 'stats'
        ));
    }

    public function history(Request $request): View
    {
        $user = auth()->user();

        $query = AttendanceSubject::where('teacher_id', $user->id)
            ->with(['subject', 'student.classRoom']);

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        $absensi = $query->orderByDesc('date')
            ->orderBy('student_id')
            ->paginate(30)
            ->withQueryString();

        $subjects = Subject::whereHas('classSubjectTeachers', function ($q) use ($user) {
            $q->where('teacher_id', $user->id);
        })->get();

        return view('guru.absensi-mapel.history', compact('absensi', 'subjects'));
    }
}

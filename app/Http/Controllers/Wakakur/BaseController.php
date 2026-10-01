<?php

namespace App\Http\Controllers\Wakakur;

use App\Http\Controllers\Controller;
use App\Models\AcademicCalendar;
use App\Models\AcademicYear;
use App\Models\CalendarLevelStructure;
use App\Models\ClassDaySchedule;
use App\Models\ClassHomeroom;
use App\Models\ClassRoom;
use App\Models\ClassSubjectTeacher;
use App\Models\Student;
use App\Models\Subject;
use App\Models\TeacherRole;
use App\Models\TeacherSubject;
use App\Models\User;
use App\Services\AcademicYearContext;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BaseController extends Controller
{
    public function __construct(protected AcademicYearContext $academicYearContext) {}

    protected function routeGroup(): string
    {
        $name = (string) request()->route()?->getName();

        return str_contains($name, '.') ? substr($name, 0, strrpos($name, '.')) : $name;
    }

    public function classes(Request $request): View
    {
        $classes = ClassRoom::with(['walis:id,name'])
            ->where('school_id', $request->user()->school_id)
            ->withCount('students')
            ->orderBy('grade_level')
            ->orderBy('class_name')
            ->get();

        $tab = $request->query('tab', 'kelas') === 'siswa' ? 'siswa' : 'kelas';

        $studentsQuery = Student::with(['classRoom:id,class_name,grade_level'])
            ->where('school_id', $request->user()->school_id);

        $search = trim((string) $request->query('search'));
        $kelasFilter = (string) $request->query('kelas');
        $gender = (string) $request->query('gender');

        if ($search !== '') {
            $studentsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        if ($kelasFilter !== '') {
            $studentsQuery->where('class_id', $kelasFilter);
        }

        if (in_array($gender, ['L', 'P'], true)) {
            $studentsQuery->where('gender', $gender);
        }

        $students = $studentsQuery->orderBy('name')->get();

        return view('wakakur.base.classes', [
            'classes' => $classes,
            'students' => $students,
            'tab' => $tab,
            'filters' => ['search' => $search, 'kelas' => $kelasFilter, 'gender' => $gender],
        ]);
    }

    public function classDetail(Request $request, ClassRoom $kelas): View
    {
        abort_unless($kelas->school_id === $request->user()->school_id, 403);

        $kelas->load('walis:id,name');
        $students = $kelas->students()->orderBy('name')->get();

        return view('wakakur.base.class-detail', compact('kelas', 'students'));
    }

    public function students(Request $request): View
    {
        $students = Student::with(['classRoom:id,class_name,grade_level'])
            ->where('school_id', $request->user()->school_id)
            ->orderBy('name')
            ->get();

        return view('wakakur.base.students', compact('students'));
    }

    public function teachers(Request $request): View|RedirectResponse
    {
        $schoolId = $request->user()->school_id;

        if ($request->query('view') === 'kelas') {
            $classes = ClassRoom::with(['walis:id,name'])
                ->where('school_id', $schoolId)
                ->withCount('subjects')
                ->orderBy('grade_level')
                ->orderBy('class_name')
                ->get();

            return view('wakakur.base.teachers', ['segment' => 'kelas', 'classes' => $classes, 'routeGroup' => $this->routeGroup()]);
        }

        $gender = $request->query('gender');
        $tugas = array_values(array_intersect((array) $request->query('tugas', []), ['wali', 'mapel', 'quran']));
        $q = trim((string) $request->query('q', ''));

        $query = User::query()
            ->whereIn('role', [User::ROLE_GURU, User::ROLE_KEPSEK, User::ROLE_WAKASEK])
            ->where('school_id', $schoolId)
            ->with(['waliClasses:id,class_name'])
            ->withCount(['waliClasses as wali_classes_count'])
            ->withCount(['classSubjectTeachers as mapel_count' => function ($qBuilder) {
                if ($this->academicYearContext->exists()) {
                    $qBuilder->where('academic_year_id', $this->academicYearContext->id());
                }
            }])
            ->withExists(['subjects as quran_subject_exists' => function ($qBuilder) {
                $qBuilder->where('subjects.type', Subject::TYPE_QURAN);
            }])
            ->withExists(['taughtSubjects as quran_taught_exists' => function ($qBuilder) {
                $qBuilder->where('subjects.type', Subject::TYPE_QURAN);
            }]);

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', '%' . $q . '%')
                    ->orWhere('email', 'like', '%' . $q . '%');
            });
        }

        $teachersPaginator = $query->orderByRaw("CASE role WHEN '" . User::ROLE_KEPSEK . "' THEN 0 WHEN '" . User::ROLE_WAKASEK . "' THEN 1 ELSE 2 END")
            ->orderBy('name')
            ->paginate(25);

        $subjectNames = ClassSubjectTeacher::query()
            ->with('subject:id,name,type')
            ->where('school_id', $schoolId)
            ->whereIn('teacher_id', $teachersPaginator->pluck('id'));

        if ($this->academicYearContext->exists()) {
            $subjectNames->where('academic_year_id', $this->academicYearContext->id());
        }

        $subjectNames = $subjectNames->get()
            ->filter(fn ($cst) => $cst->subject && $cst->subject->type !== Subject::TYPE_QURAN)
            ->groupBy('teacher_id')
            ->map(fn ($rows) => $rows->map(fn ($r) => $r->subject->name)->unique()->values());

        $school = $request->user()->school;

        $teachers = $teachersPaginator->getCollection()->map(function ($teacher) use ($subjectNames, $school) {
            $labels = [];

            if ($teacher->isKepsek()) {
                $labels[] = $school->roleLabel(User::ROLE_KEPSEK);
            } elseif ($teacher->isWakasek()) {
                $labels[] = $teacher->wakasekPositionLabel() ?? $school->roleLabel(User::ROLE_WAKASEK);
            }

            foreach ($teacher->waliClasses as $kelas) {
                $labels[] = 'Wali Kelas ' . $kelas->class_name;
            }

            foreach ($subjectNames->get($teacher->id, collect()) as $subjectName) {
                $labels[] = 'Guru ' . $subjectName;
            }

            if ($teacher->quran_subject_exists || $teacher->quran_taught_exists) {
                $labels[] = 'Guru Al-Quran';
            }

            $teacher->tugas = $labels;

            return $teacher;
        });

        if (in_array($gender, ['L', 'P'], true)) {
            $teachers = $teachers->filter(fn ($teacher) => $teacher->gender === $gender);
        }

        if (in_array('wali', $tugas, true)) {
            $teachers = $teachers->filter(fn ($teacher) => $teacher->wali_classes_count > 0);
        }

        if (in_array('mapel', $tugas, true)) {
            $teachers = $teachers->filter(fn ($teacher) => $teacher->mapel_count > 0);
        }

        if (in_array('quran', $tugas, true)) {
            $teachers = $teachers->filter(fn ($teacher) => $teacher->quran_subject_exists || $teacher->quran_taught_exists);
        }

        $paginated = new LengthAwarePaginator(
            $teachers->values(),
            $teachersPaginator->total(),
            $teachersPaginator->perPage(),
            $teachersPaginator->currentPage(),
            array_merge($request->query(), ['view' => 'guru'])
        );

        return view('wakakur.base.teachers', [
            'segment' => 'guru',
            'teachers' => $paginated,
            'filters' => ['gender' => $gender, 'tugas' => $tugas, 'q' => $q],
            'routeGroup' => $this->routeGroup(),
        ]);
    }

    public function teacherDetail(Request $request, User $guru): View
    {
        abort_unless($guru->isGuru(), 404);
        abort_unless($guru->school_id === $request->user()->school_id, 403);

        $guru->load([
            'waliClasses:id,class_name,grade_level',
            'teacherRoles:id,teacher_id,role_name,is_student_related,description',
            'createdBy:id,name',
        ]);

        $plotting = $guru->classSubjectTeachers()
            ->with(['subject:id,name,type', 'classRoom:id,class_name,grade_level'])
            ->get();

        $prioritySubjects = $guru->subjects()->orderBy('name')->get(['subjects.id', 'subjects.name', 'subjects.type']);
        $quranAssignments = $guru->quranTeachingAssignments()
            ->with(['student:id,name,class_id', 'student.classRoom:id,class_name'])
            ->get();
        $quranAssignmentCount = $quranAssignments->count();

        $isTahfidzTeacher = (bool) $guru->is_pj_tahfidz || $guru->isQuranTeacher() || $guru->hasQuranAssignment();

        $allClasses = ClassRoom::where('school_id', $guru->school_id)
            ->orderBy('grade_level')
            ->orderBy('class_name')
            ->get(['id', 'class_name', 'grade_level']);

        $allSubjects = Subject::where('school_id', $guru->school_id)
            ->orderBy('name')
            ->get(['id', 'name', 'type']);

        $activeYear = $this->academicYearContext->exists()
            ? $this->academicYearContext->get()->name
            : null;

        return view('wakakur.base.teacher-detail', compact(
            'guru',
            'plotting',
            'prioritySubjects',
            'quranAssignments',
            'quranAssignmentCount',
            'isTahfidzTeacher',
            'allClasses',
            'allSubjects',
            'activeYear'
        ))->with('routeGroup', $this->routeGroup());
    }

    public function updateWaliKelas(Request $request, User $guru): RedirectResponse
    {
        $schoolId = $request->user()->school_id;

        abort_unless($guru->isGuru() && $guru->school_id === $schoolId, 403);

        $validated = $request->validate([
            'class_id' => ['nullable', 'uuid'],
        ]);

        $target = $validated['class_id'] ?? null;

        ClassHomeroom::where('user_id', $guru->id)->delete();
        User::where('id', $guru->id)->update(['is_wali_kelas' => false]);

        if ($target) {
            $class = ClassRoom::where('school_id', $schoolId)->find($target);

            if (! $class) {
                return back()->withErrors(['class_id' => 'Kelas tidak ditemukan di sekolah ini.']);
            }

            $existing = ClassHomeroom::where('class_id', $class->id)->count();
            if ($existing >= 2) {
                return back()->withErrors([
                    'class_id' => 'Kelas ' . $class->class_name . ' sudah memiliki 2 wali kelas.',
                ]);
            }

            ClassHomeroom::create([
                'school_id' => $schoolId,
                'class_id' => $class->id,
                'user_id' => $guru->id,
                'label' => null,
                'sort' => $existing + 1,
            ]);

            User::where('id', $guru->id)->update(['is_wali_kelas' => true]);

            return back()->with('status', $guru->name . ' kini wali kelas ' . $class->class_name . '.');
        }

        return back()->with('status', 'Wali kelas berhasil dilepas dari ' . $guru->name . '.');
    }

    public function storePlotting(Request $request, User $guru): RedirectResponse
    {
        $schoolId = $request->user()->school_id;

        abort_unless($guru->isGuru() && $guru->school_id === $schoolId, 403);

        $validated = $request->validate([
            'class_ids' => ['required', 'array', 'min:1'],
            'class_ids.*' => ['uuid'],
            'subject_id' => ['required', 'uuid'],
        ]);

        $subject = Subject::where('school_id', $schoolId)->find($validated['subject_id']);

        if (! $subject) {
            return back()->withErrors(['class_ids' => 'Mapel tidak ditemukan di sekolah ini.'])->with('error', 'Gagal: mapel tidak ditemukan di sekolah ini.');
        }

        $academicYearId = $this->academicYearContext->exists() ? $this->academicYearContext->id() : null;

        $classes = ClassRoom::where('school_id', $schoolId)
            ->whereIn('id', array_values(array_unique($validated['class_ids'])))
            ->get(['id', 'class_name']);

        if ($classes->isEmpty()) {
            return back()->withErrors(['class_ids' => 'Kelas tidak ditemukan di sekolah ini.'])->with('error', 'Gagal: kelas tidak ditemukan di sekolah ini.');
        }

        $created = 0;
        $skipped = 0;

        foreach ($classes as $class) {
            $exists = ClassSubjectTeacher::where('school_id', $schoolId)
                ->where('class_id', $class->id)
                ->where('subject_id', $subject->id)
                ->where('academic_year_id', $academicYearId)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            ClassSubjectTeacher::create([
                'school_id' => $schoolId,
                'class_id' => $class->id,
                'subject_id' => $subject->id,
                'teacher_id' => $guru->id,
                'academic_year_id' => $academicYearId,
            ]);

            $created++;
        }

        if ($created === 0) {
            return back()->withErrors(['class_ids' => $subject->name . ' sudah ditugaskan di semua kelas yang dipilih.'])->with('error', 'Tidak ada kelas baru: ' . $subject->name . ' sudah diplot di seluruh kelas yang dipilih.');
        }

        return back()->with('status', 'Plotting ' . $subject->name . ' untuk ' . $created . ' kelas ditambahkan ke ' . $guru->name . ($skipped ? ' (' . $skipped . ' sudah lebih dulu ada).' : '.'));
    }

    public function destroyPlottingMany(Request $request, User $guru): RedirectResponse
    {
        $schoolId = $request->user()->school_id;

        abort_unless($guru->isGuru() && $guru->school_id === $schoolId, 403);

        $validated = $request->validate([
            'plot_ids' => ['required', 'array', 'min:1'],
            'plot_ids.*' => ['uuid'],
        ]);

        $deleted = ClassSubjectTeacher::where('school_id', $schoolId)
            ->where('teacher_id', $guru->id)
            ->whereIn('id', array_values(array_unique($validated['plot_ids'])))
            ->delete();

        if ($deleted === 0) {
            return back()->withErrors(['plot_ids' => 'Plotting terpilih tidak ditemukan.'])->with('error', 'Gagal: plotting terpilih tidak ditemukan.');
        }

        return back()->with('status', $deleted . ' plotting ' . $guru->name . ' dihapus.');
    }

    public function destroyPlotting(Request $request, User $guru, ClassSubjectTeacher $classSubjectTeacher): RedirectResponse
    {
        abort_unless($guru->isGuru() && $guru->school_id === $request->user()->school_id, 403);
        abort_unless(
            $classSubjectTeacher->school_id === $request->user()->school_id
                && $classSubjectTeacher->teacher_id === $guru->id,
            403
        );

        $classSubjectTeacher->delete();

        return back()->with('status', 'Plotting dihapus.');
    }

    public function updatePrioritySubjects(Request $request, User $guru): RedirectResponse
    {
        $schoolId = $request->user()->school_id;

        abort_unless($guru->isGuru() && $guru->school_id === $schoolId, 403);

        $validated = $request->validate([
            'subject_ids' => ['array'],
            'subject_ids.*' => ['uuid'],
        ]);

        $ids = array_values(array_unique($validated['subject_ids'] ?? []));

        $valid = Subject::where('school_id', $schoolId)
            ->whereIn('id', $ids)
            ->pluck('id')
            ->values()
            ->all();

        TeacherSubject::where('school_id', $schoolId)
            ->where('teacher_id', $guru->id)
            ->delete();

        foreach ($valid as $subjectId) {
            TeacherSubject::create([
                'school_id' => $schoolId,
                'teacher_id' => $guru->id,
                'subject_id' => $subjectId,
            ]);
        }

        return back()->with('status', 'Mapel prioritas ' . $guru->name . ' diperbarui.');
    }

    public function storeTeacherRole(Request $request, User $guru): RedirectResponse
    {
        $schoolId = $request->user()->school_id;

        abort_unless($guru->isGuru() && $guru->school_id === $schoolId, 403);

        $validated = $request->validate([
            'role_name' => ['required', 'string', 'max:100'],
            'is_student_related' => ['sometimes', 'boolean'],
        ]);

        TeacherRole::create([
            'school_id' => $schoolId,
            'teacher_id' => $guru->id,
            'role_name' => $validated['role_name'],
            'is_student_related' => $validated['is_student_related'] ?? false,
        ]);

        return back()->with('status', 'Tugas khusus "' . $validated['role_name'] . '" ditambahkan untuk ' . $guru->name . '.');
    }

    public function destroyTeacherRole(Request $request, User $guru, TeacherRole $teacherRole): RedirectResponse
    {
        abort_unless($guru->isGuru() && $guru->school_id === $request->user()->school_id, 403);
        abort_unless(
            $teacherRole->school_id === $request->user()->school_id
                && $teacherRole->teacher_id === $guru->id,
            403
        );

        $teacherRole->delete();

        return back()->with('status', 'Tugas khusus dihapus.');
    }

    public function plotting(): RedirectResponse
    {
        return redirect()->route('wakasek.base.teachers', ['view' => 'kelas']);
    }

    public function plottingDetail(Request $request, ClassRoom $class): View
    {
        abort_unless($class->school_id === $request->user()->school_id, 403);

        $class->load('walis:id,name');

        $query = ClassSubjectTeacher::with(['subject', 'teacher:id,name'])
            ->where('class_id', $class->id);

        if ($this->academicYearContext->exists()) {
            $query->where('academic_year_id', $this->academicYearContext->id());
        }

        $plotting = $query->get()->groupBy(fn ($item) => $item->subject->name);

        return view('wakakur.base.plotting-detail', compact('class', 'plotting'))->with('routeGroup', $this->routeGroup());
    }

    public function roster(Request $request): View
    {
        $mode = match ($request->query('view', 'mapel')) {
            'jadwal', 'guru' => $request->query('view'),
            default => 'mapel',
        };

        $schoolId = $request->user()->school_id;
        $q = trim((string) $request->query('q', ''));

        $classes = ClassRoom::with(['walis:id,name'])
            ->where('school_id', $schoolId)
            ->when($q !== '', fn ($query) => $query->where('class_name', 'like', '%' . $q . '%'))
            ->orderBy('grade_level')
            ->orderBy('class_name')
            ->get();

        $fallbackId = $this->academicYearContext->exists() ? $this->academicYearContext->id() : null;
        $ta = $request->query('ta', $fallbackId);
        if ($ta !== null && AcademicYear::where('school_id', $schoolId)->where('id', $ta)->doesntExist()) {
            $ta = $fallbackId;
        }

        $academicYears = AcademicYear::where('school_id', $schoolId)
            ->orderByDesc('start_date')
            ->get(['id', 'name']);

        $jpByDay = $this->jpPerDay($schoolId, $ta);

        if ($mode === 'jadwal') {
            $schedules = ClassDaySchedule::with(['subject:id,name,type', 'teacher:id,name'])
                ->where('school_id', $schoolId)
                ->when($ta !== null, fn ($query) => $query->where('academic_year_id', $ta))
                ->get()
                ->groupBy('class_id');

            $jpByClass = $this->jpPerClass($schoolId, $ta);

            return view('wakakur.base.roster', compact('mode', 'classes', 'schedules', 'jpByClass', 'jpByDay', 'academicYears', 'ta', 'q'));
        }

        if ($mode === 'guru') {
            $teacherIds = ClassSubjectTeacher::where('school_id', $schoolId)
                ->when($ta !== null, fn ($query) => $query->where('academic_year_id', $ta))
                ->pluck('teacher_id')
                ->unique()
                ->values();
            $teachers = User::whereIn('id', $teacherIds)->orderBy('name')->get(['id', 'name']);
            $selectedGuru = $request->query('guru');
            if ($selectedGuru !== null && User::where('school_id', $schoolId)->where('id', $selectedGuru)->doesntExist()) {
                $selectedGuru = null;
            }
            $guruSchedules = collect();
            if ($selectedGuru !== null) {
                $guruSchedules = ClassDaySchedule::with(['classRoom:id,class_name', 'subject:id,name,type'])
                    ->where('school_id', $schoolId)
                    ->where('teacher_id', $selectedGuru)
                    ->when($ta !== null, fn ($query) => $query->where('academic_year_id', $ta))
                    ->get()
                    ->groupBy('day_name');
            }

            return view('wakakur.base.roster', compact('mode', 'classes', 'guruSchedules', 'teachers', 'selectedGuru', 'jpByDay', 'academicYears', 'ta', 'q'));
        }

        $rosterRows = ClassSubjectTeacher::with(['subject:id,name,type', 'teacher:id,name'])
            ->where('school_id', $schoolId)
            ->when($ta !== null, fn ($query) => $query->where('academic_year_id', $ta))
            ->whereIn('class_id', $classes->pluck('id'))
            ->get()
            ->groupBy('class_id')
            ->map(fn ($group) => $group->sortBy(fn ($row) => $row->subject?->name)->values());

        return view('wakakur.base.roster', compact('mode', 'classes', 'rosterRows', 'jpByDay', 'academicYears', 'ta', 'q'));
    }

    public function kepsekRoster(Request $request): View
    {
        $schoolId = $request->user()->school_id;

        $classes = ClassRoom::with(['walis:id,name'])
            ->where('school_id', $schoolId)
            ->orderBy('grade_level')
            ->orderBy('class_name')
            ->get();

        $fallbackId = $this->academicYearContext->exists() ? $this->academicYearContext->id() : null;
        $ta = $request->query('ta', $fallbackId);
        if ($ta !== null && AcademicYear::where('school_id', $schoolId)->where('id', $ta)->doesntExist()) {
            $ta = $fallbackId;
        }

        $selectedKelas = $classes->firstWhere('id', $request->query('kelas')) ?? $classes->first();

        $rosterRows = collect();
        if ($selectedKelas) {
            $rosterRows = ClassSubjectTeacher::with(['subject:id,name,type', 'teacher:id,name'])
                ->where('school_id', $schoolId)
                ->where('class_id', $selectedKelas->id)
                ->when($ta !== null, fn ($query) => $query->where('academic_year_id', $ta))
                ->get()
                ->sortBy(fn ($row) => $row->subject?->name)
                ->values();
        }

        $jpByDay = $this->jpPerDay($schoolId, $ta);

        $academicYears = AcademicYear::where('school_id', $schoolId)
            ->orderByDesc('start_date')
            ->get(['id', 'name']);

        return view('kepsek.roster', compact('classes', 'selectedKelas', 'rosterRows', 'jpByDay', 'ta', 'academicYears'));
    }

    private function jpPerDay(string $schoolId, ?string $taId): array
    {
        $days = ClassDaySchedule::DAYS;
        if ($taId === null) {
            return array_fill_keys($days, 8);
        }

        $calendar = AcademicCalendar::where('school_id', $schoolId)
            ->where('academic_year_id', $taId)
            ->where('status', AcademicCalendar::STATUS_FINAL)
            ->orderByDesc('version')
            ->first();

        if (!$calendar) {
            return array_fill_keys($days, 8);
        }

        $rows = CalendarLevelStructure::where('academic_calendar_id', $calendar->id)->get();

        $result = array_fill_keys($days, 0);
        foreach ($rows as $ls) {
            foreach ($days as $day) {
                $result[$day] = max($result[$day], $ls->jpForDay($day));
            }
        }

        return $result;
    }

    private function jpPerClass(string $schoolId, ?string $taId): array
    {
        $days = ClassDaySchedule::DAYS;
        $result = [];

        $classes = ClassRoom::where('school_id', $schoolId)
            ->orderBy('grade_level')
            ->orderBy('class_name')
            ->get(['id', 'grade_level']);

        if ($classes->isEmpty()) {
            return $result;
        }

        if ($taId === null) {
            foreach ($classes as $class) {
                $result[$class->id] = array_fill_keys($days, 8);
            }
            return $result;
        }

        $calendar = AcademicCalendar::where('school_id', $schoolId)
            ->where('academic_year_id', $taId)
            ->where('status', AcademicCalendar::STATUS_FINAL)
            ->orderByDesc('version')
            ->first();

        if (!$calendar) {
            foreach ($classes as $class) {
                $result[$class->id] = array_fill_keys($days, 8);
            }
            return $result;
        }

        $lsGroups = [];
        foreach (CalendarLevelStructure::where('academic_calendar_id', $calendar->id)->get() as $ls) {
            $lsGroups[] = [
                'start' => $ls->grade_level_start,
                'end' => $ls->grade_level_end,
                'jp' => $ls->jp_per_day ?? [],
            ];
        }

        foreach ($classes as $class) {
            $grade = (int) $class->grade_level;
            $match = null;
            foreach ($lsGroups as $group) {
                if ($grade >= $group['start'] && $grade <= $group['end']) {
                    $match = $group;
                    break;
                }
            }

            if ($match === null) {
                $result[$class->id] = array_map(fn () => 8, array_flip($days));
                continue;
            }

            $perDay = array_fill(0, 7, 0);
            foreach ($days as $i => $day) {
                $perDay[$day] = (int) ($match['jp'][$day] ?? 0);
            }
            $result[$class->id] = $perDay;
        }

        return $result;
    }

    public function jadwalEdit(Request $request, ClassRoom $kelas): View
    {
        abort_unless($kelas->school_id === $request->user()->school_id, 403);

        $schoolId = $request->user()->school_id;
        $taId = $this->academicYearContext->exists() ? $this->academicYearContext->id() : null;

        $mappings = ClassSubjectTeacher::where('school_id', $schoolId)
            ->where('class_id', $kelas->id)
            ->when($taId !== null, fn ($query) => $query->where('academic_year_id', $taId))
            ->with(['subject:id,name,type', 'teacher:id,name'])
            ->get();

        $subjectOptions = $mappings->map(fn ($row) => [
            'id' => $row->subject_id,
            'name' => $row->subject?->name ?? '(tanpa nama)',
            'teacher' => $row->teacher?->name ?? '-',
            'is_quran' => $row->subject?->type === 'QURAN',
        ])->sortBy('name')->values();

        $existing = ClassDaySchedule::where('school_id', $schoolId)
            ->where('class_id', $kelas->id)
            ->when($taId !== null, fn ($query) => $query->where('academic_year_id', $taId))
            ->get();

        $jpByDay = $this->jpPerDay($schoolId, $taId);

        return view('wakakur.base.roster-jadwal-edit', compact('kelas', 'subjectOptions', 'existing', 'jpByDay', 'taId'));
    }

    public function jadwalStore(Request $request, ClassRoom $kelas): RedirectResponse
    {
        abort_unless($kelas->school_id === $request->user()->school_id, 403);

        $schoolId = $request->user()->school_id;
        $taId = $this->academicYearContext->exists() ? $this->academicYearContext->id() : null;

        $subjectMap = ClassSubjectTeacher::where('school_id', $schoolId)
            ->where('class_id', $kelas->id)
            ->when($taId !== null, fn ($query) => $query->where('academic_year_id', $taId))
            ->get(['subject_id', 'teacher_id'])
            ->pluck('teacher_id', 'subject_id');

        $jpByDay = $this->jpPerDay($schoolId, $taId);
        $cells = $request->input('schedules', []);
        $errors = [];

        $blocks = [];
        foreach (ClassDaySchedule::DAYS as $day) {
            $dayCells = is_array($cells[$day] ?? null) ? $cells[$day] : [];
            $maxJp = $jpByDay[$day] ?? 0;
            $open = null;
            for ($jp = 1; $jp <= $maxJp; $jp++) {
                $sid = isset($dayCells[$jp]) ? (string) $dayCells[$jp] : '';
                if ($sid === '') {
                    if ($open !== null) {
                        $blocks[] = ['day' => $day, 'subject_id' => $open['subject_id'], 'start_jp' => $open['start_jp'], 'end_jp' => $jp - 1];
                        $open = null;
                    }
                    continue;
                }
                if ($open !== null && $open['subject_id'] === $sid) {
                    continue;
                }
                if ($open !== null) {
                    $blocks[] = ['day' => $day, 'subject_id' => $open['subject_id'], 'start_jp' => $open['start_jp'], 'end_jp' => $jp - 1];
                }
                $open = ['subject_id' => $sid, 'start_jp' => $jp];
            }
            if ($open !== null) {
                $blocks[] = ['day' => $day, 'subject_id' => $open['subject_id'], 'start_jp' => $open['start_jp'], 'end_jp' => $maxJp];
            }
        }

        foreach ($blocks as $block) {
            if (!$subjectMap->has($block['subject_id'])) {
                $errors[] = sprintf('Mapel sudah pilih di hari %s jam %d tapi belum diplot untuk kelas %s.', $block['day'], $block['start_jp'], $kelas->class_name);
            }
        }

        $byDay = collect($blocks)->groupBy('day');
        foreach ($byDay as $day => $dayBlocks) {
            $dayBlocks = $dayBlocks->values();
            for ($i = 0; $i < $dayBlocks->count(); $i++) {
                for ($j = $i + 1; $j < $dayBlocks->count(); $j++) {
                    $a = $dayBlocks[$i];
                    $b = $dayBlocks[$j];
                    $overlap = $a['start_jp'] <= $b['end_jp'] && $b['start_jp'] <= $a['end_jp'];
                    if ($overlap && $a['subject_id'] !== $b['subject_id']) {
                        $errors[] = sprintf('Bentrok di kelas %s hari %s jam %d: dua mapel beda pada slot yang sama.', $kelas->class_name, $day, $a['start_jp']);
                    }
                }
            }
        }

        $teacherNames = User::whereIn('id', $subjectMap->values())->pluck('name', 'id');
        foreach ($blocks as $block) {
            $teacherId = $subjectMap[$block['subject_id']] ?? null;
            if ($teacherId === null) {
                continue;
            }
            $clash = ClassDaySchedule::where('school_id', $schoolId)
                ->where('teacher_id', $teacherId)
                ->where('day_name', $block['day'])
                ->where('class_id', '!=', $kelas->id)
                ->when($taId !== null, fn ($query) => $query->where('academic_year_id', $taId))
                ->with('classRoom:id,class_name')
                ->get()
                ->first(fn ($row) => $row->start_jp <= $block['end_jp'] && $block['start_jp'] <= $row->end_jp);

            if ($clash) {
                $errors[] = sprintf('Bentrok guru: %s sudah mengajar %s di jam %d-%d.', 
                    $teacherNames[$teacherId] ?? 'Guru',
                    $clash->classRoom?->class_name ?? 'kelas lain',
                    $clash->start_jp,
                    $clash->end_jp);
            }
        }

        if (count($errors) > 0) {
            $message = 'Jadwal tidak tersimpan: ' . implode(' ', array_slice($errors, 0, 4));
            if (count($errors) > 4) {
                $message .= ' (dan lain-lain)';
            }

            return redirect()->route('wakasek.base.roster.jadwal-edit', $kelas)
                ->with('error', $message)
                ->withInput();
        }

        ClassDaySchedule::where('school_id', $schoolId)
            ->where('class_id', $kelas->id)
            ->when($taId !== null, fn ($query) => $query->where('academic_year_id', $taId))
            ->delete();

        foreach ($blocks as $block) {
            ClassDaySchedule::create([
                'school_id' => $schoolId,
                'academic_year_id' => $taId,
                'class_id' => $kelas->id,
                'subject_id' => $block['subject_id'],
                'teacher_id' => $subjectMap[$block['subject_id']],
                'day_name' => $block['day'],
                'start_jp' => $block['start_jp'],
                'end_jp' => $block['end_jp'],
            ]);
        }

        return redirect()->route('wakasek.base.roster.jadwal-edit', $kelas)
            ->with('status', count($blocks) > 0
                ? 'Jadwal ' . $kelas->class_name . ' tersimpan (' . count($blocks) . ' blok).'
                : 'Jadwal ' . $kelas->class_name . ' dikosongkan.');
    }

    public function prota(Request $request): View
    {
        $subjects = Subject::where('school_id', $request->user()->school_id)->orderBy('type')->orderBy('name')->get();
        return view('wakakur.base.prota', compact('subjects'));
    }

    public function prosem(Request $request): View
    {
        $subjects = Subject::where('school_id', $request->user()->school_id)->orderBy('type')->orderBy('name')->get();
        return view('wakakur.base.prosem', compact('subjects'));
    }

    public function capaianPembelajaran(Request $request): View
    {
        $subjects = Subject::where('school_id', $request->user()->school_id)->orderBy('type')->orderBy('name')->get();
        return view('wakakur.base.capaian-pembelajaran', compact('subjects'));
    }

    public function atp(Request $request): View
    {
        $subjects = Subject::where('school_id', $request->user()->school_id)->orderBy('type')->orderBy('name')->get();
        return view('wakakur.base.atp', compact('subjects'));
    }

    public function modulAjar(Request $request): View
    {
        $subjects = Subject::where('school_id', $request->user()->school_id)->orderBy('type')->orderBy('name')->get();
        return view('wakakur.base.modul-ajar', compact('subjects'));
    }
}

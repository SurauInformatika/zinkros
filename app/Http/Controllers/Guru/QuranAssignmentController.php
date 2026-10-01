<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\ClassSubjectTeacher;
use App\Models\QuranTeachingAssignment;
use App\Models\Student;
use App\Models\Subject;
use App\Models\TeacherSubject;
use App\Models\User;
use App\Services\AcademicYearContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class QuranAssignmentController extends Controller
{
    public function __construct(protected AcademicYearContext $academicYearContext) {}

    private function indexRoute(): string
    {
        return \Illuminate\Support\Str::of(Route::currentRouteName())
            ->beforeLast('.')
            ->append('.index')
            ->toString();
    }

    public function index(Request $request): View
    {
        $user = auth()->user();
        $schoolId = $user->school_id;

        $quranSubjectIds = Subject::where('type', 'QURAN')->pluck('id');

        $plottedTeacherIds = ClassSubjectTeacher::whereIn('subject_id', $quranSubjectIds)
            ->where('school_id', $schoolId)
            ->pluck('teacher_id');

        $subjectTeacherIds = TeacherSubject::whereIn('subject_id', $quranSubjectIds)
            ->where('school_id', $schoolId)
            ->pluck('teacher_id');

        $assignedTeacherIds = QuranTeachingAssignment::where('school_id', $schoolId)
            ->when($this->academicYearContext->exists(), function ($q) {
                $q->where(function ($q2) {
                    $q2->where('academic_year_id', $this->academicYearContext->id())
                        ->orWhereNull('academic_year_id');
                });
            })
            ->pluck('teacher_id');

        $quranTeacherIds = $plottedTeacherIds->merge($subjectTeacherIds)->merge($assignedTeacherIds)->unique();

        $quranTeachers = User::where('school_id', $schoolId)
            ->where('role', 'guru')
            ->where(function ($q) use ($quranTeacherIds) {
                $q->whereIn('id', $quranTeacherIds)
                    ->orWhereHas('teacherRoles', function ($q2) {
                        $q2->where('role_name', 'PJ Tahfidz');
                    });
            })
            ->orderBy('name')
            ->get();

        $assignments = QuranTeachingAssignment::where('school_id', $schoolId)
            ->when($this->academicYearContext->exists(), function ($q) {
                $q->where(function ($q2) {
                    $q2->where('academic_year_id', $this->academicYearContext->id())
                        ->orWhereNull('academic_year_id');
                });
            })
            ->with(['student.classRoom', 'teacher'])
            ->get()
            ->groupBy('teacher_id');

        $allStudents = Student::where('school_id', $schoolId)
            ->whereNotNull('class_id')
            ->with('classRoom')
            ->orderBy('name')
            ->get();

        $assignedMap = QuranTeachingAssignment::where('school_id', $schoolId)
            ->when($this->academicYearContext->exists(), function ($q) {
                $q->where(function ($q2) {
                    $q2->where('academic_year_id', $this->academicYearContext->id())
                        ->orWhereNull('academic_year_id');
                });
            })
            ->with('teacher:id,name')
            ->get()
            ->mapWithKeys(fn ($a) => [$a['student_id'] => $a['teacher']?->name ?? '-']);

        $unassignedStudents = $allStudents->whereNotIn('id', $assignedMap->keys());

        $studentsByClass = $allStudents->groupBy(fn ($s) => $s->classRoom?->class_name ?? 'Tanpa Kelas')
            ->map(fn ($students, $className) => [
                'class_name' => $className,
                'students' => $students->map(fn ($s) => [
                    'id' => $s->id,
                    'name' => $s->name,
                    'class_name' => $s->classRoom?->class_name ?? '-',
                    'is_assigned' => $assignedMap->has($s->id),
                    'assigned_teacher' => $assignedMap->get($s->id),
                ])->values(),
            ])->values();

        return view('guru.quran-assignment.index', compact(
            'quranTeachers', 'assignments', 'allStudents', 'unassignedStudents', 'studentsByClass'
        ));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $schoolId = $user->school_id;
        $ayId = $this->academicYearContext->id();

        $validated = $request->validate([
            'student_id' => 'required|uuid',
            'teacher_id' => 'required|uuid',
        ]);

        $existing = QuranTeachingAssignment::where('student_id', $validated['student_id'])
            ->when($ayId, fn ($q) => $q->where('academic_year_id', $ayId))
            ->first();

        if ($existing) {
            $existing->update(['teacher_id' => $validated['teacher_id']]);
        } else {
            QuranTeachingAssignment::create([
                'school_id' => $schoolId,
                'academic_year_id' => $ayId,
                'student_id' => $validated['student_id'],
                'teacher_id' => $validated['teacher_id'],
            ]);
        }

        return redirect()->route($this->indexRoute())
            ->with('success', 'Assignment berhasil disimpan.');
    }

    public function destroy(QuranTeachingAssignment $quranAssignment)
    {
        $quranAssignment->delete();

        return redirect()->route($this->indexRoute())
            ->with('success', 'Assignment berhasil dihapus.');
    }

    public function bulkAssign(Request $request)
    {
        $user = auth()->user();
        $schoolId = $user->school_id;
        $ayId = $this->academicYearContext->id();

        $validated = $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'uuid',
            'teacher_id' => 'required|uuid',
        ]);

        foreach ($validated['student_ids'] as $studentId) {
            $existing = QuranTeachingAssignment::where('student_id', $studentId)
                ->when($ayId, fn ($q) => $q->where('academic_year_id', $ayId))
                ->first();

            if ($existing) {
                $existing->update(['teacher_id' => $validated['teacher_id']]);
            } else {
                QuranTeachingAssignment::create([
                    'school_id' => $schoolId,
                    'academic_year_id' => $ayId,
                    'student_id' => $studentId,
                    'teacher_id' => $validated['teacher_id'],
                ]);
            }
        }

        return redirect()->route($this->indexRoute())
            ->with('success', count($validated['student_ids']) . ' siswa berhasil di-assign.');
    }
}

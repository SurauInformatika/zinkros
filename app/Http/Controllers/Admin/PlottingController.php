<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\ClassSubjectTeacher;
use App\Models\Subject;
use App\Models\User;
use App\Services\AcademicYearContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlottingController extends Controller
{
    public function __construct(protected AcademicYearContext $academicYearContext) {}

    public function index(): View
    {
        $classes = ClassRoom::query()
            ->with('walis')
            ->withCount('subjects')
            ->orderBy('grade_level')
            ->orderBy('class_name')
            ->paginate(15);

        return view('admin.plotting.index', compact('classes'));
    }

    public function edit(ClassRoom $class): View
    {
        $class->load('walis');

        $subjects = Subject::query()
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        $teachers = User::query()
            ->where('role', 'guru')
            ->orderBy('name')
            ->get();

        $query = ClassSubjectTeacher::query()
            ->where('class_id', $class->id);

        if ($this->academicYearContext->exists()) {
            $query->where(function ($q) {
                $q->where('academic_year_id', $this->academicYearContext->id())
                    ->orWhereNull('academic_year_id');
            });
        }

        $current = $query->get()
            ->groupBy('subject_id')
            ->map(fn ($items) => $items->pluck('teacher_id')->toArray())
            ->all();

        $existingPlotsQuery = ClassSubjectTeacher::query()
            ->with('subject:id,name', 'classRoom:id,class_name')
            ->where('class_id', '!=', $class->id);

        if ($this->academicYearContext->exists()) {
            $existingPlotsQuery->where('academic_year_id', $this->academicYearContext->id());
        }

        $existingPlots = $existingPlotsQuery->get()
            ->groupBy('teacher_id')
            ->map(fn ($items) => $items->map(fn ($item) => $item->subject->name . ' (' . $item->classRoom->class_name . ')')->toArray())
            ->all();

        return view('admin.plotting.edit', compact('class', 'subjects', 'teachers', 'current', 'existingPlots'));
    }

    public function update(Request $request, ClassRoom $class): RedirectResponse
    {
        $validated = $request->validate([
            'assignments' => 'nullable|array',
            'assignments.*' => 'nullable|array',
            'assignments.*.*' => 'exists:users,id',
        ]);

        $deleteQuery = ClassSubjectTeacher::query()
            ->where('class_id', $class->id);

        if ($this->academicYearContext->exists()) {
            $deleteQuery->where('academic_year_id', $this->academicYearContext->id());
        }

        $deleteQuery->delete();

        $assignments = $validated['assignments'] ?? [];
        $rows = [];

        foreach ($assignments as $subjectId => $teacherIds) {
            if (empty($teacherIds)) {
                continue;
            }

            foreach (array_unique($teacherIds) as $teacherId) {
                $rows[] = [
                    'id' => \Illuminate\Support\Str::uuid(),
                    'school_id' => $request->user()->school_id,
                    'class_id' => $class->id,
                    'subject_id' => $subjectId,
                    'teacher_id' => $teacherId,
                    'academic_year_id' => $this->academicYearContext->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (! empty($rows)) {
            ClassSubjectTeacher::query()->insert($rows);
        }

        return redirect()
            ->route('admin.plotting.index')
            ->with('status', "Plotting untuk kelas {$class->class_name} berhasil disimpan.");
    }
}

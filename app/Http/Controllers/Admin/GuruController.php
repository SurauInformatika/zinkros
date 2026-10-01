<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassHomeroom;
use App\Models\ClassRoom;
use App\Models\Subject;
use App\Models\TeacherRole;
use App\Models\TeacherSubject;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class GuruController extends Controller
{
    public function index(Request $request): View
    {
        $gurus = User::query()
            ->where('role', User::ROLE_GURU)
            ->when($request->filled('gender'), function ($query) use ($request) {
                $query->where('gender', $request->input('gender'));
            })
            ->with('subjects:id,name')
            ->withCount('waliClasses as wali_classes_count')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.guru.index', compact('gurus'));
    }

    public function create(): View
    {
        return view('admin.guru.create', [
            'subjects' => $this->subjectList(),
            'existingRoles' => $this->existingRoles(),
            'classes' => $this->waliClassList(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['nullable', 'in:L,P'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'subject_ids' => ['nullable', 'array'],
            'subject_ids.*' => ['exists:subjects,id'],
            'role_names' => ['nullable', 'array'],
            'role_names.*' => ['required', 'string', 'max:255'],
            'role_student_related' => ['nullable', 'array'],
            'is_wali' => ['sometimes'],
            'wali_class_id' => ['nullable', 'required_with:is_wali', 'exists:classes,id'],
            'wali_sort' => ['nullable', 'required_with:is_wali', Rule::in([1, 2])],
        ]);

        $validated['role'] = User::ROLE_GURU;
        $validated['is_wali_kelas'] = false;
        $validated['created_by'] = $request->user()->id;
        $validated['school_id'] = $request->user()->school_id;

        $guru = User::create($validated);

        $this->syncSubjects($guru->id, $request->input('subject_ids', []));
        $this->syncTeacherRoles($guru, $request);
        $this->syncWaliKelas($guru, $request);

        return redirect()
            ->route('admin.guru.index')
            ->with('status', 'Akun guru berhasil ditambahkan.');
    }

    public function edit(User $guru): View
    {
        abort_unless($guru->isGuru(), 404);

        $prefill = $guru->waliClasses->first();

        return view('admin.guru.edit', [
            'guru' => $guru,
            'subjects' => $this->subjectList(),
            'existingRoles' => $this->existingRoles(),
            'classes' => $this->waliClassList(),
            'waliClassId' => old('wali_class_id', $prefill?->id),
            'waliSort' => old('wali_sort', $prefill?->pivot?->sort),
            'isWali' => old('is_wali', $prefill ? '1' : ''),
        ]);
    }

    public function update(Request $request, User $guru): RedirectResponse
    {
        abort_unless($guru->isGuru(), 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['nullable', 'in:L,P'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($guru->id)],
            'phone' => ['nullable', 'string', 'max:20', Rule::unique('users', 'phone')->ignore($guru->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'subject_ids' => ['nullable', 'array'],
            'subject_ids.*' => ['exists:subjects,id'],
            'role_names' => ['nullable', 'array'],
            'role_names.*' => ['required', 'string', 'max:255'],
            'role_student_related' => ['nullable', 'array'],
            'is_wali' => ['sometimes'],
            'wali_class_id' => ['nullable', 'required_with:is_wali', 'exists:classes,id'],
            'wali_sort' => ['nullable', 'required_with:is_wali', Rule::in([1, 2])],
        ]);

        if (! $request->filled('password')) {
            unset($validated['password']);
        }

        $guru->update($validated);

        $this->syncSubjects($guru->id, $request->input('subject_ids', []));
        $this->syncTeacherRoles($guru, $request);
        $this->syncWaliKelas($guru, $request);

        return redirect()
            ->route('admin.guru.index')
            ->with('status', 'Data guru berhasil diperbarui.');
    }

    public function destroy(User $guru): RedirectResponse
    {
        abort_unless($guru->isGuru(), 404);

        $guru->delete();

        return redirect()
            ->route('admin.guru.index')
            ->with('status', 'Akun guru berhasil dihapus.');
    }

    private function subjectList(): \Illuminate\Database\Eloquent\Collection
    {
        return Subject::query()
            ->orderBy('type')
            ->orderBy('name')
            ->get(['id', 'name', 'type']);
    }

    private function existingRoles(): \Illuminate\Support\Collection
    {
        return TeacherRole::with('teacher:id,name')
            ->orderBy('role_name')
            ->get()
            ->groupBy('role_name')
            ->map(fn ($roles) => [
                'count' => $roles->count(),
                'teachers' => $roles->pluck('teacher.name'),
            ]);
    }

    private function syncSubjects(string $teacherId, array $subjectIds): void
    {
        TeacherSubject::where('teacher_id', $teacherId)->delete();

        foreach (array_unique($subjectIds) as $subjectId) {
            TeacherSubject::create([
                'teacher_id' => $teacherId,
                'subject_id' => $subjectId,
            ]);
        }
    }

    private function syncTeacherRoles(User $guru, Request $request): void
    {
        $roleNames = $request->input('role_names', []);
        $studentRelated = $request->input('role_student_related', []);

        TeacherRole::where('teacher_id', $guru->id)->delete();

        foreach (array_unique($roleNames) as $index => $roleName) {
            if (empty(trim($roleName))) {
                continue;
            }

            TeacherRole::create([
                'school_id' => $guru->school_id,
                'teacher_id' => $guru->id,
                'role_name' => trim($roleName),
                'is_student_related' => in_array($index, $studentRelated),
            ]);
        }
    }

    private function syncWaliKelas(User $guru, Request $request): void
    {
        if (! $request->has('is_wali')) {
            return;
        }

        $classId = $request->input('wali_class_id');
        $sort = (int) $request->input('wali_sort');

        ClassHomeroom::where('class_id', $classId)->where('user_id', $guru->id)->delete();

        $taken = ClassHomeroom::where('class_id', $classId)->where('sort', $sort)->exists();

        DB::transaction(function () use ($guru, $classId, $sort, $taken) {
            if ($taken) {
                throw ValidationException::withMessages([
                    'wali_sort' => "Slot Wali {$sort} pada kelas tersebut sudah terisi guru lain.",
                ]);
            }

            ClassHomeroom::create([
                'school_id' => $guru->school_id,
                'class_id' => $classId,
                'user_id' => $guru->id,
                'label' => null,
                'sort' => $sort,
            ]);

            User::where('id', $guru->id)->update(['is_wali_kelas' => true]);
        });
    }

    private function waliClassList(): \Illuminate\Database\Eloquent\Collection
    {
        return ClassRoom::query()
            ->orderBy('grade_level')
            ->orderBy('class_name')
            ->get(['id', 'class_name']);
    }
}

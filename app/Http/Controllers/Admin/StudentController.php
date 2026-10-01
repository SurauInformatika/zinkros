<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\Student;
use App\Models\StudentParent;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $students = Student::query()
            ->with(['classRoom', 'parents'])
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('nisn', 'like', "%{$request->search}%")
                ->orWhere('nis', 'like', "%{$request->search}%")
            )
            ->when($request->filled('class_id'), fn ($q) => $q->where('class_id', $request->class_id))
            ->when($request->filled('gender'), fn ($q) => $q->where('gender', $request->gender))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $classes = ClassRoom::query()->orderBy('grade_level')->orderBy('class_name')->get();

        return view('admin.student.index', compact('students', 'classes'));
    }

    public function create(): View
    {
        $classes = ClassRoom::query()->orderBy('grade_level')->orderBy('class_name')->get();
        $parents = User::query()->where('role', 'ortu')->orderBy('name')->get();

        return view('admin.student.create', compact('classes', 'parents'));
    }

    public function searchOrtu(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q'));

        $query = User::query()
            ->where('role', 'ortu')
            ->when($q !== '', fn ($b) => $b->where(function ($b2) use ($q) {
                $b2->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%");
            }))
            ->withCount(['students as children_count'])
            ->orderBy('name')
            ->limit(20);

        $results = $query->get()->map(fn ($u) => [
            'id' => $u->id,
            'name' => $u->name,
            'email' => $u->email,
            'phone' => $u->phone,
            'children_count' => $u->children_count,
        ]);

        return response()->json(['data' => $results]);
    }

    public function storeOrtu(Request $request): JsonResponse
    {
        $schoolId = $request->user()->school_id;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
        ]);

        $exists = User::where('school_id', $schoolId)
            ->where('email', $validated['email'])
            ->first();

        if ($exists) {
            return response()->json(['error' => ['email' => 'Email sudah digunakan oleh ' . $exists->name . '.']], 422);
        }

        if (!empty($validated['phone'])) {
            $phoneOwner = User::where('school_id', $schoolId)
                ->where('phone', $validated['phone'])
                ->first();
            if ($phoneOwner) {
                return response()->json(['error' => ['phone' => 'Telepon sudah digunakan oleh ' . $phoneOwner->name . '.']], 422);
            }
        }

        try {
            $user = User::create([
                'school_id' => $schoolId,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'position' => 'Orang Tua',
                'role' => 'ortu',
                'created_by' => $request->user()->id,
                'password' => Hash::make(User::DEFAULT_FIRST_PASSWORD),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json(['error' => ['email' => 'Email atau telepon sudah digunakan akun lain.']], 422);
        }

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'children_count' => 0,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'string', 'in:L,P'],
            'nisn' => ['nullable', 'string', 'max:50', $this->uniqueNisn()],
            'nis' => ['required', 'string', 'max:50'],
            'rfid_tag_id' => ['nullable', 'string', 'max:100'],
            'class_id' => ['required', 'exists:classes,id'],
            'parent_user_ids' => ['nullable', 'array'],
            'parent_user_ids.*' => ['exists:users,id'],
            'parent_relations.*' => ['nullable', 'in:AYAH,IBU,WALI'],
            'primary_parent' => ['nullable', 'string'],
            'parent_relations' => [$this->parentRelationRule()],
        ]);

        $student = Student::create([
            'school_id' => auth()->user()->school_id,
            'name' => $validated['name'],
            'gender' => $validated['gender'],
            'nisn' => $validated['nisn'] ?? null,
            'nis' => $validated['nis'],
            'rfid_tag_id' => $validated['rfid_tag_id'] ?? null,
            'class_id' => $validated['class_id'],
        ]);

        $this->syncParents($student, $request);

        return redirect()
            ->route('admin.siswa.index')
            ->with('status', 'Siswa berhasil ditambahkan.');
    }

    public function edit(Student $student): View
    {
        $classes = ClassRoom::query()->orderBy('grade_level')->orderBy('class_name')->get();
        $parents = User::query()->where('role', 'ortu')->orderBy('name')->get();

        return view('admin.student.edit', compact('student', 'classes', 'parents'));
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'string', 'in:L,P'],
            'nisn' => ['nullable', 'string', 'max:50', $this->uniqueNisn($student->id)],
            'nis' => ['required', 'string', 'max:50'],
            'rfid_tag_id' => ['nullable', 'string', 'max:100'],
            'class_id' => ['required', 'exists:classes,id'],
            'parent_user_ids' => ['nullable', 'array'],
            'parent_user_ids.*' => ['exists:users,id'],
            'parent_relations.*' => ['nullable', 'in:AYAH,IBU,WALI'],
            'primary_parent' => ['nullable', 'string'],
            'parent_relations' => [$this->parentRelationRule()],
        ]);

        $student->update([
            'name' => $validated['name'],
            'gender' => $validated['gender'],
            'nisn' => $validated['nisn'] ?? null,
            'nis' => $validated['nis'],
            'rfid_tag_id' => $validated['rfid_tag_id'] ?? null,
            'class_id' => $validated['class_id'],
        ]);

        $this->syncParents($student, $request);

        return redirect()
            ->route('admin.siswa.index')
            ->with('status', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $hasAttendance = $student->gateAttendances()->exists()
            || $student->subjectAttendances()->exists();

        if ($hasAttendance) {
            return back()->with('error', 'Siswa tidak dapat dihapus karena memiliki riwayat absensi.');
        }

        $student->delete();

        return redirect()
            ->route('admin.siswa.index')
            ->with('status', 'Siswa berhasil dihapus.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['exists:students,id'],
        ]);

        $students = Student::whereIn('id', $request->ids)->get();
        $deleted = 0;
        $skipped = 0;

        foreach ($students as $student) {
            $hasAttendance = $student->gateAttendances()->exists()
                || $student->subjectAttendances()->exists();

            if ($hasAttendance) {
                $skipped++;
                continue;
            }

            $student->delete();
            $deleted++;
        }

        $msg = "{$deleted} siswa berhasil dihapus.";
        if ($skipped > 0) {
            $msg .= " {$skipped} dilewati (punya riwayat absensi).";
        }

        return redirect()
            ->route('admin.siswa.index')
            ->with('status', $msg);
    }

    public function importPreview(Request $request): View
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:2048'],
        ]);

        $schoolId = auth()->user()->school_id;
        $file = $request->file('file');

        $tempPath = $file->store('temp/import', 'local');
        session(['import_temp_path' => $tempPath]);

        $reader = \Maatwebsite\Excel\Facades\Excel::toCollection(new \App\Imports\SiswaPreviewImport, $file);
        $rows = $reader->get(0);

        $existingClasses = ClassRoom::where('school_id', $schoolId)
            ->pluck('class_name')
            ->map(fn ($name) => strtolower($name))
            ->toArray();

        $grouped = $rows->groupBy('nama_kelas')->map(function ($students, $className) use ($existingClasses) {
            $exists = in_array(strtolower($className), $existingClasses);
            return [
                'class_name' => $className,
                'exists' => $exists,
                'students' => $students->map(fn ($s) => [
                    'nama' => $s['nama'] ?? '',
                    'nisn' => $s['nisn'] ?? '',
                    'nis' => $s['nis'] ?? '',
                    'gender' => $s['gender'] ?? '',
                ])->toArray(),
                'count' => $students->count(),
            ];
        })->values();

        $newClasses = $grouped->where('exists', false);
        $existingClassRows = $grouped->where('exists', true);

        return view('admin.student.import-preview', compact('newClasses', 'existingClassRows'));
    }

    public function importConfirm(Request $request): RedirectResponse
    {
        $schoolId = auth()->user()->school_id;
        $tempPath = session('import_temp_path');

        if (!$tempPath) {
            return redirect()->route('admin.siswa.index')->with('error', 'Session expired. Silakan upload ulang.');
        }

        $fullPath = \Illuminate\Support\Facades\Storage::disk('local')->path($tempPath);
        if (!file_exists($fullPath)) {
            return redirect()->route('admin.siswa.index')->with('error', 'File tidak ditemukan. Silakan upload ulang.');
        }

        $reader = \Maatwebsite\Excel\Facades\Excel::toCollection(new \App\Imports\SiswaPreviewImport, new \Illuminate\Http\File($fullPath));
        $rows = $reader->get(0);

        $classNames = $rows->pluck('nama_kelas')->filter()->unique()->toArray();

        $created = 0;
        foreach ($classNames as $className) {
            $exists = ClassRoom::where('school_id', $schoolId)
                ->whereRaw('LOWER(class_name) = ?', [strtolower($className)])
                ->exists();

            if (!$exists) {
                $gradeLevel = $this->inferGradeLevel($className);
                ClassRoom::create([
                    'school_id' => $schoolId,
                    'class_name' => $className,
                    'grade_level' => $gradeLevel,
                ]);
                $created++;
            }
        }

        $import = new \App\Imports\SiswaImport($schoolId);
        \Maatwebsite\Excel\Facades\Excel::import($import, new \Illuminate\Http\File($fullPath));

        @unlink($fullPath);
        session()->forget('import_temp_path');

        $msg = "Berhasil import {$import->getImportedCount()} siswa.";
        if ($created > 0) {
            $msg .= " {$created} kelas baru dibuat otomatis.";
        }
        if ($import->getSkippedCount() > 0) {
            $msg .= " {$import->getSkippedCount()} dilewati (NIS/NISN sudah ada).";
        }

        return redirect()->route('admin.siswa.index')->with('status', $msg);
    }

    private function inferGradeLevel(string $className): string
    {
        $clean = trim($className);

        if (preg_match('/^TK[\s_-]*(A|B|a|b)?$/i', $clean, $m)) {
            return isset($m[1]) ? 'TK_' . strtoupper($m[1]) : 'TK_A';
        }

        if (preg_match('/^(\d+)/', $clean, $m)) {
            return $m[1];
        }

        return '0';
    }

    private function uniqueNisn(?string $ignoreId = null)
    {
        return Rule::unique('students', 'nisn')
            ->where('school_id', auth()->user()->school_id)
            ->ignore($ignoreId);
    }

    /**
     * Business rule: at most one AYAH and one IBU per student.
     * WALI is unlimited.
     */
    private function parentRelationRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) {
            $relations = (array) $value;
            $counts = array_count_values($relations);

            $ayah = ($counts[\App\Models\StudentParent::RELATION_AYAH] ?? 0) - 1;
            $ibu = ($counts[\App\Models\StudentParent::RELATION_IBU] ?? 0) - 1;

            if ($ayah > 0) {
                $fail('Maksimal satu Ayah per siswa.');
            }
            if ($ibu > 0) {
                $fail('Maksimal satu Ibu per siswa.');
            }
        };
    }

    private function syncParents(Student $student, Request $request): void    {
        $parentIds = array_values(array_filter($request->input('parent_user_ids', [])));
        $relations = $request->input('parent_relations', []);
        $primaryIndex = $request->input('primary_parent');

        $schoolId = auth()->user()->school_id;
        $hasPrimary = false;
        $prepared = [];

        foreach ($parentIds as $index => $parentId) {
            $isPrimary = $primaryIndex !== null && (int) $primaryIndex === $index;
            if ($primaryIndex === null && count($parentIds) === 1) {
                $isPrimary = true;
            }
            $hasPrimary = $hasPrimary || $isPrimary;

            $prepared[$parentId] = [
                'relation' => $relations[$index] ?? \App\Models\StudentParent::RELATION_WALI,
                'is_primary' => $isPrimary,
            ];
        }

        if (!empty($prepared) && !$hasPrimary) {
            $first = array_key_first($prepared);
            $prepared[$first]['is_primary'] = true;
        }

        // Remove any pivot rows whose parent is no longer selected (or all if none selected)
        if (empty($prepared)) {
            \App\Models\StudentParent::where('student_id', $student->id)->delete();
        } else {
            \App\Models\StudentParent::where('student_id', $student->id)
                ->whereNotIn('user_id', array_keys($prepared))
                ->delete();
        }

        // Upsert each selected parent via the model so the UUID id is generated
        foreach ($prepared as $parentId => $data) {
            \App\Models\StudentParent::updateOrCreate(
                ['student_id' => $student->id, 'user_id' => $parentId],
                [
                    'school_id' => $schoolId,
                    'relation' => $data['relation'],
                    'is_primary' => $data['is_primary'],
                ]
            );
        }
    }
}

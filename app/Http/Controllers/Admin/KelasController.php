<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassHomeroom;
use App\Models\ClassRoom;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KelasController extends Controller
{
    public function index(): View
    {
        $classes = ClassRoom::query()
            ->with(['walis:id,name', 'students:id,class_id'])
            ->withCount('students')
            ->orderBy('grade_level')
            ->orderBy('class_name')
            ->paginate(15);

        return view('admin.kelas.index', compact('classes'));
    }

    public function create(): View
    {
        return view('admin.kelas.create', [
            'waliKelasList' => $this->waliKelasList(),
            'gradeOptions' => School::gradeOptionGroups(request()->user()->school?->education_level),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'class_name' => ['required', 'string', 'max:255'],
            'grade_level' => ['required', 'string', Rule::in($this->allowedGradeLevels($request))],
            'wali' => ['nullable', 'array', 'max:2'],
            'wali.*.user_id' => ['nullable', 'exists:users,id'],
            'wali.*.label' => ['nullable', 'string', 'max:191'],
        ], [], [
            'wali.0.user_id' => 'Wali Kelas 1',
            'wali.1.user_id' => 'Wali Kelas 2',
        ]);

        $waliUsers = $this->extractWaliUsers($request->input('wali', []));
        if (count($waliUsers) !== count(array_unique(array_column($waliUsers, 'user_id')))) {
            return back()->withErrors(['wali' => 'Wali Kelas tidak boleh diambil dari guru yang sama.'])->withInput();
        }

        DB::transaction(function () use ($validated, $waliUsers) {
            $kelas = ClassRoom::create([
                'school_id' => request()->user()->school_id,
                'class_name' => $validated['class_name'],
                'grade_level' => $validated['grade_level'],
            ]);

            $this->syncWalis($kelas, $waliUsers);
        });

        return redirect()
            ->route('admin.kelas.index')
            ->with('status', 'Kelas berhasil ditambahkan.');
    }

    public function edit(ClassRoom $kelas): View
    {
        $kelas->load('homerooms');

        return view('admin.kelas.edit', [
            'kelas' => $kelas,
            'waliKelasList' => $this->waliKelasList(),
            'gradeOptions' => School::gradeOptionGroups(request()->user()->school?->education_level),
        ]);
    }

    public function update(Request $request, ClassRoom $kelas): RedirectResponse
    {
        $validated = $request->validate([
            'class_name' => ['required', 'string', 'max:255'],
            'grade_level' => ['required', 'string', Rule::in($this->allowedGradeLevels($request))],
            'wali' => ['nullable', 'array', 'max:2'],
            'wali.*.user_id' => ['nullable', 'exists:users,id'],
            'wali.*.label' => ['nullable', 'string', 'max:191'],
        ], [], [
            'wali.0.user_id' => 'Wali Kelas 1',
            'wali.1.user_id' => 'Wali Kelas 2',
        ]);

        $waliUsers = $this->extractWaliUsers($request->input('wali', []));
        if (count($waliUsers) !== count(array_unique(array_column($waliUsers, 'user_id')))) {
            return back()->withErrors(['wali' => 'Wali Kelas tidak boleh diambil dari guru yang sama.'])->withInput();
        }

        DB::transaction(function () use ($request, $kelas, $validated, $waliUsers) {
            $kelas->update([
                'class_name' => $validated['class_name'],
                'grade_level' => $validated['grade_level'],
            ]);

            $this->syncWalis($kelas, $waliUsers);
        });

        return redirect()
            ->route('admin.kelas.index')
            ->with('status', 'Kelas berhasil diperbarui.');
    }

    public function destroy(ClassRoom $kelas): RedirectResponse
    {
        if ($kelas->students()->exists()) {
            return back()->with('error', 'Kelas tidak dapat dihapus karena masih memiliki siswa.');
        }

        $kelas->delete();

        return redirect()
            ->route('admin.kelas.index')
            ->with('status', 'Kelas berhasil dihapus.');
    }

    private function extractWaliUsers(array $wali): array
    {
        $result = [];
        $sort = 1;
        foreach ($wali as $slot) {
            $userId = $slot['user_id'] ?? null;
            if ($userId) {
                $result[] = [
                    'school_id' => request()->user()->school_id,
                    'user_id' => $userId,
                    'label' => $slot['label'] ?? null,
                    'sort' => $sort,
                ];
                $sort++;
            }
        }

        return $result;
    }

    private function syncWalis(ClassRoom $kelas, array $waliUsers): void
    {
        ClassHomeroom::where('class_id', $kelas->id)->delete();

        foreach ($waliUsers as $data) {
            ClassHomeroom::create([
                'school_id' => $kelas->school_id,
                'class_id' => $kelas->id,
                'user_id' => $data['user_id'],
                'label' => $data['label'],
                'sort' => $data['sort'],
            ]);
        }

        $assignedUserIds = array_column($waliUsers, 'user_id');
        foreach ($assignedUserIds as $userId) {
            User::where('id', $userId)->update(['is_wali_kelas' => true]);
        }
    }

    private function waliKelasList(): \Illuminate\Database\Eloquent\Collection
    {
        return User::query()
            ->where('role', User::ROLE_GURU)
            ->orderByDesc('is_wali_kelas')
            ->orderBy('name')
            ->get(['id', 'name', 'is_wali_kelas']);
    }

    private function allowedGradeLevels(Request $request): array
    {
        $levels = School::educationLevels();
        $school = $request->user()->school;

        if ($school?->education_level && isset($levels[$school->education_level])) {
            return array_keys($levels[$school->education_level]['levels']);
        }

        return School::allGradeLevelValues();
    }
}

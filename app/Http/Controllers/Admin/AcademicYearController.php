<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\ClassRoom;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AcademicYearController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $academicYears = AcademicYear::where('school_id', $user->school_id)
            ->withCount('grades')
            ->orderByDesc('is_active')
            ->orderByDesc('start_date')
            ->get();

        return view('admin.academic-years.index', compact('academicYears'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:20',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        $exists = AcademicYear::where('school_id', $user->school_id)
            ->where('name', $validated['name'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'Tahun ajaran "' . $validated['name'] . '" sudah ada.');
        }

        AcademicYear::create(array_merge($validated, [
            'school_id' => $user->school_id,
            'is_active' => false,
        ]));

        return back()->with('success', 'Tahun ajaran berhasil ditambahkan.');
    }

    public function activate(AcademicYear $academicYear)
    {
        $user = auth()->user();

        if ($academicYear->school_id !== $user->school_id) {
            abort(403);
        }

        AcademicYear::where('school_id', $user->school_id)
            ->update(['is_active' => false]);

        $academicYear->update(['is_active' => true]);

        session()->put('active_academic_year_id', $academicYear->id);

        return back()->with('success', 'Tahun ajaran ' . $academicYear->name . ' diaktifkan.');
    }

    public function promote(AcademicYear $academicYear)
    {
        $user = auth()->user();

        if ($academicYear->school_id !== $user->school_id) {
            abort(403);
        }

        $schoolId = $user->school_id;

        $students = Student::where('school_id', $schoolId)
            ->with('classRoom')
            ->get();

        $promoted = 0;
        $graduated = 0;

        foreach ($students as $student) {
            $currentClass = $student->classRoom;

            if (!$currentClass) {
                continue;
            }

            $currentLevel = $currentClass->grade_level;

            if ($currentLevel >= 9) {
                $student->update([
                    'class_id' => null,
                ]);
                $graduated++;
            } else {
                $newLevel = $currentLevel + 1;
                $newClass = ClassRoom::where('school_id', $schoolId)
                    ->where('grade_level', $newLevel)
                    ->first();

                if ($newClass) {
                    $student->update([
                        'class_id' => $newClass->id,
                    ]);
                    $promoted++;
                }
            }
        }

        return back()->with('success', "Promosi siswa selesai: {$promoted} siswa naik kelas, {$graduated} siswa lulus.");
    }

    public function destroy(AcademicYear $academicYear)
    {
        $user = auth()->user();

        if ($academicYear->school_id !== $user->school_id) {
            abort(403);
        }

        if ($academicYear->is_active) {
            return back()->with('error', 'Tahun ajaran aktif tidak bisa dihapus.');
        }

        $hasData = DB::table('grades')
            ->where('academic_year_id', $academicYear->id)
            ->exists();

        if ($hasData) {
            return back()->with('error', 'Tahun ajaran memiliki data nilai, tidak bisa dihapus.');
        }

        $academicYear->delete();

        return back()->with('success', 'Tahun ajaran berhasil dihapus.');
    }
}

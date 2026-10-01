<?php

namespace App\Http\Controllers\Wakamur;

use App\Http\Controllers\Controller;
use App\Models\AttendanceClass;
use App\Models\ClassRoom;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class WakamurController extends Controller
{
    public function dashboard(): View
    {
        $user = auth()->user();
        $schoolId = $user->school_id;

        $startOfWeek = Carbon::now()->startOfWeek()->startOfDay();
        $now = Carbon::now();

        $attendanceQuery = AttendanceClass::where('school_id', $schoolId)
            ->whereBetween('date', [$startOfWeek, $now]);

        $stats = [
            'siswa' => Student::where('school_id', $schoolId)->count(),
            'kelas' => ClassRoom::where('school_id', $schoolId)->count(),
            'guru' => User::where('school_id', $schoolId)->where('role', User::ROLE_GURU)->count(),
            'catatan_pekan' => (clone $attendanceQuery)->count(),
            'hadir_pekan' => (clone $attendanceQuery)->where('status', AttendanceClass::STATUS_HADIR)->count(),
        ];
        $stats['kehadiran_pct'] = $stats['catatan_pekan'] > 0
            ? round($stats['hadir_pekan'] / $stats['catatan_pekan'] * 100, 1)
            : null;

        return view('wakamur.dashboard', compact('stats'));
    }

    public function classes(): View
    {
        $classes = ClassRoom::with(['walis:id,name'])
            ->withCount('students')
            ->orderBy('grade_level')
            ->orderBy('class_name')
            ->get();

        return view('wakamur.classes', compact('classes'));
    }

    public function classDetail(ClassRoom $kelas): View
    {
        $kelas->load('walis:id,name');
        $students = $kelas->students()->orderBy('name')->get();

        return view('wakamur.class-detail', compact('kelas', 'students'));
    }

    public function students(): View
    {
        $students = Student::with(['classRoom:id,class_name,grade_level'])
            ->orderBy('name')
            ->get();

        return view('wakamur.students', compact('students'));
    }
}
<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\AttendanceClass;
use App\Models\ClassRoom;
use App\Models\Grade;
use App\Models\QuranTeachingAssignment;
use App\Models\Student;
use App\Models\Subject;
use App\Models\TahfidzRecord;
use App\Models\User;
use App\Services\AcademicYearContext;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(protected AcademicYearContext $academicYearContext) {}

    public function admin(): View
    {
        $user = auth()->user();
        $schoolId = $user->school_id;
        $school = $user->school;

        $stats = [
            'siswa' => Student::where('school_id', $schoolId)->count(),
            'guru' => User::where('school_id', $schoolId)->where('role', 'guru')->count(),
            'kelas' => ClassRoom::where('school_id', $schoolId)->count(),
            'mapel' => Subject::where('school_id', $schoolId)->count(),
        ];

        // Kehadiran pekan ini
        $weekStart = Carbon::now()->startOfWeek()->startOfDay();
        $weekEnd = Carbon::now()->endOfDay();
        $attendanceRecords = AttendanceClass::where('school_id', $schoolId)
            ->whereBetween('date', [$weekStart, $weekEnd])
            ->get();
        $attendance = $attendanceRecords->count();
        $attendanceHadir = $attendanceRecords->where('status', AttendanceClass::STATUS_HADIR)->count();
        $attendancePct = $attendance > 0 ? round($attendanceHadir / $attendance * 100, 1) : null;

        $ayId = $this->academicYearContext->id();

        // Rekap tahfidz TA aktif
        $tahfidzQuery = TahfidzRecord::where('school_id', $schoolId);
        if ($ayId) {
            $tahfidzQuery->where(fn ($q) => $q->where('academic_year_id', $ayId)->orWhereNull('academic_year_id'));
        }
        $tahfidzRecords = $tahfidzQuery->get();
        $tahfidz = [
            'total' => $tahfidzRecords->count(),
            'avg_score' => $tahfidzRecords->isNotEmpty() ? round($tahfidzRecords->avg('score'), 1) : null,
            'siswa' => $tahfidzRecords->pluck('student_id')->filter()->unique()->count(),
        ];

        // Rata-rata nilai TA aktif
        $gradeQuery = Grade::where('school_id', $schoolId);
        if ($ayId) {
            $gradeQuery->where(fn ($q) => $q->where('academic_year_id', $ayId)->orWhereNull('academic_year_id'));
        }
        $grades = $gradeQuery->get();
        $averageGrade = $grades->isNotEmpty() ? round($grades->avg('score'), 1) : null;

        return view('dashboard.admin', compact(
            'stats', 'school', 'attendance', 'attendanceHadir', 'attendancePct',
            'tahfidz', 'averageGrade'
        ));
    }

    public function guru(): View
    {
        $user = auth()->user();
        $schoolId = $user->school_id;

        $query = \App\Models\ClassSubjectTeacher::where('teacher_id', $user->id)
            ->with(['classRoom.students', 'subject']);

        if ($this->academicYearContext->exists()) {
            $query->where(function ($q2) {
                $q2->where('academic_year_id', $this->academicYearContext->id())
                    ->orWhereNull('academic_year_id');
            });
        }

        $plottedClasses = $query->get();

        $classNames = $plottedClasses->pluck('classRoom.class_name')->unique()->values();
        $uniqueClasses = $plottedClasses->groupBy('class_id')->map(function ($items) {
            $class = $items->first()->classRoom;
            return [
                'class' => $class,
                'subjects' => $items->pluck('subject.name', 'subject_id')->values(),
                'student_count' => $class->students->count(),
            ];
        })->values();

        $totalStudents = $plottedClasses->pluck('classRoom.students')->flatten()->unique('id')->count();
        $totalSubjects = $plottedClasses->pluck('subject.name')->unique()->count();

        $stats = [
            'kelas' => $classNames->count(),
            'siswa' => $totalStudents,
            'mapel' => $totalSubjects,
        ];

        $isWaliKelas = $user->is_wali_kelas;

        $assignmentQuery = QuranTeachingAssignment::where('teacher_id', $user->id)
            ->whereHas('student')
            ->with('student.classRoom');

        if ($this->academicYearContext->exists()) {
            $assignmentQuery->where(function ($q) {
                $q->where('academic_year_id', $this->academicYearContext->id())
                    ->orWhereNull('academic_year_id');
            });
        }

        $assignments = $assignmentQuery->get();
        $hasTahfidz = $assignments->isNotEmpty();
        $isPJtahfidz = $user->hasTeacherRole('PJ Tahfidz') || $hasTahfidz;

        $tahfidzData = null;
        if ($isPJtahfidz) {
            $studentIds = $assignments->pluck('student_id');

            $allRecords = $studentIds->isNotEmpty()
                ? TahfidzRecord::where('teacher_id', $user->id)
                    ->whereIn('student_id', $studentIds)
                    ->with('quranMaster')
                    ->get()
                : collect();

            $totalSetoran = $allRecords->count();
            $avgScore = $totalSetoran > 0 ? round($allRecords->avg('score'), 1) : 0;

            $studentStats = $assignments->map(function ($a) use ($allRecords) {
                $records = $allRecords->where('student_id', $a->student_id);
                $lastRecord = $records->sortByDesc('recorded_date')->first();
                return [
                    'student' => $a->student,
                    'total_setoran' => $records->count(),
                    'last_surah' => $lastRecord?->quranMaster?->surah_name ?? '-',
                    'last_ayat' => $lastRecord ? $lastRecord->ayat_start . '—' . $lastRecord->ayat_end : '-',
                    'last_date' => $lastRecord?->recorded_date ?? null,
                ];
            })->sortByDesc('total_setoran')->values();

            $tahfidzData = [
                'total_siswa' => $assignments->count(),
                'total_setoran' => $totalSetoran,
                'avg_score' => $avgScore,
                'students' => $studentStats,
            ];
        }

        return view('dashboard.guru', compact('stats', 'uniqueClasses', 'isWaliKelas', 'isPJtahfidz', 'tahfidzData'));
    }

    public function keuangan(): View
    {
        return view('dashboard.keuangan');
    }

    public function ortu(): View
    {
        return view('dashboard.ortu');
    }

    public function staff(): View
    {
        return view('dashboard.staff');
    }

    public function wakakur(): View
    {
        $user = auth()->user();
        $schoolId = $user->school_id;

        $stats = [
            'calendars' => \App\Models\AcademicCalendar::where('school_id', $schoolId)->count(),
            'draft'     => \App\Models\AcademicCalendar::where('school_id', $schoolId)->where('status', 'draft')->count(),
            'pending'   => \App\Models\AcademicCalendar::where('school_id', $schoolId)->where('status', 'pending')->count(),
            'final'     => \App\Models\AcademicCalendar::where('school_id', $schoolId)->where('status', 'final')->count(),
        ];

        return view('dashboard.wakakur', compact('stats'));
    }
}

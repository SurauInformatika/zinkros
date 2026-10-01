<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceClass;
use App\Models\ClassRoom;
use App\Models\Grade;
use App\Models\GradeType;
use App\Models\Student;
use App\Models\Subject;
use App\Models\TahfidzRecord;
use App\Services\AcademicYearContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RekapController extends Controller
{
    public function __construct(protected AcademicYearContext $academicYearContext)
    {
    }

    public function index(): View
    {
        $user = auth()->user();
        $schoolId = $user->school_id;

        $classes = ClassRoom::where('school_id', $schoolId)
            ->withCount('students')
            ->with('walis:id,name')
            ->orderBy('grade_level')
            ->orderBy('class_name')
            ->get();

        return view('admin.rekap.index', compact('classes'));
    }

    public function classReport(Request $request, ClassRoom $class): View
    {
        $user = auth()->user();

        if ($class->school_id !== $user->school_id) {
            abort(403);
        }

        $schoolId = $user->school_id;
        $ayId = $this->academicYearContext->id();

        $students = Student::where('class_id', $class->id)->orderBy('name')->get();
        $studentIds = $students->pluck('id')->toArray();

        $subjects = Subject::where('school_id', $schoolId)->orderBy('name')->get();

        $gradeTypes = GradeType::where('school_id', $schoolId)
            ->active()
            ->ordered()
            ->get();

        // Attendance stats per student
        $attendanceQuery = AttendanceClass::where('class_id', $class->id);
        if ($ayId) {
            $attendanceQuery->where('academic_year_id', $ayId);
        }
        $attendanceRecords = $attendanceQuery->get();

        $attendanceStats = $attendanceRecords->groupBy('student_id')->map(function ($records) {
            $total = $records->count();
            return [
                'hadir' => $records->where('status', 'HADIR')->count(),
                'izin' => $records->where('status', 'IZIN')->count(),
                'sakit' => $records->where('status', 'SAKIT')->count(),
                'alpa' => $records->where('status', 'ALPA')->count(),
                'total' => $total,
                'pct_hadir' => $total > 0 ? round($records->where('status', 'HADIR')->count() / $total * 100, 1) : 0,
            ];
        });

        // Grade stats per student per subject
        $gradeQuery = Grade::where('student_id', $studentIds);
        if ($ayId) {
            $gradeQuery->where('academic_year_id', $ayId);
        }
        $allGrades = $gradeQuery->get();

        $gradeStats = $allGrades->groupBy('student_id')->map(function ($studentGrades) use ($subjects, $gradeTypes) {
            $bySubject = $studentGrades->groupBy('subject_id')->map(function ($subjectGrades) use ($gradeTypes) {
                $byType = $subjectGrades->groupBy('grade_type_id')->map(function ($typeGrades) {
                    return round($typeGrades->avg('score'), 1);
                });

                $weightedSum = 0;
                $totalWeight = 0;
                foreach ($byType as $typeId => $avg) {
                    $gt = $gradeTypes->firstWhere('id', $typeId);
                    if ($gt) {
                        $weightedSum += $avg * $gt->weight;
                        $totalWeight += $gt->weight;
                    }
                }

                $finalGrade = $totalWeight > 0 ? round($weightedSum / $totalWeight, 1) : round($subjectGrades->avg('score'), 1);

                return [
                    'avg' => round($subjectGrades->avg('score'), 1),
                    'min' => $subjectGrades->min('score'),
                    'max' => $subjectGrades->max('score'),
                    'count' => $subjectGrades->count(),
                    'final' => $finalGrade,
                    'by_type' => $byType,
                ];
            });

            return $bySubject;
        });

        // Tahfidz stats per student
        $tahfidzQuery = TahfidzRecord::where('student_id', $studentIds);
        if ($ayId) {
            $tahfidzQuery->where('academic_year_id', $ayId);
        }
        $allTahfidz = $tahfidzQuery->get();

        $tahfidzStats = $allTahfidz->groupBy('student_id')->map(function ($records) {
            return [
                'total' => $records->count(),
                'ziadah' => $records->where('activity_type', TahfidzRecord::TYPE_ZIADAH)->count(),
                'murajaah' => $records->where('activity_type', TahfidzRecord::TYPE_MURAJAAH)->count(),
                'total_ayat' => $records->sum(fn ($r) => $r->ayat_end - $r->ayat_start + 1),
                'surahs' => $records->pluck('quran_master_id')->unique()->count(),
                'avg_score' => round($records->avg('score'), 1),
            ];
        });

        // Class summary
        $classSummary = [
            'total_students' => $students->count(),
            'avg_attendance' => $attendanceStats->isNotEmpty() ? round($attendanceStats->avg('pct_hadir'), 1) : 0,
            'total_records' => $attendanceRecords->count(),
        ];

        return view('admin.rekap.class-report', compact(
            'class', 'students', 'subjects', 'gradeTypes',
            'attendanceStats', 'gradeStats', 'tahfidzStats', 'classSummary'
        ));
    }

    public function studentReport(Request $request, Student $student): View
    {
        $user = auth()->user();

        if ($student->school_id !== $user->school_id) {
            abort(403);
        }

        $schoolId = $user->school_id;
        $ayId = $this->academicYearContext->id();
        $ay = $this->academicYearContext->get();

        $student->load('classRoom');

        $subjects = Subject::where('school_id', $schoolId)->orderBy('name')->get();

        $gradeTypes = GradeType::where('school_id', $schoolId)
            ->active()
            ->ordered()
            ->get();

        // Attendance
        $attQuery = AttendanceClass::where('student_id', $student->id);
        if ($ayId) {
            $attQuery->where('academic_year_id', $ayId);
        }
        $attendance = $attQuery->get();
        $attStats = [
            'hadir' => $attendance->where('status', 'HADIR')->count(),
            'izin' => $attendance->where('status', 'IZIN')->count(),
            'sakit' => $attendance->where('status', 'SAKIT')->count(),
            'alpa' => $attendance->where('status', 'ALPA')->count(),
            'total' => $attendance->count(),
        ];
        $attStats['pct_hadir'] = $attStats['total'] > 0 ? round($attStats['hadir'] / $attStats['total'] * 100, 1) : 0;

        // Grades per subject
        $gradeQuery = Grade::where('student_id', $student->id);
        if ($ayId) {
            $gradeQuery->where('academic_year_id', $ayId);
        }
        $allGrades = $gradeQuery->with('gradeType')->get();

        $subjectGrades = $allGrades->groupBy('subject_id')->map(function ($subjectGrades) use ($gradeTypes) {
            $byType = $subjectGrades->groupBy('grade_type_id')->map(function ($typeGrades) {
                return round($typeGrades->avg('score'), 1);
            });

            $weightedSum = 0;
            $totalWeight = 0;
            foreach ($byType as $typeId => $avg) {
                $gt = $gradeTypes->firstWhere('id', $typeId);
                if ($gt) {
                    $weightedSum += $avg * $gt->weight;
                    $totalWeight += $gt->weight;
                }
            }

            $finalGrade = $totalWeight > 0 ? round($weightedSum / $totalWeight, 1) : round($subjectGrades->avg('score'), 1);

            return [
                'avg' => round($subjectGrades->avg('score'), 1),
                'min' => $subjectGrades->min('score'),
                'max' => $subjectGrades->max('score'),
                'count' => $subjectGrades->count(),
                'final' => $finalGrade,
                'by_type' => $byType,
            ];
        });

        // Tahfidz
        $tahQuery = TahfidzRecord::where('student_id', $student->id);
        if ($ayId) {
            $tahQuery->where('academic_year_id', $ayId);
        }
        $tahfidz = $tahQuery->with('quranMaster')->get();
        $tahfidzStats = null;
        if ($tahfidz->isNotEmpty()) {
            $tahfidzStats = [
                'total' => $tahfidz->count(),
                'ziadah' => $tahfidz->where('activity_type', TahfidzRecord::TYPE_ZIADAH)->count(),
                'murajaah' => $tahfidz->where('activity_type', TahfidzRecord::TYPE_MURAJAAH)->count(),
                'total_ayat' => $tahfidz->sum(fn ($r) => $r->ayat_end - $r->ayat_start + 1),
                'surahs' => $tahfidz->pluck('quran_master_id')->unique()->count(),
                'surah_names' => $tahfidz->pluck('quranMaster.surah_name')->filter()->unique()->values(),
                'avg_score' => round($tahfidz->avg('score'), 1),
            ];
        }

        return view('admin.rekap.student-report', compact(
            'student', 'ay', 'subjects', 'gradeTypes',
            'attStats', 'subjectGrades', 'tahfidzStats'
        ));
    }
}

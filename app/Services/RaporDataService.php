<?php

namespace App\Services;

use App\Models\AttendanceClass;
use App\Models\Grade;
use App\Models\GradeType;
use App\Models\Student;
use App\Models\Subject;
use App\Models\TahfidzRecord;
use App\Models\User;
use stdClass;

class RaporDataService
{
    public function __construct(protected AcademicYearContext $academicYearContext) {}

    /**
     * Konteks data lengkap untuk mencetak rapor satu siswa.
     * Tidak melakukan pengecekan hak akses — controller yang menjaga.
     */
    public function forStudent(User $user, Student $student, int $semester): array
    {
        $school = $user->school;
        $ay = $this->academicYearContext->get();
        $ayId = $this->academicYearContext->id();

        $student->load('classRoom');

        $subjects = Subject::where('school_id', $user->school_id)->orderBy('name')->get();

        $gradeTypes = GradeType::where('school_id', $user->school_id)
            ->active()
            ->ordered()
            ->get();

        $attendance = AttendanceClass::where('student_id', $student->id)
            ->when($ayId, fn ($q) => $q->where('academic_year_id', $ayId))
            ->get();
        $attStats = [
            'hadir' => $attendance->where('status', 'HADIR')->count(),
            'izin' => $attendance->where('status', 'IZIN')->count(),
            'sakit' => $attendance->where('status', 'SAKIT')->count(),
            'alpa' => $attendance->where('status', 'ALPA')->count(),
            'total' => $attendance->count(),
        ];

        $gradeQuery = Grade::where('student_id', $student->id)
            ->with('gradeType')
            ->when($ayId, fn ($q) => $q->where('academic_year_id', $ayId));
        $allGrades = $gradeQuery->get();

        $subjectGrades = $allGrades->groupBy('subject_id')->map(function ($subjectGrades) use ($gradeTypes) {
            $byType = $subjectGrades->groupBy('grade_type_id')->map(function ($typeGrades) {
                $scores = $typeGrades->map(fn ($g) => $g->finalScore())
                    ->filter(fn ($s) => $s !== null)
                    ->values();

                return $scores->isNotEmpty() ? round($scores->avg(), 1) : null;
            });

            $weightedSum = 0;
            $totalWeight = 0;
            foreach ($byType as $typeId => $avg) {
                if ($avg === null) {
                    continue;
                }
                $gt = $gradeTypes->firstWhere('id', $typeId);
                if ($gt) {
                    $weightedSum += $avg * $gt->weight;
                    $totalWeight += $gt->weight;
                }
            }

            $scores = $subjectGrades->map(fn ($g) => $g->finalScore())
                ->filter(fn ($s) => $s !== null)
                ->values();

            $finalGrade = null;
            if ($scores->isNotEmpty()) {
                $finalGrade = $totalWeight > 0 && $totalWeight >= 100
                    ? round($weightedSum / $totalWeight, 1)
                    : round(array_sum($scores->all()) / $scores->count(), 1);
            }

            $kkm = (int) ($subjectGrades->first()->subject->kkm ?? 80);

            return [
                'avg' => $scores->isNotEmpty() ? round($scores->avg(), 1) : null,
                'by_type' => $byType,
                'final' => $finalGrade,
                'kkm' => $kkm,
                'predikat' => $finalGrade !== null ? self::predikat($finalGrade) : null,
            ];
        });

        $tahfidz = TahfidzRecord::where('student_id', $student->id)
            ->with('quranMaster')
            ->when($ayId, fn ($q) => $q->where('academic_year_id', $ayId))
            ->get();
        $tahfidzStats = null;
        if ($tahfidz->isNotEmpty()) {
            $tahfidzStats = [
                'total' => $tahfidz->count(),
                'total_ayat' => $tahfidz->sum(fn ($r) => $r->ayat_end - $r->ayat_start + 1),
                'surah_names' => $tahfidz->pluck('quranMaster.surah_name')->filter()->unique()->values(),
                'avg_score' => round($tahfidz->avg('score'), 1),
            ];
        }

        $kepalaSekolah = User::where('school_id', $user->school_id)
            ->where('role', 'kepsek')
            ->first();

        $parent = $student->parents()
            ->wherePivot('is_primary', true)
            ->first() ?: $student->parents()->first();

        return compact(
            'student', 'school', 'ay', 'semester', 'subjects', 'gradeTypes',
            'attStats', 'subjectGrades', 'tahfidzStats', 'kepalaSekolah', 'parent', 'user'
        );
    }

    /**
     * Konteks contoh (sampel) untuk preview editor desain rapor.
     * Dipakai sebanyak mungkin siswa asli agar preview realistis;
     * bila tidak ada, pakai data placeholder sehingga layout tetap tergambar.
     */
    public function sampleFor(User $user, int $semester, ?string $studentId = null): array
    {
        $student = null;
        if ($studentId) {
            $student = Student::where('school_id', $user->school_id)->whereKey($studentId)->first();
        }
        if (! $student) {
            $student = Student::where('school_id', $user->school_id)
                ->orderByRaw('class_id IS NULL')->orderBy('name')
                ->first();
        }

        if ($student) {
            return $this->forStudent($user, $student, $semester);
        }

        $placeholder = new stdClass;
        $placeholder->id = null;
        $placeholder->class_id = null;
        $placeholder->name = 'Nama Siswa';
        $placeholder->nis = '';
        $placeholder->nisn = '';
        $placeholder->gender = 'L';
        $placeholder->classRoom = null;

        $school = $user->school;
        $ay = $this->academicYearContext->get();
        $ayId = $this->academicYearContext->id();

        $subjects = Subject::where('school_id', $user->school_id)->orderBy('name')->get();
        $gradeTypes = GradeType::where('school_id', $user->school_id)->active()->ordered()->get();

        $subjectGrades = collect();
        foreach ($subjects as $subject) {
            $subjectGrades->put($subject->id, [
                'avg' => null,
                'by_type' => collect(),
                'final' => null,
                'kkm' => (int) ($subject->kkm ?? 80),
                'predikat' => null,
            ]);
        }

        $kepalaSekolah = User::where('school_id', $user->school_id)->where('role', 'kepsek')->first();

        return [
            'student' => $placeholder,
            'school' => $school,
            'ay' => $ay,
            'semester' => $semester,
            'subjects' => $subjects,
            'gradeTypes' => $gradeTypes,
            'attStats' => ['hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alpa' => 0, 'total' => 0],
            'subjectGrades' => $subjectGrades,
            'tahfidzStats' => null,
            'kepalaSekolah' => $kepalaSekolah,
            'parent' => null,
            'user' => $user,
        ];
    }

    public static function predikat(float $score): string
    {
        return match (true) {
            $score >= 90 => 'A',
            $score >= 80 => 'B',
            $score >= 70 => 'C',
            $score >= 60 => 'D',
            default => 'E',
        };
    }
}
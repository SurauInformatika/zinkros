<?php

namespace App\Observers;

use App\Models\QuranTeachingAssignment;
use App\Services\QuranTargetService;

class QuranTeachingAssignmentObserver
{
    public function __construct(protected QuranTargetService $quranTargetService) {}

    /**
     * Saat assignment baru dibuat, otomatis terapkan template target
     * universal (jika tersedia) untuk siswa tersebut.
     */
    public function created(QuranTeachingAssignment $assignment): void
    {
        $student = $assignment->student;
        $teacher = $assignment->teacher;

        if (!$student || !$teacher) {
            return;
        }

        $this->quranTargetService->autoApplyTemplate($student, $teacher);
    }
}

<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'school_id',
    'attendance_subject_id',
    'student_id',
    'activity_type',
    'quran_master_id',
    'ayat_start',
    'ayat_end',
    'grade',
])]
class QuranProgressLog extends Model
{
    use HasUuids, BelongsToSchool;

    public const ACTIVITY_ZIADAH = 'ZIADAH';

    public const ACTIVITY_MURAJAAH = 'MURAJAAH';

    public const GRADE_A = 'A';

    public const GRADE_B = 'B';

    public const GRADE_C = 'C';

    public function attendanceSubject(): BelongsTo
    {
        return $this->belongsTo(AttendanceSubject::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function quranMaster(): BelongsTo
    {
        return $this->belongsTo(QuranMaster::class);
    }
}

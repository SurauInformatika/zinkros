<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['school_id', 'student_id', 'subject_id', 'teacher_id', 'status', 'date', 'timestamp', 'topic', 'notes'])]
class AttendanceSubject extends Model
{
    use HasUuids, BelongsToSchool;

    public const STATUS_HADIR = 'HADIR';

    public const STATUS_IZIN = 'IZIN';

    public const STATUS_SAKIT = 'SAKIT';

    public const STATUS_ALPA = 'ALPA';

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function quranProgressLog(): HasMany
    {
        return $this->hasMany(QuranProgressLog::class);
    }
}

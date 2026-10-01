<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['school_id', 'class_id', 'student_id', 'recorded_by', 'status', 'date', 'notes'])]
class AttendanceClass extends Model
{
    use HasUuids, BelongsToSchool;

    public const STATUS_HADIR = 'HADIR';

    public const STATUS_IZIN = 'IZIN';

    public const STATUS_SAKIT = 'SAKIT';

    public const STATUS_ALPA = 'ALPA';

    public function classRoom(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}

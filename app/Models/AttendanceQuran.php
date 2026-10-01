<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['school_id', 'academic_year_id', 'student_id', 'teacher_id', 'date', 'status', 'notes'])]
class AttendanceQuran extends Model
{
    use HasUuids, BelongsToSchool;

    protected $table = 'attendance_quran';

    public const STATUS_HADIR = 'HADIR';
    public const STATUS_SAKIT = 'SAKIT';
    public const STATUS_IZIN = 'IZIN';
    public const STATUS_ALPA = 'ALPA';

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}

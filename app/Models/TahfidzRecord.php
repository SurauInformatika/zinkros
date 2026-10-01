<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'school_id',
    'academic_year_id',
    'student_id',
    'teacher_id',
    'quran_master_id',
    'ayat_start',
    'ayat_end',
    'activity_type',
    'score',
    'status',
    'recorded_date',
    'notes',
])]
class TahfidzRecord extends Model
{
    use HasUuids, BelongsToSchool;

    public const TYPE_ZIADAH = 'ZIADAH';
    public const TYPE_MURAJAAH = 'MURAJAAH';

    public const STATUS_HADIR = null;
    public const STATUS_SAKIT = 'SAKIT';
    public const STATUS_IZIN = 'IZIN';

    protected function predikat(): Attribute
    {
        return Attribute::get(fn () => self::scoreToPredikat($this->score));
    }

    public static function scoreToPredikat(int $score): string
    {
        return match (true) {
            $score >= 90 => 'A',
            $score >= 75 => 'B',
            $score >= 60 => 'C',
            default => 'D',
        };
    }

    public static function predikatColor(string $predikat): string
    {
        return match ($predikat) {
            'A' => 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400',
            'B' => 'bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400',
            'C' => 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400',
            default => 'bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400',
        };
    }

    public static function attendanceStatus(?string $status): string
    {
        return match ($status) {
            self::STATUS_SAKIT => 'SAKIT',
            self::STATUS_IZIN => 'IZIN',
            default => 'HADIR',
        };
    }

    public function getAttendanceLabelAttribute(): string
    {
        return self::attendanceStatus($this->status);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function quranMaster(): BelongsTo
    {
        return $this->belongsTo(QuranMaster::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function scopeForStudent($query, string $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeForTeacher($query, string $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }
}

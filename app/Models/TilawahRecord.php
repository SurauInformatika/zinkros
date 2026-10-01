<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'school_id',
    'academic_year_id',
    'student_id',
    'teacher_id',
    'reading_level_id',
    'page_start',
    'page_end',
    'score',
    'status',
    'recorded_date',
    'notes',
])]
class TilawahRecord extends Model
{
    use HasUuids, BelongsToSchool;

    public const STATUS_SAKIT = 'SAKIT';
    public const STATUS_IZIN = 'IZIN';

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function readingLevel(): BelongsTo
    {
        return $this->belongsTo(QuranReadingLevel::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function pagesRead(): int
    {
        return $this->page_end - $this->page_start + 1;
    }

    public function isPresent(): bool
    {
        return $this->status === null;
    }
}

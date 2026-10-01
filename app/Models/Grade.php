<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['school_id', 'academic_year_id', 'student_id', 'subject_id', 'teacher_id', 'grade_type_id', 'date', 'score', 'notes', 'remedial_score', 'remedial_capped', 'remedial_notes', 'remedial_by', 'remedial_at'])]
class Grade extends Model
{
    use HasUuids, BelongsToSchool;

    public function hasRemedial(): bool
    {
        return $this->remedial_capped !== null;
    }

    public function finalScore(): ?float
    {
        return $this->hasRemedial() ? (float) $this->remedial_capped : ($this->score !== null ? (float) $this->score : null);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

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
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function gradeType(): BelongsTo
    {
        return $this->belongsTo(GradeType::class);
    }

    public function remedialTeacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'remedial_by');
    }
}

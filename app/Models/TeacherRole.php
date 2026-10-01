<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['school_id', 'teacher_id', 'role_name', 'is_student_related', 'description'])]
class TeacherRole extends Model
{
    use HasUuids, BelongsToSchool;

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function scopeStudentRelated($query)
    {
        return $query->where('is_student_related', true);
    }

    public function scopeBySchool($query, string $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }
}

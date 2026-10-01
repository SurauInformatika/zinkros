<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['school_id', 'name', 'type', 'kkm'])]
class Subject extends Model
{
    use HasUuids, BelongsToSchool;

    public const TYPE_GENERAL = 'GENERAL';

    public const TYPE_QURAN = 'QURAN';

    public function isQuran(): bool
    {
        return $this->type === self::TYPE_QURAN;
    }

    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(ClassRoom::class, 'class_subject_teacher', 'subject_id', 'class_id')
            ->withPivot('teacher_id');
    }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'class_subject_teacher', 'subject_id', 'teacher_id')
            ->withPivot('class_id');
    }

    public function assignedTeachers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'teacher_subject', 'subject_id', 'teacher_id');
    }

    public function attendanceSubjects(): HasMany
    {
        return $this->hasMany(AttendanceSubject::class);
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    public function classSubjectTeachers(): HasMany
    {
        return $this->hasMany(ClassSubjectTeacher::class);
    }
}

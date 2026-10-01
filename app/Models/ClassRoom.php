<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['school_id', 'class_name', 'grade_level'])]
class ClassRoom extends Model
{
    use HasUuids, BelongsToSchool;

    protected $table = 'classes';

    public function homerooms(): HasMany
    {
        return $this->hasMany(ClassHomeroom::class, 'class_id')->orderBy('sort');
    }

    public function walis(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'class_homerooms', 'class_id', 'user_id')
            ->withPivot('label', 'sort')
            ->orderBy('class_homerooms.sort');
    }

    public function isWali(User $user): bool
    {
        return $this->homerooms()->where('user_id', $user->id)->exists();
    }

    public function waliNames(): string
    {
        $names = $this->walis->map(function ($user) {
            $label = $user->pivot->label;
            return $label ? "{$user->name} ({$label})" : $user->name;
        });

        return $names->isEmpty() ? '-' : $names->implode(', ');
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'class_subject_teacher', 'class_id', 'subject_id')
            ->withPivot('teacher_id');
    }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'class_subject_teacher', 'class_id', 'teacher_id')
            ->withPivot('subject_id');
    }

    public function attendanceClasses(): HasMany
    {
        return $this->hasMany(AttendanceClass::class, 'class_id');
    }
}

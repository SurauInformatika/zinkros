<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['school_id', 'nis', 'nisn', 'rfid_tag_id', 'name', 'gender', 'class_id'])]
class Student extends Model
{
    use HasUuids, BelongsToSchool;

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Student $student) {
            if (! $student->school_id) {
                return;
            }

            \App\Models\School::find($student->school_id)?->assertWithinQuota('siswa');
        });
    }

    public function classRoom(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'student_parent', 'student_id', 'user_id')
            ->withPivot('relation', 'is_primary', 'school_id')
            ->withTimestamps();
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function quranTargets(): HasMany
    {
        return $this->hasMany(StudentQuranTarget::class);
    }

    public function tilawahRecords(): HasMany
    {
        return $this->hasMany(TilawahRecord::class);
    }

    public function gateAttendances(): HasMany
    {
        return $this->hasMany(AttendanceGate::class);
    }

    public function subjectAttendances(): HasMany
    {
        return $this->hasMany(AttendanceSubject::class);
    }

    public function quranProgressLogs(): HasMany
    {
        return $this->hasMany(QuranProgressLog::class);
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }
}

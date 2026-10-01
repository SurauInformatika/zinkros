<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentQuranTarget extends Model
{
    use HasUuids;

    protected $table = 'student_quran_targets';
    protected $guarded = [];

    protected $casts = [
        'target_date' => 'date',
        'is_active' => 'boolean',
    ];

    // Relationships

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

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StudentQuranTargetItem::class, 'target_id');
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Computed

    public function totalAyat(): int
    {
        return $this->items->sum(fn ($item) => $item->totalAyat());
    }

    public function daysRemaining(): int
    {
        return (int) now()->diffInDays($this->target_date, false);
    }

    public function isOverdue(): bool
    {
        return $this->target_date->isPast();
    }
}

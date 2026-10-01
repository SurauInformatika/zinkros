<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['school_id', 'name', 'month', 'day', 'calendar_type', 'is_active'])]
class RecurringHoliday extends Model
{
    use HasUuids;

    protected $table = 'recurring_holidays';

    protected function casts(): array
    {
        return [
            'month' => 'integer',
            'day' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function scopeForSchool($query, $schoolId)
    {
        return $query->where(function ($q) use ($schoolId) {
            $q->where('school_id', $schoolId)
              ->orWhereNull('school_id');
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

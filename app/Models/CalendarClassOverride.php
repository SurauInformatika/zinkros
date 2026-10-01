<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['academic_calendar_id', 'grade_level', 'override_type', 'start_date', 'end_date', 'title', 'notes'])]
class CalendarClassOverride extends Model
{
    use HasUuids;

    protected $table = 'calendar_class_overrides';

    protected $casts = [
        'grade_level' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function academicCalendar(): BelongsTo
    {
        return $this->belongsTo(AcademicCalendar::class);
    }
}

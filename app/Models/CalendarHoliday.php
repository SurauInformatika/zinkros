<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['academic_calendar_id', 'date', 'name', 'type'])]
class CalendarHoliday extends Model
{
    use HasUuids;

    protected $table = 'calendar_holidays';

    protected $casts = [
        'date' => 'date',
    ];

    public function academicCalendar(): BelongsTo
    {
        return $this->belongsTo(AcademicCalendar::class);
    }
}

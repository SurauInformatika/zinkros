<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['academic_calendar_id', 'grade_level_start', 'grade_level_end', 'jp_duration_minutes', 'jp_per_day'])]
class CalendarLevelStructure extends Model
{
    use HasUuids;

    public const DAYS = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'ahad'];

    protected $table = 'calendar_level_structures';

    protected function casts(): array
    {
        return [
            'grade_level_start' => 'integer',
            'grade_level_end' => 'integer',
            'jp_duration_minutes' => 'integer',
            'jp_per_day' => 'array',
        ];
    }

    public function academicCalendar(): BelongsTo
    {
        return $this->belongsTo(AcademicCalendar::class);
    }

    public function jpForDay(string $day): int
    {
        $map = is_array($this->jp_per_day) ? $this->jp_per_day : [];
        return (int) ($map[$day] ?? 0);
    }

    public function jpPerWeek(): int
    {
        $total = 0;
        foreach (self::DAYS as $day) {
            $total += $this->jpForDay($day);
        }
        return $total;
    }
}
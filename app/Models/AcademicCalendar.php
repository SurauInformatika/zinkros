<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicCalendar extends Model
{
    use HasUuids, BelongsToSchool;

    protected $table = 'academic_calendars';

    protected $fillable = ['school_id', 'academic_year_id', 'template_id', 'name', 'semester', 'source', 'start_date', 'end_date', 'status', 'version', 'parent_version_id', 'created_by', 'approved_by', 'approved_at', 'reject_reason', 'semester_2_start_date'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'semester_2_start_date' => 'date',
        'semester' => 'integer',
        'version' => 'integer',
        'approved_at' => 'datetime',
    ];

    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_FINAL = 'final';
    const STATUS_ARCHIVED = 'archived';

    const SEMESTER_ANNUAL = 0;

    public function isAnnual(): bool
    {
        return (int) $this->semester === self::SEMESTER_ANNUAL;
    }

    public function semesterLabel(): string
    {
        return $this->isAnnual() ? 'Tahunan' : 'Semester ' . $this->semester;
    }

    public function hasExplicitSemesterBoundaries(): bool
    {
        return $this->semester_2_start_date !== null;
    }

    public function semester1EndDate(): ?\Illuminate\Support\Carbon
    {
        if (!$this->hasExplicitSemesterBoundaries()) {
            return null;
        }
        return $this->semester_2_start_date->copy()->subDay();
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(KaldikTemplate::class, 'template_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function parentVersion(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_version_id');
    }

    public function levelStructures(): HasMany
    {
        return $this->hasMany(CalendarLevelStructure::class, 'academic_calendar_id');
    }

    public function holidays(): HasMany
    {
        return $this->hasMany(CalendarHoliday::class, 'academic_calendar_id');
    }

    public function classOverrides(): HasMany
    {
        return $this->hasMany(CalendarClassOverride::class, 'academic_calendar_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(self::class, 'parent_version_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_FINAL);
    }

    public function scopeLatestVersion($query)
    {
        return $query->orderByDesc('version');
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isFinal(): bool
    {
        return $this->status === self::STATUS_FINAL;
    }

    public function totalWeeks(): int
    {
        $totalDays = $this->start_date->diffInDays($this->end_date) + 1;
        return (int) ceil($totalDays / 7);
    }

    public function effectiveWeeks(): int
    {
        $holidayCount = $this->holidays()->count();
        $totalWeeks = $this->totalWeeks();
        return max(1, $totalWeeks - (int) ceil($holidayCount / 5));
    }

    public function totalJp(): int
    {
        $effectiveWeeks = $this->effectiveWeeks();
        return $effectiveWeeks * $this->jpPerWeek();
    }

    public function learningDays(): array
    {
        $days = [];
        foreach ($this->levelStructures as $ls) {
            foreach (CalendarLevelStructure::DAYS as $day) {
                if ($ls->jpForDay($day) > 0) {
                    $days[$day] = true;
                }
            }
        }
        return $days;
    }

    public function jpPerWeek(): int
    {
        $max = 0;
        foreach ($this->levelStructures as $ls) {
            $max = max($max, $ls->jpPerWeek());
        }
        return $max;
    }

    public function maxGradeLevel(): int
    {
        $max = ClassRoom::where('school_id', $this->school_id)->max('grade_level');
        return (int) ($max ?: 12);
    }

    public function minutesByLevel(): array
    {
        $effectiveWeeks = $this->effectiveWeeks();

        $rows = [];
        foreach ($this->levelStructures->sortBy('grade_level_start') as $ls) {
            $label = 'Kelas ' . $ls->grade_level_start
                . ($ls->grade_level_start !== $ls->grade_level_end ? ' – ' . $ls->grade_level_end : '');
            $rows[] = $this->minutesRow(
                $label,
                (int) $ls->jp_duration_minutes,
                $ls->jpPerWeek(),
                $effectiveWeeks
            );
        }
        return $rows;
    }

    public function levelStructureWarnings(): array
    {
        $rows = $this->levelStructures->sortBy('grade_level_start');
        if ($rows->isEmpty()) {
            return ['Belum ada struktur durasi per jenjang — memakai durasi default.'];
        }

        $warnings = [];
        $covered = [];
        foreach ($rows as $ls) {
            if ($ls->grade_level_start > $ls->grade_level_end) {
                $warnings[] = 'Rentang kelas ' . $ls->grade_level_start . '–' . $ls->grade_level_end . ' tidak valid.';
                continue;
            }
            for ($g = $ls->grade_level_start; $g <= $ls->grade_level_end; $g++) {
                if (in_array($g, $covered, true)) {
                    $warnings[] = 'Kelas ' . $g . ' tercakup oleh lebih dari satu rentang durasi.';
                    break;
                }
                $covered[] = $g;
            }
        }

        sort($covered);
        $missing = [];
        $cursor = 1;
        foreach ($covered as $g) {
            while ($cursor < $g) {
                $missing[] = $cursor++;
            }
            $cursor = $g + 1;
        }
        while ($cursor <= $this->maxGradeLevel()) {
            $missing[] = $cursor++;
        }

        if ($missing) {
            $warnings[] = 'Kelas tanpa durasi: ' . implode(', ', $missing) . '. Lengkapi agar semua jenjang memiliki durasi JP.';
        }

        return $warnings;
    }

    private function minutesRow(string $label, int $duration, int $jpPerWeek, int $effectiveWeeks): array
    {
        return [
            'label' => $label,
            'duration' => $duration,
            'jp_per_week' => $jpPerWeek,
            'minutes_per_week' => $jpPerWeek * $duration,
            'minutes_year' => $jpPerWeek * $duration * $effectiveWeeks,
            'is_default' => false,
        ];
    }
}

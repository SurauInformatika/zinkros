<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'source', 'default_day_structure', 'default_level_structure', 'default_holidays', 'is_active'])]
class KaldikTemplate extends Model
{
    use HasUuids;

    protected $table = 'kaldik_templates';

    protected function casts(): array
    {
        return [
            'default_day_structure' => 'array',
            'default_level_structure' => 'array',
            'default_holidays' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function academicCalendars(): HasMany
    {
        return $this->hasMany(AcademicCalendar::class, 'template_id');
    }
}

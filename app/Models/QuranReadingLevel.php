<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

#[Fillable(['kind', 'number', 'label', 'pages', 'school_id', 'sort_order'])]
class QuranReadingLevel extends Model
{
    use HasUuids;

    public const KIND_JILID = 'JILID';
    public const KIND_JUZ = 'JUZ';
    public const KIND_IQRA = 'IQRA';
    public const KIND_UMMI = 'UMMI';

    public const TYPES = [
        self::KIND_JILID => 'Jilid',
        self::KIND_JUZ => 'Juz',
        self::KIND_IQRA => 'Iqra',
        self::KIND_UMMI => 'UMMI',
        'LAIN' => 'Lainnya',
    ];

    /**
     * The reading catalog a school actually uses.
     *
     * A school that has configured its own catalog (any school-owned levels,
     * or the school explicitly customized/emptied it) uses ONLY its own levels,
     * even when that list is empty. Otherwise it falls back to the global
     * (seed) Al-Qur'an catalog.
     */
    public static function forSchool(?string $schoolId): Collection
    {
        if ($schoolId) {
            $customized = (bool) School::where('id', $schoolId)->value('reading_catalog_customized');
            if ($customized || static::hasSchoolOwned($schoolId)) {
                return static::where('school_id', $schoolId)
                    ->orderBy('sort_order')
                    ->orderByRaw("FIELD(kind, 'JILID', 'JUZ', 'IQRA', 'UMMI')")
                    ->orderBy('number')
                    ->get();
            }
        }

        return static::whereNull('school_id')
            ->orderBy('sort_order')
            ->orderByRaw("FIELD(kind, 'JILID', 'JUZ', 'IQRA', 'UMMI')")
            ->orderBy('number')
            ->get();
    }

    /**
     * Whether a school has configured levels of its own (an empty list means
     * it falls back to the global catalog).
     */
    public static function hasSchoolOwned(string $schoolId): bool
    {
        return static::where('school_id', $schoolId)->exists();
    }

    /**
     * Whether the school should be marked as owning/customizing its catalog.
     * Once true, the school's effective catalog is exactly its school-owned
     * rows (possibly none), and the global catalog stops applying.
     */
    public static function markSchoolCustomized(?string $schoolId): void
    {
        if ($schoolId) {
            School::where('id', $schoolId)->update(['reading_catalog_customized' => true]);
        }
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function tilawahRecords(): HasMany
    {
        return $this->hasMany(TilawahRecord::class, 'reading_level_id');
    }

    public function displayLabel(): string
    {
        return $this->label;
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentQuranTargetItem extends Model
{
    use HasUuids;

    protected $table = 'student_quran_target_items';
    protected $guarded = [];

    public function target(): BelongsTo
    {
        return $this->belongsTo(StudentQuranTarget::class, 'target_id');
    }

    public function quranMaster(): BelongsTo
    {
        return $this->belongsTo(QuranMaster::class);
    }

    public function totalAyat(): int
    {
        return $this->ayat_end - $this->ayat_start + 1;
    }
}

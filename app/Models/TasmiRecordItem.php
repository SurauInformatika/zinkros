<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'tasmi_record_id',
    'page_number',
    'quran_master_id',
    'ayat_start',
    'ayat_end',
    'score',
    'fluency_score',
    'tajwid_score',
    'fasohah_score',
    'notes',
])]
class TasmiRecordItem extends Model
{
    use HasUuids;

    public function tasmiRecord(): BelongsTo
    {
        return $this->belongsTo(TasmiRecord::class);
    }

    public function quranMaster(): BelongsTo
    {
        return $this->belongsTo(QuranMaster::class);
    }
}

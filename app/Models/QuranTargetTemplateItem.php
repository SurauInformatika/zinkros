<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuranTargetTemplateItem extends Model
{
    use HasUuids;

    protected $table = 'quran_target_template_items';
    protected $guarded = [];

    public function template(): BelongsTo
    {
        return $this->belongsTo(QuranTargetTemplate::class, 'template_id');
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

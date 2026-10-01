<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['surah_number', 'surah_name', 'total_ayats'])]
class QuranMaster extends Model
{
    use HasUuids;

    public function progressLogs(): HasMany
    {
        return $this->hasMany(QuranProgressLog::class);
    }
}

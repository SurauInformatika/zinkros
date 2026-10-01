<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['school_id', 'student_id', 'check_in_at', 'check_out_at', 'date'])]
class AttendanceGate extends Model
{
    use HasUuids, BelongsToSchool;

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}

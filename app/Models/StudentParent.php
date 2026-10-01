<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['school_id', 'student_id', 'user_id', 'relation', 'is_primary'])]
class StudentParent extends Model
{
    use HasUuids, BelongsToSchool;

    public const RELATION_AYAH = 'AYAH';
    public const RELATION_IBU = 'IBU';
    public const RELATION_WALI = 'WALI';

    protected $table = 'student_parent';

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
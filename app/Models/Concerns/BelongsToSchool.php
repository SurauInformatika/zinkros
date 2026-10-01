<?php

namespace App\Models\Concerns;

use App\Models\Scopes\SchoolScope;
use App\Services\SchoolContext;

trait BelongsToSchool
{
    public static function bootBelongsToSchool(): void
    {
        static::addGlobalScope(new SchoolScope);

        static::creating(function ($model) {
            if (! $model->school_id) {
                $model->school_id = app(SchoolContext::class)->id();
            }
        });
    }
}

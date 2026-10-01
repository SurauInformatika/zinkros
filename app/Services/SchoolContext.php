<?php

namespace App\Services;

class SchoolContext
{
    protected ?string $schoolId = null;

    public function set(?string $schoolId): void
    {
        $this->schoolId = $schoolId;
    }

    public function id(): ?string
    {
        return $this->schoolId;
    }
}

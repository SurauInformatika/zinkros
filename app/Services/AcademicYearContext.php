<?php

namespace App\Services;

use App\Models\AcademicYear;

class AcademicYearContext
{
    protected ?AcademicYear $academicYear = null;

    public function set(?AcademicYear $academicYear): void
    {
        $this->academicYear = $academicYear;
    }

    public function get(): ?AcademicYear
    {
        return $this->academicYear;
    }

    public function id(): ?string
    {
        return $this->academicYear?->id;
    }

    public function exists(): bool
    {
        return $this->academicYear !== null;
    }
}

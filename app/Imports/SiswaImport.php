<?php

namespace App\Imports;

use App\Models\ClassRoom;
use App\Models\Student;
use App\Models\StudentParent;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class SiswaImport implements ToModel, WithHeadingRow, WithValidation
{
    private string $schoolId;
    private int $imported = 0;
    private int $skipped = 0;
    private int $skippedNoClass = 0;

    public function __construct(string $schoolId)
    {
        $this->schoolId = $schoolId;
    }

    public function prepareForValidation(array $row): array
    {
        $row['nisn'] = isset($row['nisn']) && $row['nisn'] !== '' ? (string) $row['nisn'] : null;
        $row['nis'] = isset($row['nis']) && $row['nis'] !== '' ? (string) $row['nis'] : null;
        $row['rfid'] = isset($row['rfid']) && $row['rfid'] !== '' ? (string) $row['rfid'] : null;
        $row['gender'] = isset($row['gender']) && $row['gender'] !== '' ? strtoupper(trim($row['gender'])) : null;
        return $row;
    }

    public function model(array $row): ?Student
    {
        $existing = null;

        if (!empty($row['nis'])) {
            $existing = Student::where('school_id', $this->schoolId)
                ->where('nis', $row['nis'])
                ->first();
        } elseif (!empty($row['nisn'])) {
            $existing = Student::where('school_id', $this->schoolId)
                ->where('nisn', $row['nisn'])
                ->first();
        }

        if ($existing) {
            $this->skipped++;
            return null;
        }

        $classRoom = ClassRoom::where('school_id', $this->schoolId)
            ->where('class_name', $row['nama_kelas'])
            ->first();

        if (!$classRoom) {
            $this->skippedNoClass++;
            return null;
        }

        $parentUserId = null;
        if (!empty($row['email_orangtua'])) {
            $parent = User::where('school_id', $this->schoolId)
                ->where('email', $row['email_orangtua'])
                ->where('role', 'ortu')
                ->first();
            $parentUserId = $parent?->id;
        }

        $this->imported++;

        $student = new Student([
            'school_id' => $this->schoolId,
            'name' => $row['nama'],
            'gender' => $row['gender'] ?? null,
            'nisn' => $row['nisn'],
            'nis' => $row['nis'] ?? null,
            'class_id' => $classRoom?->id,
            'rfid_tag_id' => $row['rfid'] ?? null,
        ]);

        $student->save();

        if (!empty($parentUserId)) {
            StudentParent::create([
                'school_id' => $this->schoolId,
                'student_id' => $student->id,
                'user_id' => $parentUserId,
                'relation' => StudentParent::RELATION_WALI,
                'is_primary' => true,
            ]);
        }

        return $student;
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'gender' => ['nullable', 'string', 'in:L,P'],
            'nisn' => ['nullable', 'string', 'max:20'],
            'nis' => ['nullable', 'string', 'max:20'],
            'nama_kelas' => ['required', 'string', 'max:255'],
            'email_orangtua' => ['nullable', 'email', 'max:255'],
            'rfid' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function getImportedCount(): int
    {
        return $this->imported;
    }

    public function getSkippedCount(): int
    {
        return $this->skipped;
    }

    public function getSkippedNoClassCount(): int
    {
        return $this->skippedNoClass;
    }
}

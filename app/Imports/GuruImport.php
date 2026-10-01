<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class GuruImport implements ToModel, WithHeadingRow, WithValidation
{
    private string $schoolId;
    private int $imported = 0;
    private int $skipped = 0;

    public function __construct(string $schoolId)
    {
        $this->schoolId = $schoolId;
    }

    public function model(array $row): ?User
    {
        $existing = User::where('school_id', $this->schoolId)
            ->where('email', $row['email'])
            ->first();

        if ($existing) {
            $this->skipped++;
            return null;
        }

        $this->imported++;

        return new User([
            'school_id' => $this->schoolId,
            'name' => $row['nama'],
            'email' => $row['email'],
            'phone' => $row['telepon'] ?? null,
            'position' => $row['jabatan'] ?? null,
            'role' => User::ROLE_GURU,
            'password' => Hash::make($row['password'] ?? 'password123'),
        ]);
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'telepon' => ['nullable', 'string', 'max:20'],
            'jabatan' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:6'],
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
}

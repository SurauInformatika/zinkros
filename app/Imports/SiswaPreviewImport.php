<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class SiswaPreviewImport implements ToCollection, WithHeadingRow, WithValidation
{
    public function collection(\Illuminate\Support\Collection $rows): void
    {
        // No-op: we just need validation + heading row parsing
    }

    public function prepareForValidation(array $row): array
    {
        $row['nisn'] = isset($row['nisn']) && $row['nisn'] !== '' ? (string) $row['nisn'] : null;
        $row['nis'] = isset($row['nis']) && $row['nis'] !== '' ? (string) $row['nis'] : null;
        $row['gender'] = isset($row['gender']) && $row['gender'] !== '' ? strtoupper(trim($row['gender'])) : null;
        return $row;
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
}
